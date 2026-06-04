<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PlayerClubListResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->resource['id'],
            'club_name' => $this->resource['club_name'],
            'club_logo' => $this->resource['image'],
            'address' => $this->resource['address'],
            'city' => $this->resource['city'],
            'opening_time' => $this->resource['opening_time'],
            'closing_time' => $this->resource['closing_time'],
            'is_open_now' => $this->resource['is_open_now'],
            'lowest_court_price' => $this->resource['lowest_court_price'],
            'total_courts' => $this->resource['total_courts'],
        ];
    }
}
