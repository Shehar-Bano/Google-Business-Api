<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PlayerCourtResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'court_id' => $this->resource['court_id'],
            'club_id' => $this->resource['club_id'],
            'court_name' => $this->resource['court_name'],
            'court_type' => $this->resource['court_type'],
            'price_per_slot' => $this->resource['price_per_slot'],
            'status' => $this->resource['status'],
            'status_label' => $this->resource['status_label'],
        ];
    }
}
