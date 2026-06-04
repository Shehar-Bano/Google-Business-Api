<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PlayerTimeSlotResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'slot_id' => $this->resource['slot_id'],
            'start_time' => $this->resource['start_time'],
            'end_time' => $this->resource['end_time'],
            'status' => $this->resource['status'],
            'price' => $this->resource['price'],
        ];
    }
}
