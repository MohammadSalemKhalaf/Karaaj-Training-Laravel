<?php

namespace App\Services\AI;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use RuntimeException;
use Smalot\PdfParser\Parser;
use ZipArchive;

class ResumeAnalysisService
{
    public function extractResumeInformation(string $fileLocation): array
    {
        $rawText = $this->extractTextFromCloudFile($fileLocation);

        if ($rawText === '') {
            throw new RuntimeException('Resume text extraction returned an empty string.');
        }

        $analysis = $this->analyzeWithGroq($rawText);

        Log::info('Resume analysis extracted fields.', [
            'file_location' => $fileLocation,
            'analysis' => $analysis,
        ]);

        return $analysis;
    }

    /**
     * Analyze a resume file against a vacancy description and return extended analysis.
     * Returns: summary, skills, experience, education, compatibility_score (int|null), feedback
     */
    public function analyzeResumeForVacancy(string $fileLocation, ?string $vacancyText = null): array
    {
        $rawText = $this->extractTextFromCloudFile($fileLocation);

        if ($rawText === '') {
            throw new RuntimeException('Resume text extraction returned an empty string.');
        }

        return $this->analyzeRawTextForVacancy($rawText, $vacancyText);
    }

    /**
     * Analyze raw resume text against a vacancy description and return extended analysis.
     */
    public function analyzeRawTextForVacancy(string $rawText, ?string $vacancyText = null): array
    {
        $apiKey = env('GROQ_API_KEY');

        if (!$apiKey) {
            throw new RuntimeException('GROQ_API_KEY is missing.');
        }

        $prompt = <<<'PROMPT'
Extract resume information and evaluate compatibility with the provided job description. Return only valid JSON with exactly these keys:
{
  "summary": "",
  "skills": "",
  "experience": "",
  "education": "",
  "compatibility_score": 0,
  "feedback": ""
}

Rules:
- Return JSON only with no markdown and no code fences.
- Every value must be a string, except "compatibility_score" which must be a number between 0 and 100.
- If information is missing, return empty string or 0 for score.
- Keep summary concise.
- Keep skills as a comma-separated string.
- Keep experience and education concise and readable.
- Calculate compatibility_score based on how well the resume matches the job description (skills, experience, keywords). Provide an honest percentage.
- Provide feedback explaining why the candidate is strong or weak for THIS vacancy and list 2-4 actionable points.
PROMPT;

        $messages = [
            [
                'role' => 'system',
                'content' => $prompt,
            ],
            [
                'role' => 'user',
                'content' => "Resume text:\n\n" . mb_substr($rawText, 0, 20000),
            ],
        ];

        if (!empty($vacancyText)) {
            $messages[] = [
                'role' => 'user',
                'content' => "Job description:\n\n" . mb_substr($vacancyText, 0, 20000),
            ];
        }

        $response = Http::timeout(60)
            ->retry(2, 500)
            ->withHeaders([
                'Authorization' => 'Bearer ' . $apiKey,
                'Content-Type' => 'application/json',
            ])
            ->post('https://api.groq.com/openai/v1/chat/completions', [
                'model' => env('GROQ_MODEL', 'llama-3.3-70b-versatile'),
                'temperature' => 0.2,
                'max_tokens' => 1500,
                'messages' => $messages,
            ]);

        if ($response->failed()) {
            Log::error('Groq request failed during resume-vacancy analysis.', [
                'status' => $response->status(),
                'body' => $response->json() ?? $response->body(),
            ]);

            throw new RuntimeException('Groq request failed while analyzing the resume.');
        }

        $content = $response->json('choices.0.message.content');

        if (!is_string($content) || trim($content) === '') {
            throw new RuntimeException('Groq returned an empty analysis response.');
        }

        $decoded = json_decode($this->sanitizeJson($content), true);

        if (json_last_error() !== JSON_ERROR_NONE || !is_array($decoded)) {
            Log::error('Groq returned invalid JSON for resume-vacancy analysis.', [
                'content' => $content,
                'json_error' => json_last_error_msg(),
            ]);

            throw new RuntimeException('Failed to parse Groq analysis response.');
        }

        $summary = $this->normalizeField($decoded['summary'] ?? '');
        $skills = $this->normalizeField($decoded['skills'] ?? '');
        $experience = $this->normalizeField($decoded['experience'] ?? '');
        $education = $this->normalizeField($decoded['education'] ?? '');

        $score = null;
        if (isset($decoded['compatibility_score'])) {
            $rawScore = $decoded['compatibility_score'];
            if (is_numeric($rawScore)) {
                $score = (int) round($rawScore);
                if ($score < 0 || $score > 100) {
                    $score = null;
                }
            }
        }

        $feedback = '';
        if (isset($decoded['feedback']) && is_scalar($decoded['feedback'])) {
            $feedback = $this->normalizeText((string) $decoded['feedback']);
        }

        return [
            'summary' => $summary,
            'skills' => $skills,
            'experience' => $experience,
            'education' => $education,
            'compatibility_score' => $score,
            'feedback' => $feedback,
        ];
    }

    private function extractTextFromCloudFile(string $fileLocation): string
    {
        $cloudPath = $this->resolveCloudPath($fileLocation);

        if (!Storage::disk('cloud')->exists($cloudPath)) {
            throw new RuntimeException("Resume file not found on cloud disk: {$cloudPath}");
        }

        $fileContents = Storage::disk('cloud')->get($cloudPath);

        if ($fileContents === false || $fileContents === '') {
            throw new RuntimeException('Failed to read resume file from cloud storage.');
        }

        $extension = strtolower(pathinfo($cloudPath, PATHINFO_EXTENSION));

        return match ($extension) {
            'pdf' => $this->normalizeText($this->extractTextFromPdf($fileContents)),
            'docx' => $this->normalizeText($this->extractTextFromDocx($fileContents)),
            'doc' => $this->normalizeText($this->extractTextFromDoc($fileContents)),
            default => throw new RuntimeException("Unsupported resume file extension: {$extension}"),
        };
    }

    private function extractTextFromPdf(string $fileContents): string
    {
        $tempFile = tempnam(sys_get_temp_dir(), 'resume_pdf_');

        if ($tempFile === false) {
            throw new RuntimeException('Unable to create temporary file for PDF parsing.');
        }

        try {
            file_put_contents($tempFile, $fileContents);

            $parser = new Parser();
            $pdf = $parser->parseFile($tempFile);

            return $pdf->getText();
        } finally {
            if (file_exists($tempFile)) {
                @unlink($tempFile);
            }
        }
    }

    private function extractTextFromDocx(string $fileContents): string
    {
        if (!class_exists(ZipArchive::class)) {
            throw new RuntimeException('ZipArchive extension is required to read DOCX resumes.');
        }

        $tempFile = tempnam(sys_get_temp_dir(), 'resume_docx_');

        if ($tempFile === false) {
            throw new RuntimeException('Unable to create temporary file for DOCX parsing.');
        }

        try {
            file_put_contents($tempFile, $fileContents);

            $zip = new ZipArchive();
            if ($zip->open($tempFile) !== true) {
                throw new RuntimeException('Unable to open DOCX resume archive.');
            }

            $documentXml = $zip->getFromName('word/document.xml');
            $zip->close();

            if ($documentXml === false) {
                throw new RuntimeException('Unable to read DOCX document.xml content.');
            }

            $documentXml = str_replace(['</w:p>', '</w:tr>', '</w:tc>', '<w:tab/>'], ["\n", "\n", " ", "\t"], $documentXml);

            return strip_tags($documentXml);
        } finally {
            if (file_exists($tempFile)) {
                @unlink($tempFile);
            }
        }
    }

    private function extractTextFromDoc(string $fileContents): string
    {
        preg_match_all('/[\p{L}\p{N}\p{P}\p{Zs}\r\n\t]{4,}/u', $fileContents, $matches);

        $text = trim(implode("\n", $matches[0] ?? []));

        if ($text === '') {
            throw new RuntimeException('Unable to extract readable text from DOC resume.');
        }

        return $text;
    }

    private function analyzeWithGroq(string $rawText): array
    {
        $apiKey = env('GROQ_API_KEY');

        if (!$apiKey) {
            throw new RuntimeException('GROQ_API_KEY is missing.');
        }

        $prompt = <<<'PROMPT'
Extract resume information from the provided text and return only valid JSON with exactly these keys:
{
  "summary": "",
  "skills": "",
  "experience": "",
  "education": ""
}

Rules:
- Return JSON only with no markdown and no code fences.
- Every value must be a string.
- If information is missing, return an empty string.
- Keep summary concise.
- Keep skills as a comma-separated string.
- Keep experience and education concise and readable.
PROMPT;

        $response = Http::timeout(60)
            ->retry(2, 500)
            ->withHeaders([
                'Authorization' => 'Bearer '.$apiKey,
                'Content-Type' => 'application/json',
            ])
            ->post('https://api.groq.com/openai/v1/chat/completions', [
                'model' => env('GROQ_MODEL', 'llama-3.3-70b-versatile'),
                'temperature' => 0.2,
                'max_tokens' => 1000,
                'messages' => [
                    [
                        'role' => 'system',
                        'content' => $prompt,
                    ],
                    [
                        'role' => 'user',
                        'content' => "Resume text:\n\n".mb_substr($rawText, 0, 20000),
                    ],
                ],
            ]);

        if ($response->failed()) {
            Log::error('Groq request failed during resume analysis.', [
                'status' => $response->status(),
                'body' => $response->json() ?? $response->body(),
            ]);

            throw new RuntimeException('Groq request failed while analyzing the resume.');
        }

        $content = $response->json('choices.0.message.content');

        if (!is_string($content) || trim($content) === '') {
            throw new RuntimeException('Groq returned an empty analysis response.');
        }

        $decoded = json_decode($this->sanitizeJson($content), true);

        if (json_last_error() !== JSON_ERROR_NONE || !is_array($decoded)) {
            Log::error('Groq returned invalid JSON for resume analysis.', [
                'content' => $content,
                'json_error' => json_last_error_msg(),
            ]);

            throw new RuntimeException('Failed to parse Groq analysis response.');
        }

        return [
            'summary' => $this->normalizeField($decoded['summary'] ?? ''),
            'skills' => $this->normalizeField($decoded['skills'] ?? ''),
            'experience' => $this->normalizeField($decoded['experience'] ?? ''),
            'education' => $this->normalizeField($decoded['education'] ?? ''),
        ];
    }

    private function resolveCloudPath(string $fileLocation): string
    {
        if (!filter_var($fileLocation, FILTER_VALIDATE_URL)) {
            return ltrim($fileLocation, '/');
        }

        $path = ltrim((string) parse_url($fileLocation, PHP_URL_PATH), '/');
        $bucket = trim((string) config('filesystems.disks.cloud.bucket'), '/');

        if ($bucket !== '' && str_starts_with($path, $bucket.'/')) {
            return substr($path, strlen($bucket) + 1);
        }

        return $path;
    }

    private function sanitizeJson(string $content): string
    {
        $trimmed = trim($content);

        if (str_starts_with($trimmed, '```')) {
            $trimmed = preg_replace('/^```(?:json)?\s*/', '', $trimmed) ?? $trimmed;
            $trimmed = preg_replace('/\s*```$/', '', $trimmed) ?? $trimmed;
        }

        return trim($trimmed);
    }

    private function normalizeField(mixed $value): string
    {
        if (is_array($value)) {
            $value = implode(', ', array_map(fn ($item) => is_scalar($item) ? (string) $item : json_encode($item), $value));
        }

        if (!is_scalar($value)) {
            return '';
        }

        return $this->normalizeText((string) $value);
    }

    private function normalizeText(string $text): string
    {
        $text = str_replace(["\r\n", "\r"], "\n", $text);
        $text = preg_replace("/[\t ]+/", ' ', $text) ?? $text;
        $text = preg_replace("/\n{3,}/", "\n\n", $text) ?? $text;

        return trim($text);
    }
}
