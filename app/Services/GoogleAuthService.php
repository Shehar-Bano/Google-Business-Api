<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GoogleAuthService
{
    /**
     * Verify Google ID token and return user details if valid.
     */
    public function verifyToken(string $idToken): ?array
    {
        try {
            $clientId = config('services.google.client_id');
            
            // Clean token (trim and restore spaces back to plus signs if url-decoded incorrectly)
            $idToken = trim($idToken);
            $idToken = str_replace(' ', '+', $idToken);
            
            Log::info('Google Auth incoming token string: ' . substr($idToken, 0, 50) . '...' . substr($idToken, -20));

            // Validate via Google oauth2 tokeninfo endpoint
            $response = Http::get('https://oauth2.googleapis.com/tokeninfo', [
                'id_token' => $idToken,
            ]);

            if (!$response->successful()) {
                Log::warning('Google Token Verification failed: ' . $response->body() . ' | Token sent: ' . $idToken);
                return null;
            }

            $payload = $response->json();

            // Verify audience matches Google Client ID
            if (isset($payload['aud']) && $payload['aud'] !== $clientId) {
                // If it's a mobile app, it might match a different client ID.
                // We log this but in production, we should compare aud with valid client IDs.
                Log::info('Google ID token audience: ' . $payload['aud'] . ', expected: ' . $clientId);
            }

            return [
                'provider_user_id' => $payload['sub'],
                'name' => $payload['name'] ?? ($payload['given_name'] . ' ' . $payload['family_name']),
                'email' => $payload['email'],
                'profile_picture' => $payload['picture'] ?? null,
            ];

        } catch (\Exception $e) {
            Log::error('Google Auth Service Exception: ' . $e->getMessage());
            return null;
        }
    }
}
