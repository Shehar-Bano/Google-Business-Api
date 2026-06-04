<?php

namespace App\Http\Requests\Api\V1\Club;

use Illuminate\Foundation\Http\FormRequest;

class StoreTournamentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'tournament_image' => ['nullable'],
            'name' => ['required', 'string', 'max:255'],
            'format' => ['required', 'string', 'max:100'],
            'start_date' => ['required', 'date_format:Y-m-d'],
            'registration_deadline' => ['required', 'date_format:Y-m-d'],
            'end_date' => ['required', 'date_format:Y-m-d'],
            'entry_fees' => ['required', 'numeric', 'min:0'],
            'prize_pool' => ['required', 'numeric', 'min:0'],
            'allowed_player' => ['required', 'integer', 'min:1'],
            'rules' => ['nullable', 'string'],
        ];
    }

    public function withValidator($validator): void
    {
        if ($this->hasFile('tournament_image')) {
            $validator->sometimes('tournament_image', ['file', 'image', 'mimes:jpg,jpeg,png,webp'], fn () => true);
        } elseif ($this->filled('tournament_image') && is_string($this->input('tournament_image'))) {
            $validator->sometimes('tournament_image', ['url'], fn () => true);
        }

        $validator->after(function ($validator) {
            if ($this->filled('start_date') && $this->filled('registration_deadline')
                && $this->date('registration_deadline')->gt($this->date('start_date'))) {
                $validator->errors()->add('registration_deadline', 'Registration deadline must be on or before the start date.');
            }

            if ($this->filled('end_date') && $this->filled('start_date')
                && $this->date('end_date')->lt($this->date('start_date'))) {
                $validator->errors()->add('end_date', 'End date must be on or after the start date.');
            }
        });
    }
}
