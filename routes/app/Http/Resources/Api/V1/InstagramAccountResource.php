<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class InstagramAccountResource extends JsonResource
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
            'instagram_business_id' => $this->instagram_business_id,
            'username' => $this->username,
            'profile_picture' => $this->profile_picture,
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
