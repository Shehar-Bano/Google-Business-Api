<?php

namespace App\Repositories;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UserRepository
{
    /**
     * Find user by email.
     */
    public function findByEmail(string $email): ?User
    {
        return User::where('email', $email)->first();
    }

    /**
     * Find or create user by Google details.
     */
    public function findOrCreateGoogleUser(array $details): User
    {
        $user = $this->findByEmail($details['email']);

        if (! $user) {
            $user = User::create([
                'name' => $details['name'],
                'email' => $details['email'],
                'password' => Hash::make(Str::random(24)),
                'status' => 'active',
                'otp_verified' => true,
            ]);

            // Assign standard role e.g. player/client if roles system exists
            if (class_exists(\Spatie\Permission\Models\Role::class)) {
                $user->assignRole('user');
            }
        }

        // Link Google Social Account
        \App\Models\SocialAccount::updateOrCreate(
            [
                'user_id' => $user->id,
                'provider' => 'google',
            ],
            [
                'provider_user_id' => $details['provider_user_id'],
                'name' => $details['name'],
                'email' => $details['email'],
                'profile_picture' => $details['profile_picture'] ?? null,
                'access_token' => 'oauth_id_token_verified',
                'connected_at' => now(),
            ]
        );

        return $user;
    }
}
