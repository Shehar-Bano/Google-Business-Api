<?php

namespace App\Http\Requests\Api\V1\Notification;

use App\Http\Requests\Api\BaseApiRequest;

class MarkNotificationReadRequest extends BaseApiRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'notification_id' => ['required', 'integer'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'notification_id' => $this->route('notification_id'),
        ]);
    }
}
