<?php

namespace App\Http\Requests\Api\V1\Club;

use Illuminate\Foundation\Http\FormRequest;

class UpdateClubDetailsRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        if (! $this->has('facilities')) {
            return;
        }

        $facilities = $this->input('facilities');

        if (is_string($facilities)) {
            $decoded = json_decode($facilities, true);

            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                $facilities = $decoded;
            } else {
                $facilities = array_values(array_filter(array_map('trim', explode(',', $facilities))));
            }
        }

        $this->merge([
            'facilities' => $facilities,
        ]);
    }

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            'address' => ['sometimes', 'required', 'string', 'max:255'],
            'working_hours' => ['sometimes', 'required', 'string', 'max:100'],
            'facilities' => ['sometimes', 'required', 'array'],
            'facilities.*' => ['string', 'max:255'],
            'number_of_courts' => ['sometimes', 'nullable', 'integer', 'min:0'],
        ];
    }
}
