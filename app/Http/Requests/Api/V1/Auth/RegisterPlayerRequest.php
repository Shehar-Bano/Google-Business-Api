<?php

namespace App\Http\Requests\Api\V1\Auth;

use App\Http\Requests\Api\BaseApiRequest;
use App\Support\ApiValidationRules;

class RegisterPlayerRequest extends BaseApiRequest
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
            'full_name' => ['required', 'string', 'max:255'],
            'email' => [...ApiValidationRules::email(), 'max:255'],
            'phone' => ApiValidationRules::phone(),
            'password' => ApiValidationRules::password(),
        ];
    }
}
