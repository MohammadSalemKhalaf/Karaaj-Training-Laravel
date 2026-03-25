<?php

namespace App\Http\Requests\Department;

use App\Http\Requests\ApiFormRequest;
use Illuminate\Validation\Rule;

class StoreDepartmentRequest extends ApiFormRequest
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
            'name' => ['required', 'string', 'max:255'],
            'code' => ['required', 'string', 'max:32', 'unique:departments,code'],
            'description' => ['nullable', 'string'],
            'manager_user_id' => ['nullable', 'uuid', 'exists:users,id'],
            'status' => ['required', 'string', Rule::in(['active', 'inactive'])],
        ];
    }
}
