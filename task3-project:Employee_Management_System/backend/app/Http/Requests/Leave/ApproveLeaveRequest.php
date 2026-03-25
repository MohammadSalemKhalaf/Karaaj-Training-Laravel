<?php

namespace App\Http\Requests\Leave;

use App\Http\Requests\ApiFormRequest;

class ApproveLeaveRequest extends ApiFormRequest
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
        return [];
    }
}
