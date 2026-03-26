<?php

namespace App\Http\Requests\Attendance;

use App\Http\Requests\ApiFormRequest;

class CheckOutAttendanceRequest extends ApiFormRequest
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
            'notes' => ['nullable', 'string'],
            'status' => ['nullable', 'in:present,absent,late,half_day'],
        ];
    }
}
