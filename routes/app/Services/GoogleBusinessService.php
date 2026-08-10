<?php

namespace App\Services;

use App\Models\SocialAccount;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Exception;

class GoogleBusinessService
{
    /**
     * Retrieve a valid access token. If expired, refresh it.
     */
    public function getValidAccessToken(SocialAccount $socialAccount): string
    {
        if ($socialAccount->token_expires_at && $socialAccount->token_expires_at->isFuture() && $socialAccount->access_token) {
            return $socialAccount->access_token;
        }

        if (!$socialAccount->refresh_token) {
            if ($socialAccount->access_token) {
                Log::info("No refresh token present for social account {$socialAccount->id}. Falling back to existing access token.");
                return $socialAccount->access_token;
            }
            throw new Exception("Missing access token. User must re-authenticate.");
        }

        $response = Http::asForm()->post('https://oauth2.googleapis.com/token', [
            'client_id' => config('services.google.client_id'),
            'client_secret' => config('services.google.client_secret'),
            'refresh_token' => $socialAccount->refresh_token,
            'grant_type' => 'refresh_token',
        ]);

        if ($response->failed()) {
            Log::error('Failed to refresh Google OAuth token', [
                'social_account_id' => $socialAccount->id,
                'response' => $response->json(),
            ]);
            throw new Exception("Unable to refresh Google access token: " . ($response->json()['error_description'] ?? 'Unknown error'));
        }

        $data = $response->json();
        $accessToken = $data['access_token'];
        $expiresIn = $data['expires_in'] ?? 3600;

        $socialAccount->update([
            'access_token' => $accessToken,
            'token_expires_at' => now()->addSeconds($expiresIn),
        ]);

        return $accessToken;
    }

    /**
     * Fetch Google Business Accounts.
     */
    public function getAccounts(string $accessToken): array
    {
        $response = Http::withToken($accessToken)
            ->get('https://mybusinessaccountmanagement.googleapis.com/v1/accounts');

        if ($response->failed()) {
            Log::warning('Google Business Profile account request failed', [
                'status' => $response->status(),
                'response' => $response->json(),
            ]);

            // A 0 QPM quota means that the project has not been granted Google
            // Business Profile API access yet. Retrying or refreshing the OAuth
            // token cannot resolve it.
            if ($response->status() === 429 && data_get($response->json(), 'error.details.0.metadata.quota_limit_value') === '0') {
                throw new Exception(
                    'Google Business Profile API access has not been granted for this Google Cloud project. Submit the Basic API Access application in Google Business Profile API documentation, then wait for approval.',
                    429
                );
            }

            throw new Exception(
                'Failed to fetch Google Business Accounts: ' . (data_get($response->json(), 'error.message') ?? 'Unknown Google API error.'),
                $response->status()
            );
        }

        return $response->json()['accounts'] ?? [];
    }

    /**
     * Fetch all Business Locations for a specific account.
     */
    public function getLocations(string $accessToken, string $accountId): array
    {
        // AccountId might already contain 'accounts/' prefix or not. E.g. accounts/12345
        $accountName = str_contains($accountId, 'accounts/') ? $accountId : "accounts/{$accountId}";
        
        $response = Http::withToken($accessToken)
            ->get("https://mybusinessbusinessinformation.googleapis.com/v1/{$accountName}/locations", [
                'readMask' => 'name,title,storefrontAddress,phoneNumbers,categories,regularHours,latlng,websiteUri,profile,metadata'
            ]);

        if ($response->failed()) {
            throw new Exception("Failed to fetch locations for account {$accountId}: " . $response->body());
        }

        return $response->json()['locations'] ?? [];
    }

    /**
     * Fetch complete Business Profile details for a specific location.
     */
    public function getLocationDetails(string $accessToken, string $locationName): array
    {
        // locationName is in format 'locations/{locationId}'
        $response = Http::withToken($accessToken)
            ->get("https://mybusinessbusinessinformation.googleapis.com/v1/{$locationName}", [
                'readMask' => 'name,title,storefrontAddress,phoneNumbers,categories,regularHours,latlng,websiteUri,profile,metadata'
            ]);

        if ($response->failed()) {
            throw new Exception("Failed to fetch location details for {$locationName}: " . $response->body());
        }

        return $response->json();
    }

    /**
     * Fetch business media (photos).
     */
    public function getBusinessMedia(string $accessToken, string $locationName): array
    {
        // Get media using the Google My Business Business Information Media endpoint
        // NOTE: In GBP API, media is accessed via v1/locations/{locationId}/media
        $response = Http::withToken($accessToken)
            ->get("https://mybusinessbusinessinformation.googleapis.com/v1/{$locationName}/media");

        if ($response->failed()) {
            // Fallback gracefully without breaking if media API fails or is not enabled
            Log::warning("Failed to fetch media for location {$locationName}: " . $response->body());
            return [];
        }

        return $response->json()['mediaItems'] ?? [];
    }

    /**
     * Format Google GBP response into custom response structure.
     */
    public function formatBusinessResponse(array $details, array $mediaItems): array
    {
        // 1. Resolve Profile and Cover Photos
        $profilePhoto = '';
        $coverPhoto = '';

        foreach ($mediaItems as $item) {
            $category = $item['category'] ?? '';
            $googleUrl = $item['googleUrl'] ?? '';

            if ($category === 'PROFILE') {
                $profilePhoto = $googleUrl;
            } elseif ($category === 'COVER') {
                $coverPhoto = $googleUrl;
            }
        }

        // Fallbacks if not explicitly categorized
        if (empty($profilePhoto) && !empty($mediaItems)) {
            $profilePhoto = $mediaItems[0]['googleUrl'] ?? '';
        }

        // 2. Extract basic details
        $description = $details['profile']['description'] ?? '';
        $primaryPhone = $details['phoneNumbers']['primaryPhoneNumber'] ?? '';

        // 3. Resolve categories
        $primaryCategory = $details['categories']['primaryCategory']['displayName'] ?? '';
        $additionalCategories = [];
        if (!empty($details['categories']['additionalCategories'])) {
            foreach ($details['categories']['additionalCategories'] as $cat) {
                if (isset($cat['displayName'])) {
                    $additionalCategories[] = $cat['displayName'];
                }
            }
        }

        // 4. Resolve working hours
        $workingHours = [
            'monday' => '',
            'tuesday' => '',
            'wednesday' => '',
            'thursday' => '',
            'friday' => '',
            'saturday' => '',
            'sunday' => '',
        ];

        if (isset($details['regularHours']['periods'])) {
            foreach ($details['regularHours']['periods'] as $period) {
                // E.g. openDay => 'MONDAY', openTime => '08:00', closeDay => 'MONDAY', closeTime => '18:00'
                $openDay = strtolower($period['openDay'] ?? '');
                $openTime = $period['openTime'] ?? '';
                $closeTime = $period['closeTime'] ?? '';

                if ($openDay && isset($workingHours[$openDay]) && $openTime && $closeTime) {
                    $workingHours[$openDay] = "{$openTime}-{$closeTime}";
                }
            }
        }

        return [
            'brand_logo' => $profilePhoto,
            'cover_photo' => $coverPhoto,
            'description' => $description,
            'primary_phone' => $primaryPhone,
            'primary_category' => $primaryCategory,
            'additional_categories' => $additionalCategories,
            'working_hours_day_wise' => $workingHours,
        ];
    }
}
