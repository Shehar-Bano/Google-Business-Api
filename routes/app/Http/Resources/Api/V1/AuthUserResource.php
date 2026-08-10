<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AuthUserResource extends JsonResource
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
            'profile_completed' => $this->role === 'player'
                ? $this->status === 'active' && filled($this->dob) && filled($this->gender) && filled($this->city) && filled($this->playing_level) && filled($this->primary_hand)
                : filled($this->club_name) && filled($this->owner_manager_name) && filled($this->address) && filled($this->city),
        ];
    }
}
