<?php

namespace App\Http\Requests\Salary;

use App\Http\Requests\ApiFormRequest;

class UpdateSalaryRequest extends ApiFormRequest
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
            'employee_id' => ['required', 'uuid', 'exists:employees,id'],
            'amount' => ['required', 'numeric', 'min:0'],
            'bonuses' => ['nullable', 'numeric', 'min:0'],
            'deductions' => ['nullable', 'numeric', 'min:0'],
            'effective_date' => ['required', 'date'],
            'notes' => ['nullable', 'string'],
        ];
    }
}
