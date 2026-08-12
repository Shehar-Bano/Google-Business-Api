<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\KeywordIdeasRequest;
use App\Services\DataForSeoService;
use App\Services\GoogleAdsService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Exception;

class GoogleAdsController extends Controller
{
    protected DataForSeoService $dataForSeoService;
    protected GoogleAdsService $googleAdsService;

    public function __construct(
        DataForSeoService $dataForSeoService,
        GoogleAdsService $googleAdsService
    ) {
        $this->dataForSeoService = $dataForSeoService;
        $this->googleAdsService = $googleAdsService;
    }

    /**
     * Get keyword ideas based on location and seed keyword via DataForSEO Google Ads API.
     *
     * POST /api/google/keyword-ideas
     *
     * @param KeywordIdeasRequest $request
     * @return JsonResponse
     */
    public function getKeywordIdeas(KeywordIdeasRequest $request): JsonResponse
    {
        $country = $request->input('country', 'United States');
        $city = $request->input('city', '');
        $keyword = $request->input('keyword');
        $businessId = $request->input('business_id');

        // Auto-resolve business_id from authenticated user if not provided in payload
        if (! $businessId) {
            $user = $request->user();
            if (! $user && $request->bearerToken()) {
                $hashed = hash('sha256', $request->bearerToken());
                $user = \App\Models\User::where('api_access_token', $hashed)->first();
            }
            if ($user) {
                $businessId = \App\Models\Business::where('user_id', $user->id)->value('id');
            }
        }

        try {
            // 1. Fetch via DataForSEO Service
            $data = $this->dataForSeoService->generateKeywordIdeas($country, $city, $keyword);

            // 2. Fallback to GoogleAdsService if DataForSEO returned empty
            if (empty($data)) {
                try {
                    $data = $this->googleAdsService->generateKeywordIdeas($country, $city, $keyword);
                } catch (\Throwable $e) {
                    Log::warning("GoogleAdsService fallback failed: " . $e->getMessage());
                }
            }

            // 3. Store in database if business_id is available
            if ($businessId && ! empty($data)) {
                \App\Models\BusinessKeywordIdea::where('business_id', $businessId)
                    ->where('search_query', $keyword)
                    ->delete();

                foreach ($data as $item) {
                    \App\Models\BusinessKeywordIdea::create([
                        'business_id' => $businessId,
                        'search_query' => $keyword,
                        'keyword' => $item['keyword'] ?? '',
                        'avg_monthly_searches' => $item['search_volume'] ?? $item['avg_monthly_searches'] ?? 0,
                        'competition' => $item['competition'] ?? 'LOW',
                        'low_range_bid' => $item['low_top_of_page_bid'] ?? $item['low_range_bid'] ?? null,
                        'high_range_bid' => $item['high_top_of_page_bid'] ?? $item['high_range_bid'] ?? null,
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
            Log::error("Keyword Ideas API Exception: " . $e->getMessage(), [
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
