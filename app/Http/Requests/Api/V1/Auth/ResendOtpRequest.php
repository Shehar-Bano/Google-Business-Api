<?php

namespace App\Http\Requests\Api\V1\Auth;

use App\Http\Requests\Api\BaseApiRequest;

class ResendOtpRequest extends BaseApiRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'email' => strtolower((string) $this->input('email')),
        ]);
    }

    public function rules(): array
    {
        return [
            'email' => ['required', 'email'],
            'purpose' => ['required', 'in:registration,forgot_password'],
        ];
    }
}
