<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class SendWhatsAppReviewRequest extends FormRequest
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
        $rules = [
            'business_id' => 'required|exists:businesses,id',
            'channel' => 'required|in:personal,app',
            'customers' => 'required|array|min:1',
            'customers.*.name' => 'required|string|max:255',
            'customers.*.phone' => 'required|string|max:50',
            'message' => 'nullable|string',
        ];

        if ($this->input('channel') === 'personal') {
            $rules['customers'] = 'required|array|size:1';
        }

        return $rules;
    }

    /**
     * Handle a failed validation attempt.
     */
    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json([
            'success' => false,
            'message' => 'Validation failed.',
            'errors' => $validator->errors()
        ], 422));
    }
}
