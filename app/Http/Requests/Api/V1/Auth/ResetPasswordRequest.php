<?php

namespace App\Http\Requests\Api\V1\Auth;

use App\Http\Requests\Api\BaseApiRequest;
use App\Support\ApiValidationRules;

class ResetPasswordRequest extends BaseApiRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'reset_token' => ['required', 'string'],
            'new_password' => ApiValidationRules::password(),
            'confirm_password' => ['required', 'same:new_password'],
        ];
    }
}
