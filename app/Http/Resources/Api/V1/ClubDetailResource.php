<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class ClubDetailResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->club_name ?? $this->name,
            'club_logo' => $this->logoUrl($this->club_logo),
            'address' => $this->address,
            'working_hours' => $this->working_hours,
            'facilities' => $this->facilities ?? [],
            'number_of_courts' => $this->number_of_courts ?? 0,
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
