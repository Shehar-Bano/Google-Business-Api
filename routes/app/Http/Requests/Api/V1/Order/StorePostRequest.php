<?php

namespace App\Http\Requests\Api\V1\Order;

use App\Http\Requests\Api\BaseApiRequest;

class StorePostRequest extends BaseApiRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [

            'images' => ['required', 'array', 'min:1', 'max:5'],
            'images.*' => ['required', 'file', 'mimes:jpg,jpeg,png', 'max:5120'],
            'description' => ['required', 'string'],
            'address' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', 'max:25'],
            'date' => ['required', 'date_format:Y-m-d'],
            'time' => ['required', 'string', 'max:50'],
        ];
    }
}
