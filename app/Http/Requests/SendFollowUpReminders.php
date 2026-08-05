<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class SendFollowUpReminders extends FormRequest
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
            'request_ids' => 'required|array|min:1',
            'request_ids.*' => 'required|integer|exists:review_requests,id',
            'channel' => 'required|in:personal,app',
        ];

        $requestIds = $this->input('request_ids');
        if (is_array($requestIds)) {
            $count = count($requestIds);
            if ($count === 1) {
                $rules['channel'] .= '|in:personal';
            } elseif ($count > 1) {
                $rules['channel'] .= '|in:app';
            }
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
