<?php

namespace App\Http\Resources\Salary;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SalaryResource extends JsonResource
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
                'name' => trim((string) ($this->employee?->first_name.' '.$this->employee?->last_name)),
            ],
            'amount' => $this->amount,
            'bonuses' => $this->bonuses,
            'deductions' => $this->deductions,
            'net_salary' => $this->net_salary,
            'effective_date' => $this->effective_date,
            'created_at' => $this->created_at,
        ];
    }
}
