<?php

namespace App\Services\Recruitment;

use App\Models\JobApplication;
use App\Models\JobVacancy;
use Illuminate\Support\Facades\DB;

class RecruitmentDashboardService
{
    /**
     * Get comprehensive recruitment dashboard metrics.
     *
     * @return array<string, mixed>
     */
    public function getDashboardMetrics(): array
    {
        $totalVacancies = JobVacancy::count();
        $totalApplications = JobApplication::count();
        $approvedApplications = JobApplication::where('status', 'approved')->count();
        $rejectedApplications = JobApplication::where('status', 'rejected')->count();
        $pendingApplications = JobApplication::where('status', 'submitted')->count();

        $averageScore = JobApplication::query()
            ->whereNotNull('ai_generated_score')
            ->avg('ai_generated_score');

        $topCandidates = $this->getTopCandidates(5);
        $lowScoreCandidates = $this->getLowScoreCandidates(5);
        $applicationsPerVacancy = $this->getApplicationsPerVacancy();

        return [
            'total_vacancies' => $totalVacancies,
            'total_applications' => $totalApplications,
            'approved_applications' => $approvedApplications,
            'rejected_applications' => $rejectedApplications,
            'pending_applications' => $pendingApplications,
            'average_ai_score' => $averageScore ? (int) round($averageScore) : null,
            'top_candidates' => $topCandidates,
            'low_score_candidates' => $lowScoreCandidates,
            'applications_per_vacancy' => $applicationsPerVacancy,
        ];
    }

    /**
     * Get top N candidates by AI score.
     *
     * @param int $limit
     * @return array<int, array<string, mixed>>
     */
    private function getTopCandidates(int $limit = 5): array
    {
        $candidates = JobApplication::query()
            ->with(['user', 'jobVacancy'])
            ->whereNotNull('ai_generated_score')
            ->orderByDesc('ai_generated_score')
            ->orderByDesc('created_at')
            ->limit($limit)
            ->get();

        return $candidates->map(function (JobApplication $app) {
            return [
                'id' => $app->id,
                'candidate_name' => $app->user?->name ?? 'Unknown',
                'vacancy' => $app->jobVacancy?->title ?? 'Unknown',
                'ai_score' => $app->ai_generated_score,
                'status' => $app->status,
                'applied_at' => $app->created_at->toIso8601String(),
            ];
        })->toArray();
    }

    /**
     * Get lowest N candidates by AI score.
     *
     * @param int $limit
     * @return array<int, array<string, mixed>>
     */
    private function getLowScoreCandidates(int $limit = 5): array
    {
        $candidates = JobApplication::query()
            ->with(['user', 'jobVacancy'])
            ->whereNotNull('ai_generated_score')
            ->orderBy('ai_generated_score')
            ->orderByDesc('created_at')
            ->limit($limit)
            ->get();

        return $candidates->map(function (JobApplication $app) {
            return [
                'id' => $app->id,
                'candidate_name' => $app->user?->name ?? 'Unknown',
                'vacancy' => $app->jobVacancy?->title ?? 'Unknown',
                'ai_score' => $app->ai_generated_score,
                'status' => $app->status,
                'applied_at' => $app->created_at->toIso8601String(),
            ];
        })->toArray();
    }

    /**
     * Get application counts grouped by vacancy.
     *
     * @return array<int, array<string, mixed>>
     */
    private function getApplicationsPerVacancy(): array
    {
        $counts = JobApplication::query()
            ->select('job_vacancy_id', DB::raw('COUNT(*) as total'))
            ->with('jobVacancy')
            ->groupBy('job_vacancy_id')
            ->orderByDesc('total')
            ->get();

        return $counts->map(function ($item) {
            return [
                'vacancy_id' => $item->job_vacancy_id,
                'vacancy_title' => $item->jobVacancy?->title ?? 'Unknown',
                'application_count' => $item->total,
            ];
        })->toArray();
    }
}
