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

            $systemInstructions = "You are an expert graphic designer and social media marketing expert.\n"
                . "Use the provided poster template only as a design inspiration. Replace all existing branding, logo, text and business information with the logged-in user's business details.\n"
                . "Generate a professional social media marketing poster context for this business according to the user's prompt.\n"
                . "You must output a valid JSON block containing exactly three keys: 'title', 'caption', and 'image_prompt'.\n"
                . "The 'image_prompt' must be a detailed, high-quality, professional English text prompt (without markdown, exactly describing the layout, graphics, colors, branding, and text of the social media post) suitable for a text-to-image generator like Stable Diffusion or Imagen.\n"
                . "Do not wrap response in markdown code blocks. Output raw JSON only.";

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

            // Generate image using pollinations.ai with the optimized image prompt
            $imagePrompt = $jsonDecoded['image_prompt'];
            $escapedPrompt = urlencode($imagePrompt);
            $generatedImageUrl = "https://image.pollinations.ai/prompt/{$escapedPrompt}?width=1080&height=1080&nologo=true";

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
