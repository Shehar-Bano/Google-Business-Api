<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class MetaGraphService
{
    protected string $baseUrl;
    protected string $version;

    public function __construct()
    {
        $this->baseUrl = config('meta.base_url', 'https://graph.facebook.com');
        $this->version = config('meta.graph_version', 'v20.0');
    }

    /**
     * Get the API endpoint URL.
     */
    protected function getUrl(string $path): string
    {
        return rtrim($this->baseUrl, '/') . '/' . $this->version . '/' . ltrim($path, '/');
    }

    /**
     * Fetch connected Facebook Pages.
     * Graph API: GET /me/accounts
     */
    public function fetchPages(string $userAccessToken): array
    {
        try {
            $url = $this->getUrl('/me/accounts');
            $response = Http::get($url, [
                'access_token' => $userAccessToken,
            ]);

            if (!$response->successful()) {
                Log::error('Meta Graph API fetch pages error: ' . $response->body());
                return [];
            }

            $data = $response->json();
            $rawPages = $data['data'] ?? [];
            Log::info('Meta Graph API fetched pages count: ' . count($rawPages), ['pages' => $rawPages]);

            $pages = [];
            foreach ($rawPages as $item) {
                $pages[] = [
                    'page_id' => $item['id'],
                    'page_name' => $item['name'],
                    'page_access_token' => $item['access_token'], // will be encrypted before saving
                    'category' => $item['category'] ?? null,
                ];
            }

            return $pages;

        } catch (\Exception $e) {
            Log::error('Meta Graph Service Exception in fetchPages: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Get the linked Instagram business account for a Facebook Page.
     * Graph API: GET /{page-id}?fields=instagram_business_account
     */
    public function fetchLinkedInstagramAccount(string $pageId, string $pageAccessToken): ?array
    {
        try {
            $url = $this->getUrl("/{$pageId}");
            $response = Http::get($url, [
                'fields' => 'instagram_business_account',
                'access_token' => $pageAccessToken,
            ]);

            if (!$response->successful()) {
                Log::error("Meta Graph API linked Instagram error for page {$pageId}: " . $response->body());
                return null;
            }

            $data = $response->json();
            $instagramId = $data['instagram_business_account']['id'] ?? null;

            if (!$instagramId) {
                return null;
            }

            // Fetch Instagram Account Profile Details
            // Graph API: GET /{instagram-business-id}?fields=username,profile_picture_url
            $igUrl = $this->getUrl("/{$instagramId}");
            $igResponse = Http::get($igUrl, [
                'fields' => 'username,profile_picture_url',
                'access_token' => $pageAccessToken,
            ]);

            if (!$igResponse->successful()) {
                Log::error("Meta Graph API Instagram details error for {$instagramId}: " . $igResponse->body());
                return null;
            }

            $igData = $igResponse->json();

            return [
                'page_id' => $pageId,
                'instagram_business_id' => $instagramId,
                'username' => $igData['username'] ?? 'instagram_business',
                'profile_picture' => $igData['profile_picture_url'] ?? null,
            ];

        } catch (\Exception $e) {
            Log::error('Meta Graph Service Exception in fetchLinkedInstagramAccount: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Publish a photo post to a Facebook Page.
     * Graph API: POST /{page-id}/photos
     */
    public function publishPagePhoto(string $pageId, string $pageAccessToken, ?string $caption, ?string $imagePath): ?array
    {
        try {
            $url = $this->getUrl("/{$pageId}/photos");
            $message = $caption ?? '';

            // Resolve file path if relative
            $resolvedPath = null;
            if ($imagePath && !filter_var($imagePath, FILTER_VALIDATE_URL)) {
                $cleanPath = ltrim(str_replace('storage/', '', $imagePath), '/');
                $candidates = [
                    public_path($imagePath),
                    public_path('storage/' . $cleanPath),
                    storage_path('app/public/' . $cleanPath),
                ];
                foreach ($candidates as $cand) {
                    if (file_exists($cand) && is_file($cand)) {
                        $resolvedPath = $cand;
                        break;
                    }
                }
            }

            if ($resolvedPath && file_exists($resolvedPath)) {
                $response = Http::attach(
                    'source',
                    file_get_contents($resolvedPath),
                    basename($resolvedPath)
                )->post($url, [
                    'access_token' => $pageAccessToken,
                    'message' => $message,
                ]);
            } elseif ($imagePath && filter_var($imagePath, FILTER_VALIDATE_URL)) {
                $response = Http::post($url, [
                    'access_token' => $pageAccessToken,
                    'url' => $imagePath,
                    'message' => $message,
                ]);
            } else {
                // Post to feed without image
                $feedUrl = $this->getUrl("/{$pageId}/feed");
                $response = Http::post($feedUrl, [
                    'access_token' => $pageAccessToken,
                    'message' => $message,
                ]);
            }

            if (!$response->successful()) {
                Log::error("Meta Graph API publish photo error for page {$pageId}: " . $response->body());
                return null;
            }

            $result = $response->json();
            Log::info("Successfully published post to Facebook Page {$pageId}", $result);

            return $result;

        } catch (\Throwable $e) {
            Log::error("Meta Graph API publishPagePhoto Exception: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Publish a photo post to an Instagram Business account.
     * Meta Graph API:
     * 1. POST /{ig-user-id}/media (Create Media Container)
     * 2. POST /{ig-user-id}/media_publish (Publish Container)
     */
    public function publishInstagramPhoto(string $igUserId, string $pageAccessToken, ?string $caption, ?string $imageUrl): ?array
    {
        try {
            if (!$imageUrl) {
                Log::warning("Instagram publish skipped: Instagram requires an image URL.");
                return null;
            }

            // Auto-detect the correct API domain based on access token prefix
            $domain = "https://graph.facebook.com";
            if (str_starts_with($pageAccessToken, 'IGAA')) {
                $domain = "https://graph.instagram.com";
            }
            $version = config('services.facebook.version', 'v20.0');

            // Step 1: Create Container
            $containerUrl = "{$domain}/{$version}/{$igUserId}/media";
            $containerRes = Http::post($containerUrl, [
                'image_url' => $imageUrl,
                'caption' => $caption ?? '',
                'access_token' => $pageAccessToken,
            ]);

            if (!$containerRes->successful()) {
                Log::error("Meta Graph API Instagram create container error for {$igUserId} on {$domain}: " . $containerRes->body());
                return null;
            }

            $containerData = $containerRes->json();
            $containerId = $containerData['id'] ?? null;
            if (!$containerId) {
                return null;
            }

            // Step 2: Publish Container
            $publishUrl = "{$domain}/{$version}/{$igUserId}/media_publish";
            $publishRes = Http::post($publishUrl, [
                'creation_id' => $containerId,
                'access_token' => $pageAccessToken,
            ]);

            if (!$publishRes->successful()) {
                Log::error("Meta Graph API Instagram publish error for {$igUserId} on {$domain}: " . $publishRes->body());
                return null;
            }

            $result = $publishRes->json();
            Log::info("Successfully published post to Instagram Account {$igUserId} on {$domain}", $result);

            return $result;

        } catch (\Throwable $e) {
            Log::error("Meta Graph API publishInstagramPhoto Exception: " . $e->getMessage());
            return null;
        }
    }
}
