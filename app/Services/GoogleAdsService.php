<?php

namespace App\Services;

use Google\Ads\GoogleAds\Lib\V25\GoogleAdsClientBuilder;
use Google\Ads\GoogleAds\Lib\OAuth2TokenBuilder;
use Google\Ads\GoogleAds\V25\Services\Client\GeoTargetConstantServiceClient;
use Google\Ads\GoogleAds\V25\Services\Client\KeywordPlanIdeaServiceClient;
use Google\Ads\GoogleAds\V25\Services\SuggestGeoTargetConstantsRequest;
use Google\Ads\GoogleAds\V25\Services\SuggestGeoTargetConstantsRequest\LocationNames;
use Google\Ads\GoogleAds\V25\Services\GenerateKeywordIdeasRequest;
use Google\Ads\GoogleAds\V25\Services\KeywordSeed;
use Google\Ads\GoogleAds\V25\Enums\KeywordPlanNetworkEnum\KeywordPlanNetwork;
use Google\Ads\GoogleAds\V25\Enums\KeywordPlanCompetitionLevelEnum\KeywordPlanCompetitionLevel;
use Google\Ads\GoogleAds\Lib\V25\GoogleAdsClient;
use Illuminate\Support\Facades\Log;
use Exception;

class GoogleAdsService
{
    /**
     * @var GoogleAdsClient|null
     */
    protected ?GoogleAdsClient $googleAdsClient = null;

    /**
     * GoogleAdsService constructor.
     * Initializes the client builder with configurations.
     */
    public function __construct()
    {
        $this->initializeClient();
    }

    /**
     * Helper class to inject static token.
     */
    protected function getStaticTokenCredential(string $token)
    {
        return new class($token) implements \Google\Auth\FetchAuthTokenInterface {
            private $token;
            public function __construct(string $token) { $this->token = $token; }
            public function fetchAuthToken(callable $httpHandler = null): array {
                return ['access_token' => $this->token, 'expires_in' => 3600];
            }
            public function getCacheKey(): ?string { return null; }
            public function getLastReceivedToken(): ?array {
                return ['access_token' => $this->token, 'expires_in' => 3600];
            }
        };
    }

    /**
     * Initialize the Google Ads client using configuration values.
     */
    protected function initializeClient(bool $useFallbackToken = false): void
    {
        $config = config('services.google_ads');

        $clientId = $config['client_id'] ?? null;
        $clientSecret = $config['client_secret'] ?? null;
        $refreshToken = $config['refresh_token'] ?? null;
        $developerToken = $config['developer_token'] ?? null;
        $accessToken = $config['access_token'] ?? env('ACCESS_TOKEN');

        if ($useFallbackToken && !empty($accessToken)) {
            Log::info("Google Ads Authentication: Falling back to static ACCESS_TOKEN.");
            $oAuth2Credential = $this->getStaticTokenCredential($accessToken);
        } else {
            $oAuth2Credential = (new OAuth2TokenBuilder())
                ->withClientId($clientId)
                ->withClientSecret($clientSecret)
                ->withRefreshToken($refreshToken)
                ->build();
        }

        $this->googleAdsClient = (new GoogleAdsClientBuilder())
            ->withDeveloperToken($developerToken)
            ->withOAuth2Credential($oAuth2Credential)
            ->build();
    }

    /**
     * Fetch keyword ideas using country, city and seed keyword parameters.
     *
     * @param string $country
     * @param string $city
     * @param string $keyword
     * @return array
     * @throws Exception
     */
    public function generateKeywordIdeas(string $country, string $city, string $keyword): array
    {
        $config = config('services.google_ads');
        $customerId = $config['customer_id'] ?? null;

        if (!$customerId) {
            throw new Exception("Google Ads Customer ID is missing from configuration.");
        }

        Log::info("Google Ads Keyword Request initiated", [
            'country' => $country,
            'city' => $city,
            'keyword' => $keyword,
            'customer_id' => $customerId
        ]);

        $attempt = 0;

        // Wrap the execution inside retry loops
        return retry(3, function () use ($country, $city, $keyword, $customerId, &$attempt) {
            $attempt++;
            try {
                // Resolve Geo Constant ID for Country
                $countryConstantId = $this->getGeoTargetConstantId($country, 'Country');
                if (!$countryConstantId) {
                    Log::warning("Geo Target Constant not found for country: {$country}");
                }

                // Resolve Geo Constant ID for City
                $cityConstantId = $this->getGeoTargetConstantId($city, 'City');
                if (!$cityConstantId) {
                    Log::warning("Geo Target Constant not found for city: {$city}");
                }

                // Build targets array
                $geoTargets = array_values(array_filter([$countryConstantId, $cityConstantId]));

                // Generate Keyword Ideas
                $keywordPlanIdeaServiceClient = $this->googleAdsClient->getKeywordPlanIdeaServiceClient();

                $requestParams = [
                    'customer_id' => $customerId,
                    'language' => 'languages/1000', // English language constant resource name
                    'geo_target_constants' => $geoTargets,
                    'keyword_seed' => new KeywordSeed([
                        'keywords' => [$keyword]
                    ]),
                    'keyword_plan_network' => KeywordPlanNetwork::GOOGLE_SEARCH
                ];

                $request = new GenerateKeywordIdeasRequest($requestParams);
                $response = $keywordPlanIdeaServiceClient->generateKeywordIdeas($request);
            } catch (Exception $e) {
                // If unauthorized_client or unauthenticated exception occurs, switch to fallback ACCESS_TOKEN
                if ($attempt === 1 && (str_contains($e->getMessage(), 'unauthorized_client') || str_contains($e->getMessage(), 'UNAUTHENTICATED'))) {
                    Log::warning("Google Ads Auth Exception detected, retrying using static ACCESS_TOKEN fallback.");
                    $this->initializeClient(true);
                    throw $e; // Re-throw to trigger Laravel's retry helper with fresh client
                }
                throw $e;
            }

            $results = [];
            foreach ($response->getResults() as $result) {
                /** @var \Google\Ads\GoogleAds\V25\Services\GenerateKeywordIdeaResult $result */
                $metrics = $result->getKeywordIdeaMetrics();

                if (!$metrics) {
                    continue;
                }

                // Convert micro bids to normal currency units
                $lowBid = $metrics->hasLowTopOfPageBidMicros()
                    ? $metrics->getLowTopOfPageBidMicros() / 1000000
                    : null;

                $highBid = $metrics->hasHighTopOfPageBidMicros()
                    ? $metrics->getHighTopOfPageBidMicros() / 1000000
                    : null;

                // Build monthly search histories
                $monthlySearches = [];
                if ($metrics->getMonthlySearchVolume()) {
                    foreach ($metrics->getMonthlySearchVolume() as $volume) {
                        $monthlySearches[] = [
                            'month' => $volume->getMonth(),
                            'year' => $volume->getYear(),
                            'searches' => $volume->getVolume(),
                        ];
                    }
                }

                // Get string value of Competition level
                $competitionVal = $metrics->getCompetition();
                $competitionStr = KeywordPlanCompetitionLevel::name($competitionVal);

                $results[] = [
                    'keyword' => $result->getText(),
                    'search_volume' => $metrics->getAvgMonthlySearches(),
                    'competition' => $competitionStr,
                    'competition_index' => $metrics->getCompetitionIndex(),
                    'low_top_of_page_bid' => $lowBid,
                    'high_top_of_page_bid' => $highBid,
                    'monthly_searches' => $monthlySearches
                ];
            }

            // Sort descending by average monthly searches
            usort($results, function ($a, $b) {
                return $b['search_volume'] <=> $a['search_volume'];
            });

            Log::info("Google Ads Keyword Ideas fetched successfully", [
                'ideas_count' => count($results)
            ]);

            return $results;
        }, 1000); // 1 second backoff delay between retries
    }

    /**
     * Resolve Geo Target Constant ID for country or city name.
     *
     * @param string $name
     * @param string $targetType
     * @return string|null
     */
    protected function getGeoTargetConstantId(string $name, string $targetType): ?string
    {
        try {
            $geoTargetConstantServiceClient = $this->googleAdsClient->getGeoTargetConstantServiceClient();

            $request = new SuggestGeoTargetConstantsRequest([
                'location_names' => new LocationNames([
                    'names' => [$name]
                ]),
                'locale' => 'en'
            ]);

            $response = $geoTargetConstantServiceClient->suggestGeoTargetConstants($request);

            foreach ($response->getGeoTargetConstantSuggestions() as $suggestion) {
                $geo = $suggestion->getGeoTargetConstant();
                if (strcasecmp($geo->getName(), $name) === 0) {
                    if (strcasecmp($geo->getTargetType(), $targetType) === 0) {
                        return $geo->getResourceName();
                    }
                }
            }

            // Fallback to first matched suggest value if exact case matches are not resolved
            if (count($response->getGeoTargetConstantSuggestions()) > 0) {
                return $response->getGeoTargetConstantSuggestions()[0]->getGeoTargetConstant()->getResourceName();
            }
        } catch (Exception $e) {
            Log::error("Failed resolving Geo Target Constant ID for name: {$name}", [
                'exception' => $e->getMessage()
            ]);
        }

        return null;
    }
}
