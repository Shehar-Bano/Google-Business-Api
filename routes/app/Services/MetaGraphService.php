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
            $pages = [];

            foreach ($data['data'] ?? [] as $item) {
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
}
