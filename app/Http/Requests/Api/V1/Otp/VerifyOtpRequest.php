<?php

namespace App\Http\Requests\Api\V1\Otp;

use App\Http\Requests\Api\BaseApiRequest;

class VerifyOtpRequest extends BaseApiRequest
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
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'mobile_number' => ['required', 'string', 'max:20'],
            'otp' => ['required', 'string', 'digits:4'],
        ];
    }
}
