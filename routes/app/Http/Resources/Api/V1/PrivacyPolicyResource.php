<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PrivacyPolicyResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'title' => $this->title ?? 'Privacy Policy',
            'content' => $this->content ?? '',
            'last_updated' => $this->updated_at?->toDateString(),
        ];
    }
}
