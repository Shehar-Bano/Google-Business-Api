<?php

namespace App\Http\Requests\Api\V1\Account;

use Illuminate\Foundation\Http\FormRequest;

class StoreFcmTokenRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'fcm_token' => ['required', 'string', 'max:4096'],
            'device_type' => ['nullable', 'string', 'in:android,ios,web'],
            'device_id' => ['nullable', 'string', 'max:255'],
        ];
    }
}
