<?php

namespace App\Http\Requests\Api\V1\Player;

use App\Http\Requests\Api\BaseApiRequest;
use Illuminate\Validation\Validator;

class TimeSlotsRequest extends BaseApiRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'club_id' => ['required', 'integer', 'min:1'],
            'date' => ['required', 'date_format:Y-m-d'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            if ($this->filled('date') && $this->date('date')->startOfDay()->lt(now()->startOfDay())) {
                $validator->errors()->add('date', 'The selected date must not be in the past.');
            }
        });
    }
}
