<?php

namespace App\Http\Resources\Leave;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class LeaveResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'employee' => [
                'id' => $this->employee?->id,
                'employee_code' => $this->employee?->employee_code,
                'name' => trim((string) ($this->employee?->first_name.' '.$this->employee?->last_name)),
            ],
            'description' => $this->description,
            'start_date' => $this->start_date,
            'end_date' => $this->end_date,
            'status' => $this->status,
            'approved_by' => $this->approver
                ? [
                    'id' => $this->approver->id,
                    'name' => $this->approver->name,
                    'role' => $this->approver->role?->name,
                ]
                : (object) [],
            'created_at' => $this->created_at,
        ];
    }
}
