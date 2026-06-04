<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BookingListResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $courtPrice = (float) ($this->court_price ?? 0);

        return [
            'id' => $this->id,
            'player_name' => $this->player?->name,
            'court_number' => $this->court?->name,
            'date' => $this->booking_date?->toDateString(),
            'time' => substr((string) $this->start_time, 0, 5).' - '.substr((string) $this->end_time, 0, 5),
            'price' => $courtPrice == (int) $courtPrice ? (int) $courtPrice : $courtPrice,
            'status' => $this->booking_status,
        ];
    }
}
