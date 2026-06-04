<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ClubDashboardResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'court_utilization' => $this->resource['court_utilization'] ?? '0/0',
            'court_booking' => $this->resource['court_booking'] ?? 0,
            'today_booking' => $this->resource['today_booking'] ?? 0,
            'pending_bookings' => $this->resource['pending_bookings'] ?? 0,
            'total_courts' => $this->resource['total_courts'] ?? 0,
            'available_courts' => $this->resource['available_courts'] ?? 0,
            'maintenance_courts' => $this->resource['maintenance_courts'] ?? 0,
            'active_tournaments' => $this->resource['active_tournaments'] ?? 0,
        ];
    }
}
