<?php

namespace App\Http\Resources\Attendance;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AttendanceResource extends JsonResource
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
            'attendance_date' => $this->attendance_date,
            'check_in_time' => $this->check_in_time,
            'check_out_time' => $this->check_out_time,
            'worked_hours' => $this->worked_hours,
            'status' => $this->status,
        ];
    }
}
