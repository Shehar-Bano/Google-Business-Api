<?php

namespace App\Http\Resources\Api\V1;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class PlayerBookingDetailResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'club_name' => $this->club?->club_name ?? $this->club?->name,
            'club_logo' => $this->club ? $this->imageUrl($this->club->club_logo) : null,
            'booking_status' => $this->booking_status,
            'booking_id' => $this->id,
            'amount' => $this->normalizeNumber($this->total_amount),
            'court_name' => $this->court?->name,
            'booking_date' => $this->booking_date?->toDateString(),
            'booking_time' => substr((string) $this->start_time, 0, 5).' - '.substr((string) $this->end_time, 0, 5),
            'start_time' => substr((string) $this->start_time, 0, 5),
            'end_time' => substr((string) $this->end_time, 0, 5),
            'duration' => $this->durationLabel(),
            'booking_created_date' => $this->created_at?->toDateString(),
            'payment_type' => $this->payment_method,
            'payment_status' => $this->payment_status,
            'club_phone_number' => $this->club?->phone,
            'club_address' => $this->club?->address,
        ];
    }

    private function durationLabel(): string
    {
        $start = Carbon::createFromFormat('H:i:s', (string) $this->start_time);
        $end = Carbon::createFromFormat('H:i:s', (string) $this->end_time);
        $minutes = $start->diffInMinutes($end);

        if ($minutes % 60 === 0) {
            $hours = (int) ($minutes / 60);

            return $hours.' '.($hours === 1 ? 'hour' : 'hours');
        }

        return $minutes.' minutes';
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
