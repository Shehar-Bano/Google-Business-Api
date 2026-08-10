<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SocialPageResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'page_id' => $this->page_id,
            'page_name' => $this->page_name,
            'category' => $this->category,
            'connected_at' => $this->connected_at?->toIso8601String(),
            'is_connected' => !is_null($this->connected_at),
        ];
    }
}
