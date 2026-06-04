<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class AccountProfileResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'phone' => $this->phone,
            'role' => $this->role,
            'status' => $this->status,
            'otp_verified' => (bool) $this->otp_verified,
            'club_name' => $this->club_name,
            'owner_manager_name' => $this->owner_manager_name,
            'address' => $this->address,
            'city' => $this->city,
            'number_of_courts' => $this->number_of_courts,
            'working_hours' => $this->working_hours,
            'club_logo' => $this->imageUrl($this->club_logo),
            'facilities' => $this->facilities ?? [],
            'profile_image' => $this->imageUrl($this->profile_image),
            'dob' => $this->dob?->toDateString(),
            'gender' => $this->gender,
            'playing_level' => $this->playing_level,
            'primary_hand' => $this->primary_hand,
            'bio' => $this->bio,
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
