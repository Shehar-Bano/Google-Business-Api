<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderDetailResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'order_id' => $this->order_id,
            'status' => $this->status,
            'description' => $this->detail?->description,
            'price' => $this->detail?->price,
            'phone' => $this->detail?->phone,
            'address' => $this->detail?->address,
            'date' => $this->detail?->date?->toDateString(),
            'time' => $this->detail?->time,
            'images' => $this->detail?->images ?? [],
        ];
    }
}
