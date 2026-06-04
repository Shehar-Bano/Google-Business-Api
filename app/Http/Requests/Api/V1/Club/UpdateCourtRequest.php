<?php

namespace App\Http\Requests\Api\V1\Club;

use Illuminate\Foundation\Http\FormRequest;

class UpdateCourtRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            'type' => ['sometimes', 'required', 'string', 'max:255'],
            'price_per_hour' => ['sometimes', 'required', 'numeric', 'min:0'],
            'status' => ['sometimes', 'required', 'in:available,maintenance'],
            'maintenance_note' => ['sometimes', 'nullable', 'string'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            if (! $this->filled('name')
                && ! $this->filled('type')
                && ! $this->filled('price_per_hour')
                && ! $this->filled('status')
                && ! $this->has('maintenance_note')) {
                $validator->errors()->add('court', 'At least one field is required.');
            }
        });
    }
}
