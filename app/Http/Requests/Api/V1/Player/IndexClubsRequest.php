<?php

namespace App\Http\Requests\Api\V1\Player;

use App\Http\Requests\Api\BaseApiRequest;

class IndexClubsRequest extends BaseApiRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'lowest_price' => ['nullable', 'boolean'],
            'open_now' => ['nullable', 'boolean'],
            'page' => ['nullable', 'integer', 'min:1'],
            'limit' => ['nullable', 'integer', 'min:1', 'max:100'],
        ];
    }
}
