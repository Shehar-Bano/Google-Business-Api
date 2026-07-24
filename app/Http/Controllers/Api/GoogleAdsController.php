<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\KeywordIdeasRequest;
use App\Services\GoogleAdsService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Exception;

class GoogleAdsController extends Controller
{
    /**
     * @var GoogleAdsService
     */
    protected GoogleAdsService $googleAdsService;

    /**
     * GoogleAdsController constructor.
     *
     * @param GoogleAdsService $googleAdsService
     */
    public function __construct(GoogleAdsService $googleAdsService)
    {
        $this->googleAdsService = $googleAdsService;
    }

    /**
     * Get keyword ideas based on location and seed keyword.
     *
     * POST /api/google/keyword-ideas
     *
     * @param KeywordIdeasRequest $request
     * @return JsonResponse
     */
    public function getKeywordIdeas(KeywordIdeasRequest $request): JsonResponse
    {
        $country = $request->input('country');
        $city = $request->input('city');
        $keyword = $request->input('keyword');

        try {
            $data = $this->googleAdsService->generateKeywordIdeas($country, $city, $keyword);

            return response()->json([
                'success' => true,
                'message' => 'Keyword ideas fetched successfully.',
                'request' => [
                    'country' => $country,
                    'city' => $city,
                    'keyword' => $keyword
                ],
                'data' => $data
            ], 200);

        } catch (Exception $e) {
            Log::error("Google Ads API Exception encountered: " . $e->getMessage(), [
                'request' => $request->all(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch keyword ideas: ' . $e->getMessage()
            ], 500);
        }
    }
}
