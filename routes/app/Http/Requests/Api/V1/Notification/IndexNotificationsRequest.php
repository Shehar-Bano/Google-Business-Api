<?php

namespace App\Http\Requests\Api\V1\Notification;

use App\Http\Requests\Api\BaseApiRequest;
use Illuminate\Validation\Rule;

class IndexNotificationsRequest extends BaseApiRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'status' => ['nullable', 'string', Rule::in(['read', 'unread'])],
        ];
    }
}
