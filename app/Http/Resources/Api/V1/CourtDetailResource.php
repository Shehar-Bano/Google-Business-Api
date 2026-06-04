<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Str;

class CourtDetailResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $price = (float) $this->price_per_hour;

        return [
            'court_id' => sprintf('court_%02d', $this->id),
            'name' => $this->name,
            'type' => Str::headline((string) $this->type),
            'price_per_hour' => $price == (int) $price ? (int) $price : $price,
            'status' => in_array($this->status, ['maintenance', 'inactive'], true) ? 'maintenance' : 'available',
            'today_bookings' => 0,
            'upcoming_bookings' => 0,
            'maintenance_note' => $this->maintenance_note,
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
