<?php

namespace App\Http\Requests\JobVacancy;

use Illuminate\Foundation\Http\FormRequest;

class UpdateJobVacancyRequest extends FormRequest
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
            'title' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:5000'],
            'location' => ['nullable', 'string', 'max:255'],
            'salary' => ['nullable', 'numeric', 'min:0'],
            'type' => ['nullable', 'string', 'max:64'],
            'category_id' => ['nullable', 'string', 'uuid'],
            'status' => ['nullable', 'string', 'max:32'],
        ];
    }
}
