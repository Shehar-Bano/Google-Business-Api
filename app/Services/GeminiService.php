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
     * Call Google Gemini API to generate poster text details (title, caption, image prompt)
     * and construct the final image using an image generation fallback.
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

            $systemInstructions = "You are a professional graphic designer and branding expert.\n"
                . "Your task is to generate a HIGH-QUALITY MARKETING POSTER by using the uploaded poster template as the BASE DESIGN.\n"
                . "IMPORTANT RULES:\n"
                . "1. The uploaded poster image is NOT only a reference. It is the DESIGN TEMPLATE.\n"
                . "2. Keep the SAME layout, composition, spacing, colors, shapes, background, buttons, text positions, image positions, and overall visual hierarchy.\n"
                . "3. DO NOT redesign the poster.\n"
                . "4. Replace ONLY the editable content with the user's business details: Business Name, Business Category, Business Description, Products, Services, Phone, Email, Website, Address, City, Country, and CTA Button Text.\n"
                . "5. Every text must be perfectly readable, correctly spelled, and clean.\n"
                . "You must output a valid JSON block containing exactly three keys: 'title', 'caption', and 'image_prompt'.\n"
                . "The 'image_prompt' must be a detailed, high-quality prompt for the FLUX text-to-image model. The FLUX model renders text exactly as written inside double quotes in the prompt. Describe the template scene in vivid detail (colors, leaves, cosmetics bottles, backgrounds) and explicitly state the text to render in double quotes (e.g. 'The text \"MY BUSINESS NAME\" must be written in a clean white elegant font at the top-right'). Output raw JSON only.";

            $contents = [];

            // Add the template image if base64 is provided
            if ($templateImageBase64) {
                // Remove base64 data header if present
                if (preg_match('/^data:image\/(\w+);base64,/', $templateImageBase64, $type)) {
                    $templateImageBase64 = substr($templateImageBase64, strpos($templateImageBase64, ',') + 1);
                }

                $contents[] = [
                    'parts' => [
                        [
                            'text' => "Here is the poster template image for design inspiration."
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

            // Add main prompt text
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

            if (!$jsonDecoded || !isset($jsonDecoded['title']) || !isset($jsonDecoded['image_prompt'])) {
                Log::warning('Gemini response format mismatch: ' . $text);
                return $this->getMockResponse($businessInfo, $userPrompt);
            }

            // Generate image using pollinations.ai with the optimized image prompt using the state-of-the-art FLUX model
            $imagePrompt = $jsonDecoded['image_prompt'];
            $escapedPrompt = urlencode($imagePrompt);
            $generatedImageUrl = "https://image.pollinations.ai/prompt/{$escapedPrompt}?width=1080&height=1080&nologo=true&model=flux";

            return [
                'title' => $jsonDecoded['title'],
                'caption' => $jsonDecoded['caption'] ?? '',
                'image_url' => $generatedImageUrl,
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
        $title = "Stunning Marketing Campaign for " . $businessName;
        $caption = "Transform your experience with {$businessName}! 🌟 Located in {$location}, we bring you the finest quality services custom-made for your lifestyle. Contact us today to find out more! #business #marketing #branding";

        $imagePrompt = "A professional social media banner for a business named {$businessName} in {$location}. Modern corporate design, high resolution, 1080x1080 square format, professional typography and graphic elements.";
        $escapedPrompt = urlencode($imagePrompt);
        $generatedImageUrl = "https://image.pollinations.ai/prompt/{$escapedPrompt}?width=1080&height=1080&nologo=true";

        return [
            'title' => $title,
            'caption' => $caption,
            'image_url' => $generatedImageUrl,
        ];
    }
}
