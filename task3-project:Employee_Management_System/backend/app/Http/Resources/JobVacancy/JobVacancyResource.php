<?php

namespace App\Http\Resources\JobVacancy;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class JobVacancyResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'description' => $this->description,
            'location' => $this->location,
            'salary' => $this->salary,
            'type' => $this->type,
            'status' => $this->status,
            'view_count' => $this->view_count,
            'company' => [
                'id' => $this->company?->id,
                'name' => $this->company?->name,
            ],
            'category' => [
                'id' => $this->jobCategory?->id,
                'name' => $this->jobCategory?->name,
            ],
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
