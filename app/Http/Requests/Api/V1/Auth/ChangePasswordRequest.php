<?php

namespace App\Http\Requests\Api\V1\Auth;

use App\Http\Requests\Api\BaseApiRequest;
use App\Support\ApiValidationRules;

class ChangePasswordRequest extends BaseApiRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'current_password' => ['required', 'string'],
            'new_password' => [...ApiValidationRules::password(), 'different:current_password'],
            'confirm_password' => ['required', 'same:new_password'],
        ];
    }
}
