<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class JobApplicationResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'job_vacancy_id' => $this->job_vacancy_id,
            'resume_id' => $this->resume_id,
            'status' => $this->status,
            'ai_generated_score' => $this->ai_generated_score,
            'ai_generated_feedback' => $this->ai_generated_feedback,
            'created_at' => $this->created_at->toIso8601String(),
            'updated_at' => $this->updated_at->toIso8601String(),
            'user' => new UserBasicResource($this->whenLoaded('user')),
            'vacancy' => new JobVacancyResource($this->whenLoaded('jobVacancy')),
            'resume' => new ResumeResource($this->whenLoaded('resume')),
        ];
    }
}
