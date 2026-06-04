<?php

namespace App\Http\Requests\Api\V1\Player;

use App\Models\User;
use App\Models\City;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdatePlayerDetailsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'full_name' => ['sometimes', 'required', 'string', 'max:255'],
            'email' => [
                'sometimes',
                'required',
                'email',
                'max:255',
                Rule::unique(User::class, 'email')->ignore($this->user()->id),
            ],
            'phone' => [
                'sometimes',
                'required',
                'string',
                'max:25',
                Rule::unique(User::class, 'phone')->ignore($this->user()->id),
            ],
            'dob' => ['sometimes', 'required', 'date', 'before:today'],
            'gender' => ['sometimes', 'required', 'in:male,female,other'],
            'city_id' => ['sometimes', 'required', 'integer', Rule::exists(City::class, 'id')->where('is_active', true)],
            'playing_level' => ['sometimes', 'required', 'in:beginner,intermediate,advanced,professional'],
            'primary_hand' => ['sometimes', 'required', 'in:left,right'],
            'bio' => ['sometimes', 'nullable', 'string', 'max:1000'],
        ];
    }
}
