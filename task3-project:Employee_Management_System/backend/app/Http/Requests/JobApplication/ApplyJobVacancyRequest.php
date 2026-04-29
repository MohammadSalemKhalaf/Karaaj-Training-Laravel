<?php

namespace App\Http\Requests\JobApplication;

use Illuminate\Foundation\Http\FormRequest;

class ApplyJobVacancyRequest extends FormRequest
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
            'resume_id' => ['nullable', 'string', 'uuid'],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function messages(): array
    {
        return [
            'resume_id.uuid' => 'Resume ID must be a valid UUID.',
        ];
    }
}
