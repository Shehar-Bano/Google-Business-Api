<?php

namespace App\Http\Requests\Api\V1\Auth;

use App\Http\Requests\Api\BaseApiRequest;
use App\Support\ApiValidationRules;

class CompletePlayerProfileRequest extends BaseApiRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'profile_image' => ['nullable', 'file', 'mimes:jpg,jpeg,png,webp'],
            'dob' => ApiValidationRules::dob(),
            'gender' => ApiValidationRules::gender(),
            'city' => ['required', 'string', 'max:100'],
            'playing_level' => ApiValidationRules::playingLevel(),
            'primary_hand' => ApiValidationRules::primaryHand(),
            'bio' => ['nullable', 'string', 'max:1000'],
        ];
    }
}
