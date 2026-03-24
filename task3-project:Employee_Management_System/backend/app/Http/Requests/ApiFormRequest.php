<?php

namespace App\Http\Requests;

use App\Support\ApiResponse;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

abstract class ApiFormRequest extends FormRequest
{
    protected function failedValidation(Validator $validator): void
    {
        throw new HttpResponseException(
            ApiResponse::error(
                'The given data was invalid.',
                $validator->errors()->toArray(),
                'VALIDATION_ERROR',
                422
            )
        );
    }
}
