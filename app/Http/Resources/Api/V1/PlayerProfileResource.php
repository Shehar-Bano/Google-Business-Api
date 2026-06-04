<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class PlayerProfileResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'player_image' => $this->imageUrl($this->profile_image),
            'full_name' => $this->name,
            'email' => $this->email,
            'phone_number' => $this->phone,
            'role' => $this->role,
            'status' => $this->status,
            'otp_verified' => (bool) $this->otp_verified,
            'city_id' => $this->city_id ?? null,
            'city_name' => $this->cityRelation?->name ?? $this->city,
            'dob' => $this->dob?->toDateString(),
            'gender' => $this->gender,
            'playing_level' => $this->playing_level,
            'primary_hand' => $this->primary_hand,
            'bio' => $this->bio,
            'total_matches_played' => 0,
            'win_rate' => 0,
            'total_points' => 0,
            'created_at' => $this->created_at?->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at?->format('Y-m-d H:i:s'),
        ];
    }

    private function imageUrl(?string $path): ?string
    {
        if (! $path) {
            return null;
        }

        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            return $path;
        }

        return Storage::disk('public')->url($path);
    }
}
