<?php

namespace App\Http\Requests\Api\V1\Player;

use App\Http\Requests\Api\BaseApiRequest;

class IndexPlayerBookingsRequest extends BaseApiRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'filter' => ['nullable', 'in:upcoming,completed,cancelled'],
            'status' => ['nullable', 'in:upcoming,completed,cancelled'],
            'page' => ['nullable', 'integer', 'min:1'],
            'limit' => ['nullable', 'integer', 'min:1', 'max:100'],
        ];
    }
}
