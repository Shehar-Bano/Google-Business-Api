<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Str;

class BookingDetailResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $courtPrice = (float) ($this->court_price ?? 0);

        return [
            'booking_id' => $this->id,
            'status' => $this->booking_status,
            'player_detail' => [
                'player_id' => $this->player?->id,
                'name' => $this->player?->name,
                'email' => $this->player?->email,
                'phone' => $this->player?->phone,
            ],
            'court_detail' => [
                'court_id' => $this->court?->id,
                'name' => $this->court?->name,
                'type' => $this->court ? Str::headline((string) $this->court->type) : null,
                'price_per_hour' => $courtPrice == (int) $courtPrice ? (int) $courtPrice : $courtPrice,
            ],
            'booking_date' => $this->booking_date?->toDateString(),
            'booking_time' => substr((string) $this->start_time, 0, 5).' - '.substr((string) $this->end_time, 0, 5),
            'price' => $courtPrice == (int) $courtPrice ? (int) $courtPrice : $courtPrice,
            'payment_status' => $this->payment_status,
        ];
    }
}
