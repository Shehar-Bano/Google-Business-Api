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
    public function loginWithGoogle(
        ?string $idToken,
        ?User $user = null,
        ?string $currentToken = null,
        ?string $accessToken = null,
        ?string $refreshToken = null,
        ?int $expiresIn = null,
        ?string $code = null,
        $expiresAt = null
    ): ?array {
        // Exchange code if available
        if ($code) {
            $response = \Illuminate\Support\Facades\Http::post('https://oauth2.googleapis.com/token', [
                'client_id' => config('services.google.client_id'),
                'client_secret' => config('services.google.client_secret'),
                'redirect_uri' => config('services.google.redirect'),
                'code' => $code,
                'grant_type' => 'authorization_code',
            ]);

            if ($response->successful()) {
                $data = $response->json();
                $accessToken = $data['access_token'] ?? $accessToken;
                $refreshToken = $data['refresh_token'] ?? $refreshToken;
                $expiresIn = $data['expires_in'] ?? $expiresIn;
                if (isset($data['id_token'])) {
                    $idToken = $data['id_token'];
                }
            } else {
                Log::error('Google Auth Code Exchange Failed: ' . $response->body());
            }
        }

        if (!$idToken) {
            return null;
        }

        // 1. Verify Google token via service
        $googleUser = $this->googleAuthService->verifyToken($idToken);
        if (!$googleUser) {
            return null;
        }

        // 2. Link Google details to user or create user if logging in from public endpoint
        if (! $user && $currentToken) {
            $hashed = hash('sha256', $currentToken);
            $user = User::where('api_access_token', $hashed)->first();
        }

        if (! $user) {
            $user = User::where('email', $googleUser['email'])->first();
            if (! $user) {
                $user = new User();
                $user->email = $googleUser['email'];
                $user->name = $googleUser['name'];
            }
            if (! $currentToken) {
                $plainToken = bin2hex(random_bytes(32));
                $user->api_access_token = hash('sha256', $plainToken);
                $currentToken = $plainToken;
            }
        } else {
            if (empty($user->email)) {
                $user->email = $googleUser['email'];
            }
            if (empty($user->name)) {
                $user->name = $googleUser['name'];
            }
        }
        $user->save();

        // Calculate expires_at
        $expiresAtDate = null;
        if ($expiresIn) {
            $expiresAtDate = now()->addSeconds($expiresIn);
        } elseif ($expiresAt) {
            try {
                $expiresAtDate = is_numeric($expiresAt) 
                    ? \Illuminate\Support\Carbon::createFromTimestamp($expiresAt) 
                    : \Illuminate\Support\Carbon::parse($expiresAt);
            } catch (\Exception $e) {
                Log::warning('Failed parsing token expiry date: ' . $expiresAt);
            }
        }

        // 3. Link Google Social Account.
        // Google normally returns a refresh token only on the first offline-consent
        // authorization. Do not erase the stored token on later sign-ins when the
        // OAuth response does not include a new one.
        $existingSocialAccount = \App\Models\SocialAccount::query()
            ->where('user_id', $user->id)
            ->where('provider', 'google')
            ->first();

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
                'access_token' => $accessToken ?? $existingSocialAccount?->access_token ?? 'oauth_id_token_verified',
                'refresh_token' => $refreshToken ?? $existingSocialAccount?->refresh_token,
                'token_expires_at' => $expiresAtDate ?? $existingSocialAccount?->token_expires_at,
                'connected_at' => now(),
            ]
        );

        return [
            'user' => $user,
            'token' => $currentToken,
        ];
    }
}
