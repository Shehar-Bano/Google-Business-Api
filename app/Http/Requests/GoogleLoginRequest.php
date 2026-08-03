<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class GoogleLoginRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'id_token' => 'required_without:code|string|min:10',
            'code' => 'required_without:id_token|string',
            'access_token' => 'nullable|string',
            'refresh_token' => 'nullable|string',
            'expires_in' => 'nullable|integer',
            'expires_at' => 'nullable',
            'token_expires_at' => 'nullable',
        ];
    }
}
