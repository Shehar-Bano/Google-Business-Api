<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DashboardResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'role' => $this['role'] ?? null,
            'cards' => $this['cards'] ?? [],
            'shortcuts' => $this['shortcuts'] ?? [],
        ];
    }
}
