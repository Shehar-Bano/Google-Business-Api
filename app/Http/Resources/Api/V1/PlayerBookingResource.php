<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class PlayerBookingResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'booking_id' => $this->id,
            'booking_status' => $this->booking_status,
            'payment_status' => $this->payment_status,
            'payment_method' => $this->payment_method,
            'payment_transaction_id' => $this->payment_transaction_id,
            'club' => [
                'club_id' => $this->club?->id,
                'club_name' => $this->club?->club_name ?? $this->club?->name,
                'address' => $this->club?->address,
                'city' => $this->club?->city,
                'image' => $this->club ? $this->imageUrl($this->club->club_logo) : null,
            ],
            'court' => [
                'court_id' => $this->court?->id,
                'court_name' => $this->court?->name,
                'court_type' => $this->court?->type,
            ],
            'time' => [
                'booking_date' => $this->booking_date?->toDateString(),
                'start_time' => substr((string) $this->start_time, 0, 5),
                'end_time' => substr((string) $this->end_time, 0, 5),
            ],
            'amount' => [
                'court_price' => $this->court_price,
                'service_fee' => $this->service_fee,
                'total_amount' => $this->total_amount,
                'currency' => $this->currency,
            ],
            'player' => [
                'player_id' => $this->player?->id,
                'player_name' => $this->player?->name,
            ],
            'created_at' => $this->created_at?->toISOString(),
        ];
    }

    private function imageUrl(?string $path): ?string
    {
        if (! $path) {
            return null;
        }

        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            return $path;
        }

        return Storage::disk('public')->url($path);
    }
}
