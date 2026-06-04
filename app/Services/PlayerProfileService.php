<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class PlayerProfileService
{
    public function updateDetails(User $player, array $data): User
    {
        return DB::transaction(function () use ($player, $data) {
            if (array_key_exists('full_name', $data)) {
                $player->name = $data['full_name'];
            }

            if (array_key_exists('email', $data)) {
                $player->email = strtolower($data['email']);
            }

            if (array_key_exists('phone', $data)) {
                $player->phone = $data['phone'];
            }

            if (array_key_exists('dob', $data)) {
                $player->dob = $data['dob'];
            }

            if (array_key_exists('gender', $data)) {
                $player->gender = $data['gender'];
            }

            if (array_key_exists('city_id', $data)) {
                $player->city_id = $data['city_id'];
            }

            if (array_key_exists('playing_level', $data)) {
                $player->playing_level = $data['playing_level'];
            }

            if (array_key_exists('primary_hand', $data)) {
                $player->primary_hand = $data['primary_hand'];
            }

            if (array_key_exists('bio', $data)) {
                $player->bio = $data['bio'];
            }

            $player->save();

            return $player->refresh();
        });
    }

    public function updateLogo(User $player, UploadedFile $imageFile): User
    {
        return DB::transaction(function () use ($player, $imageFile) {
            $this->deleteStoredProfileImage($player->profile_image);
            $player->profile_image = $imageFile->store('player-profiles', 'public');
            $player->save();

            return $player->refresh();
        });
    }

    private function deleteStoredProfileImage(?string $path): void
    {
        if (! $path || str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            return;
        }

        Storage::disk('public')->delete($path);
    }
}
