<?php

namespace App\Http\Resources\Employee;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EmployeeResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'employee_code' => $this->employee_code,
            'full_name' => trim($this->first_name.' '.$this->last_name),
            'email' => $this->email,
            'phone_number' => $this->phone_number,
            'job_title' => $this->job_title,
            'employment_type' => $this->employment_type,
            'status' => $this->status,
            'department' => [
                'id' => $this->department?->id,
                'name' => $this->department?->name,
            ],
            'user' => [
                'id' => $this->user?->id,
                'email' => $this->user?->email,
            ],
            'hire_date' => $this->hire_date,
        ];
    }
}
