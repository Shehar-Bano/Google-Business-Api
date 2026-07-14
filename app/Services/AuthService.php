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
     * Authenticate or register a user via Google ID Token and generate Sanctum token.
     */
    public function loginWithGoogle(string $idToken): ?array
    {
        // 1. Verify Google token via service
        $googleUser = $this->googleAuthService->verifyToken($idToken);
        if (!$googleUser) {
            return null;
        }

        // 2. Find or create user
        $user = $this->userRepo->findOrCreateGoogleUser($googleUser);

        // 3. Create API access token (matching local EnsureApiTokenIsValid middleware)
        $plainAccessToken = bin2hex(random_bytes(32));
        $plainRefreshToken = bin2hex(random_bytes(32));

        $user->api_access_token = hash('sha256', $plainAccessToken);
        $user->api_refresh_token = hash('sha256', $plainRefreshToken);
        $user->save();

        return [
            'user' => $user,
            'token' => $plainAccessToken,
        ];
    }
}
