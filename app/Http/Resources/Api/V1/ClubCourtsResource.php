<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ClubCourtsResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'counts' => [
                'total_courts' => $this->resource['counts']['total_courts'] ?? 0,
                'available_courts' => $this->resource['counts']['available_courts'] ?? 0,
                'maintenance_courts' => $this->resource['counts']['maintenance_courts'] ?? 0,
            ],
            'courts' => CourtResource::collection(collect($this->resource['courts'] ?? [])),
        ];
    }
}
