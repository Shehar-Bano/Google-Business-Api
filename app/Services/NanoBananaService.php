<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class NanoBananaService
{
    protected string $apiKey;
    protected string $model;

    public function __construct()
    {
        $this->apiKey = config('services.gemini.api_key') ?? '';
        $this->model = 'nano-banana-pro-preview';
    }

    /**
     * Upload the base template, pass editing instructions and business details to Google's Nano Banana Pro model,
     * download the returned base64 image, save it locally and return the storage path.
     */
    public function editPosterTemplate(string $templatePath, string|array $editInstructions, array $businessInfo): ?string
    {
        try {
            if (is_array($editInstructions)) {
                $editInstructions = json_encode($editInstructions, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
            }
            if (empty($this->apiKey)) {
                Log::error('Gemini API Key is missing for Nano Banana.');
                return null;
            }

            // 1. Resolve template image locally
            $fullPath = public_path($templatePath);
            if (!file_exists($fullPath)) {
                Log::error("Template image not found: {$fullPath}");
                return null;
            }

            $mimeType = mime_content_type($fullPath) ?: 'image/jpeg';
            $base64Image = base64_encode(file_get_contents($fullPath));

            // 2. Format detailed text prompt injecting actual database values
            $detailedPrompt = "You are Google's Nano Banana Pro image editing model. Edit the provided template image.\n"
                . "Keep 100% of the original design layout, color palette, botanical/cosmetics/product imagery, leaves, spacing, buttons, borders, and general structure.\n"
                . "Replace ONLY the placeholder text fields and placeholder logos with the following real business information:\n\n"
                . "BUSINESS NAME: " . ($businessInfo['name'] ?? '') . "\n"
                . "MARKETING TEXT & DETAILS: " . $editInstructions . "\n"
                . "PHONE NUMBER: " . ($businessInfo['phone'] ?? '') . "\n"
                . "EMAIL: " . ($businessInfo['email'] ?? '') . "\n"
                . "WEBSITE: " . ($businessInfo['website'] ?? '') . "\n"
                . "ADDRESS: " . ($businessInfo['address'] ?? '') . "\n"
                . "CITY: " . ($businessInfo['city'] ?? '') . "\n"
                . "WORKING HOURS: " . ($businessInfo['business_timing'] ?? '') . "\n\n"
                . "Ensure all text is perfectly readable, correctly aligned, correctly spelled, and clean in English. Output only the edited image.";

            // 3. Make REST API call to Nano Banana model
            $endpoint = "https://generativelanguage.googleapis.com/v1beta/models/{$this->model}:generateContent?key={$this->apiKey}";

            $response = Http::post($endpoint, [
                'contents' => [
                    [
                        'parts' => [
                            [
                                'text' => $detailedPrompt
                            ],
                            [
                                'inlineData' => [
                                    'mimeType' => $mimeType,
                                    'data' => $base64Image
                                ]
                            ]
                        ]
                    ]
                ],
                'generationConfig' => [
                    'responseModalities' => ['IMAGE']
                ]
            ]);

            if (!$response->successful()) {
                Log::error('Nano Banana API Error: ' . $response->body());
                return null;
            }

            $responseData = $response->json();
            $base64Output = $responseData['candidates'][0]['content']['parts'][0]['inlineData']['data'] ?? null;

            if (!$base64Output) {
                Log::error('Nano Banana Response did not contain image data: ' . json_encode($responseData));
                return null;
            }

            // 4. Decode base64 image data and store it in Laravel storage
            $imageBinary = base64_decode($base64Output);
            $filename = 'generated_' . Str::random(10) . '_' . time() . '.png';
            $savePath = 'ai-generated-posters/' . $filename;

            Storage::disk('public')->put($savePath, $imageBinary);

            return 'storage/' . $savePath;

        } catch (\Exception $e) {
            Log::error('NanoBananaService Exception: ' . $e->getMessage());
            return null;
        }
    }
}
