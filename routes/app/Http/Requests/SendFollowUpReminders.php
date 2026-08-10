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
        return [
            'business_id' => 'required|exists:businesses,id',
            'request_ids' => 'required|array|min:1',
            'request_ids.*' => [
                'required',
                'integer',
                function ($attribute, $value, $fail) {
                    $businessId = $this->input('business_id');
                    $exists = \Illuminate\Support\Facades\DB::table('review_requests')
                        ->where('id', $value)
                        ->where('business_id', $businessId)
                        ->exists();

                    if (!$exists) {
                        $fail("The selected request ID {$value} is invalid or does not belong to the selected business.");
                        return;
                    }

                    // Count reminders already sent for this request
                    $count = \Illuminate\Support\Facades\DB::table('request_reminders')
                        ->where('request_id', $value)
                        ->count();

                    if ($count >= 3) {
                        $fail("The request ID {$value} has already reached the maximum limit of 3 reminders.");
                    }
                }
            ],
            'channel' => 'required|in:app',
        ];
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
