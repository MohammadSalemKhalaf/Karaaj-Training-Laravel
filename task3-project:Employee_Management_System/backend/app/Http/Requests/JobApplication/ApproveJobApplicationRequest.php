<?php

namespace App\Http\Requests\JobApplication;

use Illuminate\Foundation\Http\FormRequest;

class ApproveJobApplicationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'department_id' => ['required', 'string', 'uuid'],
            'first_name' => ['nullable', 'string', 'max:255'],
            'last_name' => ['nullable', 'string', 'max:255'],
            'email' => ['nullable', 'string', 'email', 'max:255'],
            'phone_number' => ['nullable', 'string', 'max:20'],
            'address' => ['nullable', 'string', 'max:500'],
            'hire_date' => ['nullable', 'date'],
            'job_title' => ['nullable', 'string', 'max:255'],
            'employment_type' => ['nullable', 'string', 'max:32'],
            'gender' => ['nullable', 'string', 'max:16'],
            'date_of_birth' => ['nullable', 'date'],
            'employee_status' => ['nullable', 'string', 'max:32'],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function messages(): array
    {
        return [
            'department_id.required' => 'Department ID is required.',
            'department_id.uuid' => 'Department ID must be a valid UUID.',
        ];
    }
}
