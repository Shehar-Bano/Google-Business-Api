<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PlayerClubDetailResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->resource['club_id'],
            'club_name' => $this->resource['club_name'],
            'club_logo' => $this->resource['images'][0] ?? null,
            'address' => $this->resource['address'],
            'city' => $this->resource['city'],
            'description' => $this->resource['description'],
            'phone' => $this->resource['phone'],
            'opening_time' => $this->resource['opening_time'],
            'closing_time' => $this->resource['closing_time'],
            'is_open_now' => $this->resource['is_open_now'],
            'facilities' => $this->resource['facilities'],
            'courts_count' => $this->resource['courts_count'],
            'lowest_court_price' => $this->resource['lowest_court_price'],
        ];
    }
}
