<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;

class TournamentDetailResource extends TournamentResource
{
    public function toArray(Request $request): array
    {
        return parent::toArray($request) + [
            'end_date' => $this->end_date?->toDateString(),
            'rules' => $this->rules,
        ];
    }
}
