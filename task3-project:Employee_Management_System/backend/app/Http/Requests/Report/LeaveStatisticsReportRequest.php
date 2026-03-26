<?php

namespace App\Http\Requests\Report;

use App\Http\Requests\ApiFormRequest;

class LeaveStatisticsReportRequest extends ApiFormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, array<int, \Illuminate\Contracts\Validation\ValidationRule|string>>
     */
    public function rules(): array
    {
        return [
            'date_from' => ['nullable', 'date'],
            'date_to' => ['nullable', 'date', 'after_or_equal:date_from'],
            'employee_id' => ['nullable', 'uuid', 'exists:employees,id'],
            'department_id' => ['nullable', 'uuid', 'exists:departments,id'],
        ];
    }
}
