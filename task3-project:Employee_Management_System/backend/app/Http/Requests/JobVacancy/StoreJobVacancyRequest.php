<?php

namespace App\Http\Requests\JobVacancy;

use Illuminate\Foundation\Http\FormRequest;

class StoreJobVacancyRequest extends FormRequest
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
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:5000'],
            'location' => ['nullable', 'string', 'max:255'],
            'salary' => ['nullable', 'numeric', 'min:0'],
            'type' => ['nullable', 'string', 'max:64'],
            'category_id' => ['nullable', 'string', 'uuid'],
            'company_id' => ['required', 'string', 'uuid'],
            'status' => ['nullable', 'string', 'max:32'],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function messages(): array
    {
        return [
            'title.required' => 'Vacancy title is required.',
            'company_id.required' => 'Company is required.',
            'company_id.uuid' => 'Company ID must be a valid UUID.',
        ];
    }
}
