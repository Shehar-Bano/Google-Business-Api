<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class TournamentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $registered = (int) $this->registered_players_count;
        $allowed = (int) $this->allowed_player;

        return [
            'id' => $this->id,
            'tournament_image' => $this->resolveImageUrl($this->tournament_image),
            'tournament_name' => $this->name,
            'format' => $this->format,
            'start_date' => $this->start_date?->toDateString(),
            'registration_deadline' => $this->registration_deadline?->toDateString(),
            'entry_fees' => $this->normalizeNumber($this->entry_fees),
            'prize' => $this->normalizeNumber($this->prize_pool),
            'players_count' => $registered.'/'.$allowed,
            'status' => $this->status,
        ];
    }

    private function resolveImageUrl(?string $value): ?string
    {
        if (! $value) {
            return null;
        }

        if (str_starts_with($value, 'http://') || str_starts_with($value, 'https://')) {
            return $value;
        }

        return Storage::disk('public')->url($value);
    }

    private function normalizeNumber(mixed $value): int|float
    {
        $numeric = (float) $value;

        return $numeric == (int) $numeric ? (int) $numeric : $numeric;
    }
}
