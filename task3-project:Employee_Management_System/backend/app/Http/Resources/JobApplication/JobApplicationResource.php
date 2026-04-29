<?php

namespace App\Http\Resources\JobApplication;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class JobApplicationResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'status' => $this->status,
            'ai_generated_score' => $this->ai_generated_score,
            'ai_generated_feedback' => $this->ai_generated_feedback,
            'user' => [
                'id' => $this->user?->id,
                'name' => $this->user?->name,
                'email' => $this->user?->email,
            ],
            'resume' => [
                'id' => $this->resume?->id,
                'filename' => $this->resume?->filename,
            ],
            'job_vacancy' => [
                'id' => $this->jobVacancy?->id,
                'title' => $this->jobVacancy?->title,
                'company' => [
                    'id' => $this->jobVacancy?->company?->id,
                    'name' => $this->jobVacancy?->company?->name,
                ],
            ],
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
