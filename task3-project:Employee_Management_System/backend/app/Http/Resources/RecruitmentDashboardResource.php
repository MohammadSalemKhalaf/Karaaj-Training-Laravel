<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RecruitmentDashboardResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'total_vacancies' => $this['total_vacancies'],
            'total_applications' => $this['total_applications'],
            'approved_applications' => $this['approved_applications'],
            'rejected_applications' => $this['rejected_applications'],
            'pending_applications' => $this['pending_applications'],
            'average_ai_score' => $this['average_ai_score'],
            'top_candidates' => $this['top_candidates'],
            'low_score_candidates' => $this['low_score_candidates'],
            'applications_per_vacancy' => $this['applications_per_vacancy'],
        ];
    }
}
