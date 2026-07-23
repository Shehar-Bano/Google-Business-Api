<?php

namespace App\Services;

use App\Repositories\UserRepository;
use App\Models\User;
use Illuminate\Support\Facades\Log;

class AuthService
{
    public function __construct(
        protected UserRepository $userRepo,
        protected GoogleAuthService $googleAuthService
    ) {}

    /**
     * Link verified Google ID Token details to the current authenticated user.
     */
    public function loginWithGoogle(string $idToken, User $user, ?string $currentToken = null): ?array
    {
        // 1. Verify Google token via service
        $googleUser = $this->googleAuthService->verifyToken($idToken);
        if (!$googleUser) {
            return null;
        }

        // 2. Link Google details to the current logged-in user (no new user creation)
        if (empty($user->email)) {
            $user->email = $googleUser['email'];
        }
        if (empty($user->name)) {
            $user->name = $googleUser['name'];
        }
        $user->save();

        // 3. Link Google Social Account
        \App\Models\SocialAccount::updateOrCreate(
            [
                'user_id' => $user->id,
                'provider' => 'google',
            ],
            [
                'provider_user_id' => $googleUser['provider_user_id'],
                'name' => $googleUser['name'],
                'email' => $googleUser['email'],
                'profile_picture' => $googleUser['profile_picture'] ?? null,
                'access_token' => 'oauth_id_token_verified',
                'connected_at' => now(),
            ]
        );

        return [
            'user' => $user,
            'token' => $currentToken,
        ];
    }
}
