<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class NotificationResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'description' => $this->description,
            'type' => $this->type,
            'role_type' => $this->role_type ?? $request->user()?->role,
            'is_read' => $this->read_at !== null,
            'created_at' => $this->created_at?->format('Y-m-d H:i:s'),
        ];
    }
}
