<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ListRankedApplicationsRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'status' => ['nullable', 'string', 'in:submitted,approved,rejected,under_review'],
            'job_vacancy_id' => ['nullable', 'uuid'],
            'company_id' => ['nullable', 'uuid'],
            'min_score' => ['nullable', 'integer', 'min:0', 'max:100'],
            'max_score' => ['nullable', 'integer', 'min:0', 'max:100'],
            'sort_by' => ['nullable', 'string', 'in:score,status,date'],
            'sort_direction' => ['nullable', 'string', 'in:asc,desc'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
        ];
    }

    /**
     * Get custom messages for validation errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'status.in' => 'The status must be one of: submitted, approved, rejected, under_review.',
            'job_vacancy_id.uuid' => 'The job vacancy ID must be a valid UUID.',
            'company_id.uuid' => 'The company ID must be a valid UUID.',
            'min_score.max' => 'The minimum score cannot exceed 100.',
            'max_score.max' => 'The maximum score cannot exceed 100.',
        ];
    }
}
