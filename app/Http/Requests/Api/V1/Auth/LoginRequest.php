<?php

namespace App\Http\Requests\Api\V1\Auth;

use App\Http\Requests\Api\BaseApiRequest;

class LoginRequest extends BaseApiRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        if ($this->has('email')) {
            $this->merge([
                'email' => strtolower((string) $this->input('email')),
            ]);
        }
    }

    public function rules(): array
    {
        return [
            'phone' => ['required_without:mobile_number', 'string'],
            'mobile_number' => ['required_without:phone', 'string'],
            'role' => ['nullable', 'in:user,club'],
        ];
    }
}
