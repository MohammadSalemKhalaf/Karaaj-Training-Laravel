<?php

namespace App\Http\Requests\Employee;

use App\Http\Requests\ApiFormRequest;
use Illuminate\Validation\Rule;

class StoreEmployeeRequest extends ApiFormRequest
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
            'user_id' => ['required', 'uuid', 'exists:users,id', 'unique:employees,user_id'],
            'department_id' => ['required', 'uuid', 'exists:departments,id'],
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255'],
            'phone_number' => ['required', 'string', 'max:50'],
            'hire_date' => ['required', 'date'],
            'job_title' => ['required', 'string', 'max:255'],
            'employment_type' => ['required', 'string', Rule::in(['full_time', 'part_time', 'contract', 'intern'])],
            'status' => ['required', 'string', Rule::in(['active', 'inactive', 'terminated', 'on_leave'])],
            'address' => ['nullable', 'string'],
            'gender' => ['nullable', 'string', Rule::in(['male', 'female', 'other'])],
            'date_of_birth' => ['nullable', 'date'],
        ];
    }
}
