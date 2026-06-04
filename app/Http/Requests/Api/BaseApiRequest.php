<?php

namespace App\Http\Requests\Api;

use App\Support\ApiErrorCode;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

abstract class BaseApiRequest extends FormRequest
{
    protected function failedValidation(Validator $validator): void
    {
        throw new HttpResponseException(response()->json([
            'status' => false,
            'message' => 'Validation failed.',
            'error' => [
                'code' => ApiErrorCode::VALIDATION_ERROR,
            ],
            'errors' => $validator->errors(),
        ], 422));
    }
}
