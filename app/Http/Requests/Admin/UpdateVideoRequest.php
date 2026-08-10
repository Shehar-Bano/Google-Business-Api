<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateVideoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $video = $this->route('video');
        $videoId = $video instanceof \App\Models\Video ? $video->id : $video;

        return [
            'screen_name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('videos', 'screen_name')->ignore($videoId),
            ],
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
