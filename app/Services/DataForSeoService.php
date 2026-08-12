<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Exception;

class DataForSeoService
{
    protected string $login;
    protected string $password;
    protected string $baseUrl = 'https://api.dataforseo.com/v3';

    public function __construct()
    {
        $this->login = config('services.dataforseo.login') ?: env('DATAFORSEO_LOGIN', 'bshehar2002@gmail.com');
        $this->password = config('services.dataforseo.password') ?: env('DATAFORSEO_PASSWORD', '933246dac8834240');
    }

    /**
     * Generate keyword ideas using DataForSEO Google Ads / Labs API.
     *
     * @param string $country
     * @param string $city
     * @param string $keyword
     * @return array
     * @throws Exception
     */
    public function generateKeywordIdeas(string $country, string $city, string $keyword): array
    {
        Log::info("DataForSEO Keyword Ideas Request initiated", [
            'country' => $country,
            'city' => $city,
            'keyword' => $keyword,
        ]);

        $location = trim(($city ? $city . ', ' : '') . $country);
        if (empty($location)) {
            $location = 'United States';
        }

        $lastError = null;

        // 1. Try Google Ads keywords_for_keywords live endpoint
        try {
            $postData = [
                [
                    'keywords' => [$keyword],
                    'location_name' => $country ?: 'United States',
                    'language_name' => 'English',
                    'sort_by' => 'search_volume,desc',
                ]
            ];

            $response = Http::withBasicAuth($this->login, $this->password)
                ->timeout(60)
                ->post("{$this->baseUrl}/keywords_data/google_ads/keywords_for_keywords/live", $postData);

            $data = $response->json();
            if ($response->successful() && ($data['status_code'] ?? 0) === 20000) {
                $results = $this->parseGoogleAdsResponse($data);
                if (! empty($results)) {
                    Log::info("DataForSEO Google Ads Keywords fetched successfully: " . count($results) . " items");
                    return $results;
                }
            } else {
                $msg = $data['status_message'] ?? $response->body();
                Log::warning("DataForSEO keywords_for_keywords API response: " . $msg);
                $lastError = $msg;
            }
        } catch (\Throwable $e) {
            Log::warning("DataForSEO Google Ads API call failed: " . $e->getMessage());
            $lastError = $e->getMessage();
        }

        // 2. Fallback to DataForSEO Labs keyword_ideas endpoint
        try {
            $postData = [
                [
                    'keyword' => $keyword,
                    'location_name' => $country ?: 'United States',
                    'language_name' => 'English',
                    'limit' => 50,
                ]
            ];

            $response = Http::withBasicAuth($this->login, $this->password)
                ->timeout(60)
                ->post("{$this->baseUrl}/dataforseo_labs/google/keyword_ideas/live", $postData);

            $data = $response->json();
            if ($response->successful() && ($data['status_code'] ?? 0) === 20000) {
                $results = $this->parseLabsResponse($data);
                if (! empty($results)) {
                    Log::info("DataForSEO Labs Keywords fetched successfully: " . count($results) . " items");
                    return $results;
                }
            } else {
                $msg = $data['status_message'] ?? $response->body();
                Log::warning("DataForSEO Labs API response: " . $msg);
                $lastError = $msg;
            }
        } catch (\Throwable $e) {
            Log::warning("DataForSEO Labs API call failed: " . $e->getMessage());
            $lastError = $e->getMessage();
        }

        if ($lastError) {
            throw new Exception("DataForSEO: " . $lastError);
        }

        return [];
    }

    /**
     * Parse DataForSEO Google Ads response format.
     */
    protected function parseGoogleAdsResponse(array $data): array
    {
        $results = [];
        $tasks = $data['tasks'] ?? [];

        foreach ($tasks as $task) {
            $items = $task['result'] ?? [];
            foreach ($items as $item) {
                $kw = $item['keyword'] ?? null;
                if (!$kw) {
                    continue;
                }

                $monthlySearches = [];
                if (!empty($item['monthly_searches'])) {
                    foreach ($item['monthly_searches'] as $m) {
                        $monthlySearches[] = [
                            'year' => $m['year'] ?? null,
                            'month' => $m['month'] ?? null,
                            'searches' => $m['search_volume'] ?? 0,
                        ];
                    }
                }

                $results[] = [
                    'keyword' => $kw,
                    'search_volume' => $item['search_volume'] ?? 0,
                    'avg_monthly_searches' => $item['search_volume'] ?? 0,
                    'competition' => strtoupper($item['competition'] ?? 'LOW'),
                    'competition_index' => $item['competition_index'] ?? null,
                    'low_range_bid' => $item['low_top_of_page_bid'] ?? null,
                    'high_range_bid' => $item['high_top_of_page_bid'] ?? null,
                    'low_top_of_page_bid' => $item['low_top_of_page_bid'] ?? null,
                    'high_top_of_page_bid' => $item['high_top_of_page_bid'] ?? null,
                    'monthly_searches' => $monthlySearches,
                ];
            }
        }

        return $results;
    }

    /**
     * Parse DataForSEO Labs response format.
     */
    protected function parseLabsResponse(array $data): array
    {
        $results = [];
        $tasks = $data['tasks'] ?? [];

        foreach ($tasks as $task) {
            $items = $task['result'][0]['items'] ?? [];
            foreach ($items as $item) {
                $kw = $item['keyword'] ?? null;
                if (!$kw) {
                    continue;
                }

                $keywordInfo = $item['keyword_info'] ?? [];
                $monthlySearches = [];
                if (!empty($keywordInfo['monthly_searches'])) {
                    foreach ($keywordInfo['monthly_searches'] as $m) {
                        $monthlySearches[] = [
                            'year' => $m['year'] ?? null,
                            'month' => $m['month'] ?? null,
                            'searches' => $m['search_volume'] ?? 0,
                        ];
                    }
                }

                $results[] = [
                    'keyword' => $kw,
                    'search_volume' => $keywordInfo['search_volume'] ?? 0,
                    'avg_monthly_searches' => $keywordInfo['search_volume'] ?? 0,
                    'competition' => strtoupper($keywordInfo['competition_level'] ?? 'LOW'),
                    'competition_index' => $keywordInfo['competition'] ?? null,
                    'low_range_bid' => $keywordInfo['low_top_of_page_bid'] ?? null,
                    'high_range_bid' => $keywordInfo['high_top_of_page_bid'] ?? null,
                    'low_top_of_page_bid' => $keywordInfo['low_top_of_page_bid'] ?? null,
                    'high_top_of_page_bid' => $keywordInfo['high_top_of_page_bid'] ?? null,
                    'monthly_searches' => $monthlySearches,
                ];
            }
        }

        return $results;
    }
}
