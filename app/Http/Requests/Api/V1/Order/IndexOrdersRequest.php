<?php

namespace App\Http\Requests\Api\V1\Order;

use App\Http\Requests\Api\BaseApiRequest;
use App\Models\Order;
use Illuminate\Validation\Rule;

class IndexOrdersRequest extends BaseApiRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'status' => ['nullable', 'string', Rule::in(Order::STATUSES)],
        ];
    }
}
