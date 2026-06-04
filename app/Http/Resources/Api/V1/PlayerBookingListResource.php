<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class PlayerBookingListResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'booking_id' => $this->id,
            'club_logo' => $this->club ? $this->imageUrl($this->club->club_logo) : null,
            'club_name' => $this->club?->club_name ?? $this->club?->name,
            'court_name' => $this->court?->name,
            'booking_date' => $this->booking_date?->toDateString(),
            'booking_time' => substr((string) $this->start_time, 0, 5).' - '.substr((string) $this->end_time, 0, 5),
            'amount' => $this->normalizeNumber($this->total_amount),
            'payment_status' => $this->payment_status,
            'booking_status' => $this->booking_status,
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

    private function normalizeNumber(mixed $value): int|float
    {
        $numeric = (float) ($value ?? 0);

        return $numeric == (int) $numeric ? (int) $numeric : $numeric;
    }
}
