<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class PlayerTournamentDetailResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'tournament_id' => $this->id,
            'tournament_image' => $this->imageUrl($this->tournament_image),
            'tournament_status' => $this->status,
            'club_name' => $this->club?->club_name ?? $this->club?->name,
            'tournament_name' => $this->name,
            'address' => $this->club?->address,
            'start_date' => $this->start_date?->toDateString(),
            'end_date' => $this->end_date?->toDateString(),
            'registration_deadline' => $this->registration_deadline?->toDateString(),
            'entry_fee' => $this->normalizeNumber($this->entry_fees),
            'prize_pool' => $this->normalizeNumber($this->prize_pool),
            'registered_players' => ((int) $this->registered_players_count).'/'.((int) $this->allowed_player),
            'rules' => $this->rules,
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
