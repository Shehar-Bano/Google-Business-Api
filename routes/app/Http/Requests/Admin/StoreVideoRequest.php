<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class StoreVideoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'screen_name' => ['required', 'string', 'max:255', 'unique:videos,screen_name'],
            'video_url' => ['required', 'url', 'max:2083'],
            'is_active' => ['nullable', 'boolean'],
        ];
    }

    public function payload(): array
    {
        $validated = $this->validated();
        $validated['is_active'] = $this->boolean('is_active');

        return $validated;
    }
}
