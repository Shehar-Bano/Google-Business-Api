<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GeminiService
{
    protected string $apiKey;
    protected string $model;

    public function __construct()
    {
        $this->apiKey = config('services.gemini.api_key') ?? '';
        $this->model = config('services.gemini.model', 'gemini-1.5-flash');
    }

    /**
     * Call Google Gemini API to generate marketing copy text details (title, caption, marketing_instructions).
     */
    public function generatePosterContent(array $businessInfo, string $userPrompt, ?string $templateImageBase64 = null): ?array
    {
        try {
            if (empty($this->apiKey)) {
                Log::warning('Gemini API Key is missing.');
                return $this->getMockResponse($businessInfo, $userPrompt);
            }

            $endpoint = "https://generativelanguage.googleapis.com/v1beta/models/{$this->model}:generateContent?key={$this->apiKey}";

            $businessDetailsText = json_encode($businessInfo, JSON_PRETTY_PRINT);

            $systemInstructions = "You are a professional marketing copywriter and branding expert.\n"
                . "Your task is to generate marketing copy (title, caption, and precise inline editing instructions) for a business poster.\n"
                . "You must output a valid JSON block containing exactly three keys: 'title', 'caption', and 'marketing_instructions'.\n"
                . "The 'marketing_instructions' must describe clearly what promotional text should be written onto the template poster, including headers, deals, and details. Output raw JSON only.";

            $contents = [];

            if ($templateImageBase64) {
                if (preg_match('/^data:image\/(\w+);base64,/', $templateImageBase64, $type)) {
                    $templateImageBase64 = substr($templateImageBase64, strpos($templateImageBase64, ',') + 1);
                }

                $contents[] = [
                    'parts' => [
                        [
                            'text' => "Here is the poster template design for context."
                        ],
                        [
                            'inlineData' => [
                                'mimeType' => 'image/jpeg',
                                'data' => trim($templateImageBase64)
                            ]
                        ]
                    ]
                ];
            }

            $contents[] = [
                'parts' => [
                    [
                        'text' => "System Instructions:\n{$systemInstructions}\n\nBusiness Context:\n{$businessDetailsText}\n\nUser Request: {$userPrompt}"
                    ]
                ]
            ];

            $response = Http::post($endpoint, [
                'contents' => $contents,
                'generationConfig' => [
                    'responseMimeType' => 'application/json',
                ]
            ]);

            if (!$response->successful()) {
                Log::error('Gemini API Error: ' . $response->body());
                return $this->getMockResponse($businessInfo, $userPrompt);
            }

            $result = $response->json();
            $text = $result['candidates'][0]['content']['parts'][0]['text'] ?? '';
            $jsonDecoded = json_decode(trim($text), true);

            if (!$jsonDecoded || !isset($jsonDecoded['title']) || !isset($jsonDecoded['marketing_instructions'])) {
                Log::warning('Gemini response format mismatch: ' . $text);
                return $this->getMockResponse($businessInfo, $userPrompt);
            }

            return [
                'title' => $jsonDecoded['title'],
                'caption' => $jsonDecoded['caption'] ?? '',
                'marketing_instructions' => $jsonDecoded['marketing_instructions'],
            ];

        } catch (\Exception $e) {
            Log::error('Gemini Service Exception: ' . $e->getMessage());
            return $this->getMockResponse($businessInfo, $userPrompt);
        }
    }

    /**
     * High-quality fallback generator if API fails or credentials are empty.
     */
    protected function getMockResponse(array $businessInfo, string $userPrompt): array
    {
        $businessName = $businessInfo['name'] ?? 'Our Business';
        $location = $businessInfo['location'] ?? 'Lahore';
        $title = "Special Offer from " . $businessName;
        $caption = "Transform your experience with {$businessName}! 🌟 Located in {$location}, we bring you the finest quality services custom-made for your lifestyle. Contact us today to find out more! #business #marketing #branding";

        return [
            'title' => $title,
            'caption' => $caption,
            'marketing_instructions' => "Get 30% off all premium offerings at {$businessName} in {$location}. Limitless quality, limited time only!",
        ];
    }
}
