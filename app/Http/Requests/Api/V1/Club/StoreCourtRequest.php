<?php

namespace App\Http\Requests\Api\V1\Club;

use Illuminate\Foundation\Http\FormRequest;

class StoreCourtRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'type' => ['required', 'string', 'max:255'],
            'price_per_hour' => ['required', 'numeric', 'min:0'],
            'status' => ['required', 'in:available,maintenance'],
            'maintenance_note' => ['nullable', 'string'],
        ];
    }
}
