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
        $businessId = $request->input('business_id');

        try {
            $data = $this->googleAdsService->generateKeywordIdeas($country, $city, $keyword);

            // Store in database if business_id is resolved or provided
            if ($businessId) {
                \App\Models\BusinessKeywordIdea::where('business_id', $businessId)
                    ->where('search_query', $keyword)
                    ->delete();

                foreach ($data as $item) {
                    \App\Models\BusinessKeywordIdea::create([
                        'business_id' => $businessId,
                        'search_query' => $keyword,
                        'keyword' => $item['keyword'] ?? '',
                        'avg_monthly_searches' => $item['avg_monthly_searches'] ?? null,
                        'competition' => $item['competition'] ?? null,
                        'low_range_bid' => $item['low_range_bid'] ?? null,
                        'high_range_bid' => $item['high_range_bid'] ?? null,
                    ]);
                }
            }

            return response()->json([
                'success' => true,
                'message' => 'Keyword ideas fetched and stored successfully.',
                'request' => [
                    'country' => $country,
                    'city' => $city,
                    'keyword' => $keyword,
                    'business_id' => $businessId,
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
