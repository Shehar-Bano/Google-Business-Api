<?php

namespace App\Http\Requests\Api\V1\Account;

use App\Http\Requests\Api\BaseApiRequest;
class UpdateProfileDetailsRequest extends BaseApiRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['nullable', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:25'],
            'club_name' => ['nullable', 'string', 'max:255'],
            'owner_manager_name' => ['nullable', 'string', 'max:255'],
            'address' => ['nullable', 'string', 'max:255'],
            'city' => ['nullable', 'string', 'max:100'],
            'number_of_courts' => ['nullable', 'integer', 'gt:0'],
            'working_hours' => ['nullable', 'string', 'max:100'],
            'facilities' => ['nullable', 'array'],
            'facilities.*' => ['string', 'max:255'],
            'dob' => ['nullable', 'date_format:Y-m-d'],
            'gender' => ['nullable', 'in:male,female,other'],
            'playing_level' => ['nullable', 'in:beginner,intermediate,advanced,professional'],
            'primary_hand' => ['nullable', 'in:left,right'],
            'bio' => ['nullable', 'string', 'max:1000'],
        ];
    }
}
