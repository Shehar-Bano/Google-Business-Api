<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\GoogleBusinessService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Exception;
use Illuminate\Support\Facades\Log;

class GoogleBusinessController extends Controller
{
    public function __construct(protected GoogleBusinessService $googleBusinessService) {}

    /**
     * Helper to get Google Social Account of authenticated user.
     */
    protected function getGoogleAccount(Request $request)
    {
        $user = $request->user();
        if (!$user) {
            throw new Exception("Unauthorized", 401);
        }

        $socialAccount = $user->socialAccounts()->where('provider', 'google')->first();
        if (!$socialAccount) {
            throw new Exception("Google account not connected. Please connect your Google account first.", 400);
        }

        return $socialAccount;
    }

    /**
     * Get list of Google Business Profile accounts.
     */
    public function getAccounts(Request $request): JsonResponse
    {
        try {
            $socialAccount = $this->getGoogleAccount($request);
            $accessToken = $this->googleBusinessService->getValidAccessToken($socialAccount);
            $accounts = $this->googleBusinessService->getAccounts($accessToken);

            return response()->json([
                'success' => true,
                'data' => $accounts
            ]);
        } catch (Exception $e) {
            Log::error('GBP Get Accounts Error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], $e->getCode() >= 400 && $e->getCode() <= 500 ? $e->getCode() : 500);
        }
    }

    /**
     * Get locations for a given Google Business account.
     */
    public function getLocations(Request $request, string $accountId): JsonResponse
    {
        try {
            $socialAccount = $this->getGoogleAccount($request);
            $accessToken = $this->googleBusinessService->getValidAccessToken($socialAccount);
            $locations = $this->googleBusinessService->getLocations($accessToken, $accountId);

            if (empty($locations)) {
                return response()->json([
                    'success' => true,
                    'message' => 'No locations found for this account.',
                    'data' => []
                ]);
            }

            return response()->json([
                'success' => true,
                'data' => $locations
            ]);
        } catch (Exception $e) {
            Log::error('GBP Get Locations Error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], $e->getCode() >= 400 && $e->getCode() <= 500 ? $e->getCode() : 500);
        }
    }

    /**
     * Get detailed, formatted profile for a specific location.
     */
    public function getLocationDetails(Request $request, string $locationId): JsonResponse
    {
        try {
            $socialAccount = $this->getGoogleAccount($request);
            $accessToken = $this->googleBusinessService->getValidAccessToken($socialAccount);
            
            $locationName = "locations/{$locationId}";

            // 1. Fetch profile details
            $details = $this->googleBusinessService->getLocationDetails($accessToken, $locationName);

            // 2. Fetch media items
            $mediaItems = [];
            try {
                $mediaItems = $this->googleBusinessService->getBusinessMedia($accessToken, $locationName);
            } catch (Exception $mediaEx) {
                Log::warning("Could not fetch media for location {$locationId}: " . $mediaEx->getMessage());
            }

            // 3. Format response
            $formatted = $this->googleBusinessService->formatBusinessResponse($details, $mediaItems);

            return response()->json([
                'success' => true,
                'data' => $formatted
            ]);
        } catch (Exception $e) {
            Log::error('GBP Get Location Details Error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], $e->getCode() >= 400 && $e->getCode() <= 500 ? $e->getCode() : 500);
        }
    }
}
