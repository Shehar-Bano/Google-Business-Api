<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ClubBookingsResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'counts' => [
                'pending_bookings' => $this->resource['counts']['pending_bookings'] ?? 0,
                'confirmed_bookings' => $this->resource['counts']['confirmed_bookings'] ?? 0,
                'cancelled_bookings' => $this->resource['counts']['cancelled_bookings'] ?? 0,
            ],
            'bookings' => BookingListResource::collection(collect($this->resource['bookings'] ?? [])),
        ];
    }
}
