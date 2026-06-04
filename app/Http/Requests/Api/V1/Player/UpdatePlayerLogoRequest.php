<?php

namespace App\Http\Requests\Api\V1\Player;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePlayerLogoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'profile_image' => ['required', 'file', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ];
    }
}
