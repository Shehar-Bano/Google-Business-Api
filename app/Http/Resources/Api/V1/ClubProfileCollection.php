<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;
use Illuminate\Support\Facades\Storage;

class ClubProfileCollection extends ResourceCollection
{
    public function toArray(Request $request): array
    {
        $club = $this->collection->first();

        if (! $club) {
            return [];
        }

        return [
            'id' => $club->id,
            'name' => $club->club_name ?? $club->name,
            'club_logo' => $this->logoUrl($club->club_logo),
            'email' => $club->email,
            'phone' => $club->phone,
            'status' => $club->status,
            'role' => $club->role,
            'otp_verified' => (bool) $club->otp_verified,
            'address' => $club->address,
            'working_hours' => $club->working_hours,
            'facilities' => $club->facilities ?? [],
            'number_of_courts' => $club->number_of_courts ?? 0,
        ];
    }

    private function logoUrl(?string $path): ?string
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
