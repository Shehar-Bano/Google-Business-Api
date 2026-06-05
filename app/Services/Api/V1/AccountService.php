<?php

namespace App\Services\Api\V1;

use App\Http\Requests\Api\V1\Account\UpdateProfileLogoRequest;
use App\Models\User;
use Illuminate\Support\Facades\Storage;

class AccountService
{
    public function dashboard(User $user): array
    {
        return [
            'role' => $user->role,
            'cards' => [
                [
                    'key' => 'profile_status',
                    'title' => 'Profile Status',
                    'value' => $user->status ?? 'active',
                    'type' => 'status',
                ],
                [
                    'key' => 'notifications',
                    'title' => 'Unread Notifications',
                    'value' => 0,
                    'type' => 'count',
                ],
                [
                    'key' => 'modules',
                    'title' => 'Available Modules',
                    'value' => 0,
                    'type' => 'count',
                ],
            ],
            'shortcuts' => [
                [
                    'key' => 'profile',
                    'title' => 'Profile',
                    'route' => 'profile',
                ],
                [
                    'key' => 'help_support',
                    'title' => 'Help & Support',
                    'route' => 'help-support',
                ],
                [
                    'key' => 'privacy_policy',
                    'title' => 'Privacy Policy',
                    'route' => 'privacy-policy',
                ],
            ],
        ];
    }

    public function updateDetails(User $user, array $validated): User
    {
        $allowedFields = [
            'name',
            // 'phone',
            // 'club_name',
            // 'owner_manager_name',
            'address',

            // 'city',
            // 'number_of_courts',
            // 'working_hours',
            // 'facilities',
            // 'dob',
            // 'gender',
            // 'playing_level',
            // 'primary_hand',
            // 'bio',
        ];

        foreach ($allowedFields as $field) {
            if (array_key_exists($field, $validated)) {
                $user->{$field} = $validated[$field];
            }
        }
        // ✅ Profile Image Update
        if (request()->hasFile('profile_image')) {

            // old image delete
            $this->deleteStoredImage($user->profile_image ?? null);

            // new image store
            $path = request()->file('profile_image')->store('profiles', 'public');

            $user->profile_image = $path;
        }

        $user->save();

        return $user->refresh();
    }

    public function updateLogo(User $user, UpdateProfileLogoRequest $request): User
    {
        $field = $user->role === 'club' ? 'club_logo' : 'profile_image';
        $folder = $user->role === 'club' ? 'club-logos' : 'player-profiles';

        $this->deleteStoredImage($user->{$field});

        $user->{$field} = $request->file('logo')->store($folder, 'public');
        $user->save();

        return $user->refresh();
    }

    private function deleteStoredImage(?string $path): void
    {
        if (! $path || str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            return;
        }

        Storage::disk('public')->delete($path);
    }
}
