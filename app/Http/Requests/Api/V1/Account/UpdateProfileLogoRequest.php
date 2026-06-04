<?php

namespace App\Http\Requests\Api\V1\Account;

use App\Http\Requests\Api\BaseApiRequest;

class UpdateProfileLogoRequest extends BaseApiRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'logo' => ['required', 'file', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
        ];
    }
}
