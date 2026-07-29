<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GooglePlaceController extends Controller
{
    /**
     * Fetch Google Place Details by Place ID.
     *
     * GET /api/v1/google/place-details?place_id=PLACE_ID
     */
    public function getPlaceDetails(Request $request): JsonResponse
    {
        $request->validate([
            'place_id' => 'required|string|max:500',
        ]);

        $placeId = $request->string('place_id')->toString();
        $apiKey = env('PLACES');

        if (empty($apiKey)) {
            return response()->json([
                'success' => false,
                'message' => 'Google Places API key is not configured in the system environment.',
            ], 500);
        }

        try {
            // Call Google Places Details API
            $response = Http::get('https://maps.googleapis.com/maps/api/place/details/json', [
                'place_id' => $placeId,
                'key' => $apiKey,
                'fields' => 'name,formatted_address,formatted_phone_number,website,rating,user_ratings_total,reviews,photos,geometry,opening_hours,business_status,editorial_summary'
            ]);

            if ($response->failed()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to connect to Google Places API.',
                    'error' => $response->body()
                ], 502);
            }

            $data = $response->json();

            if (isset($data['status']) && $data['status'] !== 'OK') {
                return response()->json([
                    'success' => false,
                    'message' => 'Google Places API returned an error status: ' . $data['status'],
                    'error' => $data['error_message'] ?? 'Unknown error'
                ], 400);
            }

            return response()->json([
                'success' => true,
                'message' => 'Google Place details fetched successfully.',
                'data' => $data['result'] ?? []
            ], 200);

        } catch (\Exception $e) {
            Log::error('Google Place details fetch error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'An error occurred while fetching Google Place details: ' . $e->getMessage()
            ], 500);
        }
    }
}
