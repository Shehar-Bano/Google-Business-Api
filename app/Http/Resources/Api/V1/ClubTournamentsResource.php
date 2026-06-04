<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ClubTournamentsResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'counts' => [
                'open_tournament_count' => $this->resource['counts']['open_tournament_count'] ?? 0,
                'full_tournament_count' => $this->resource['counts']['full_tournament_count'] ?? 0,
                'total_registered_players' => $this->resource['counts']['total_registered_players'] ?? 0,
            ],
            'tournaments' => TournamentResource::collection(collect($this->resource['tournaments'] ?? [])),
        ];
    }
}
