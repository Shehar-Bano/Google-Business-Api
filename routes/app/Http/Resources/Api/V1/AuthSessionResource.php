<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AuthSessionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'access_token' => $this['access_token'] ?? null,
            'refresh_token' => $this['refresh_token'] ?? null,
            'user' => isset($this['user']) ? AuthUserResource::make($this['user']) : null,
        ];
    }
}
