<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class OpenAiPosterService
{
    public function __construct(
        protected string $apiKey = '',
        protected string $model = '',
        protected string $imageModel = ''
    ) {
        $this->apiKey = $this->apiKey ?: (string) config('services.openai.api_key');
        $this->model = $this->model ?: (string) config('services.openai.model', 'gpt-5.6-sol');
        $this->imageModel = $this->imageModel ?: (string) config('services.openai.image_model', 'gpt-image-2');
    }

    /**
     * Edit a stored poster template using OpenAI's image-edit endpoint.
     */
    public function editPosterTemplate(string $templatePath, array $businessContext, array $content): ?string
    {
        if (blank($this->apiKey)) {
            Log::error('OpenAI image generation skipped because the OpenAI API key is not configured.');

            return null;
        }

        $fullPath = public_path($templatePath);
        if (! is_file($fullPath)) {
            Log::error('Poster template image was not found.', ['template_path' => $templatePath]);

            return null;
        }

        // Convert the template image to PNG if it's in another format (like JPEG)
        $pngPath = $this->ensurePng($fullPath);
        if (!$pngPath) {
            Log::error('Failed to convert template image to PNG.', ['template_path' => $templatePath]);
            return null;
        }

        $business = $businessContext['business'] ?? [];
        $preferences = $businessContext['preferences'] ?? [];
        $imagePrompt = "Edit this marketing-poster template for the supplied business. Preserve the template's visual style, layout, colours, imagery, borders and spacing. Replace only placeholder copy with the exact copy below. Keep every word legible and correctly spelled. Do not add facts, prices, contact details, or offers not included below.\n\n"
            . 'BUSINESS: '.json_encode($business, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)."\n"
            . 'PREFERENCES: '.json_encode($preferences, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)."\n"
            . 'POSTER HEADLINE: '.($content['title'] ?? '')."\n"
            . 'POSTER INSTRUCTIONS: '.($content['marketing_instructions'] ?? '');

        $options = [];
        if (app()->environment('local')) {
            $options['proxy'] = '';
        }

        try {
            $imageData = null;

            if (PHP_OS_FAMILY === 'Windows') {
                $promptFile = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'openai_prompt_' . uniqid() . '_' . time() . '.txt';
                file_put_contents($promptFile, $imagePrompt);

                $process = \Illuminate\Support\Facades\Process::timeout(180)->run([
                    'curl.exe', '-s', '-X', 'POST', 'https://api.openai.com/v1/images/edits',
                    '-H', 'Authorization: Bearer ' . $this->apiKey,
                    '-F', 'image=@' . $pngPath,
                    '-F', 'model=' . $this->imageModel,
                    '-F', 'prompt=<' . $promptFile,
                    '-F', 'size=1024x1024'
                ]);

                @unlink($promptFile);

                if ($process->successful()) {
                    $resData = json_decode($process->output(), true);
                    $imageData = $resData['data'][0]['b64_json'] ?? null;
                } else {
                    Log::error('OpenAI poster image generation via process failed.', ['error' => $process->errorOutput()]);
                }
            }

            // Fallback to standard HTTP Client if not on Windows or if process execution failed
            if (empty($imageData)) {
                $response = Http::withToken($this->apiKey)
                    ->withOptions($options)
                    ->acceptJson()
                    ->timeout(120)
                    ->attach('image', file_get_contents($pngPath), 'image.png')
                    ->post('https://api.openai.com/v1/images/edits', [
                        'model' => $this->imageModel,
                        'prompt' => $imagePrompt,
                        'size' => '1024x1024',
                    ]);

                if ($response->successful()) {
                    $imageData = $response->json('data.0.b64_json');
                } else {
                    Log::error('OpenAI poster image generation failed.', ['status' => $response->status(), 'response' => $response->body()]);
                }
            }

            // Clean up temporary PNG file if it was created
            if ($pngPath !== $fullPath) {
                @unlink($pngPath);
            }

            if (! is_string($imageData) || $imageData === '') {
                Log::error('OpenAI poster image response did not contain image data.');

                return null;
            }

            $imageBinary = base64_decode($imageData, true);
            if ($imageBinary === false) {
                Log::error('OpenAI poster image response contained invalid base64 data.');

                return null;
            }

            $path = 'ai-generated-posters/generated_'.Str::random(10).'_'.time().'.png';
            Storage::disk('public')->put($path, $imageBinary);

            return 'storage/'.$path;
        } catch (\Throwable $exception) {
            // Clean up temporary PNG file if it was created
            if ($pngPath !== $fullPath) {
                @unlink($pngPath);
            }

            Log::error('OpenAI poster image generation exception.', ['message' => $exception->getMessage()]);

            return null;
        }
    }

    /**
     * Generate the copy and image-editing instructions for a business poster.
     */
    public function generatePosterContent(array $businessContext, string $userPrompt, ?string $templateTitle = null): ?array
    {
        if (blank($this->apiKey)) {
            Log::error('OpenAI poster generation skipped because OPENAI_API_KEY is not configured.');

            return null;
        }

        $developerPrompt = <<<'PROMPT'
You create accurate, conversion-focused marketing copy for a single business poster.

Use the supplied business context as the sole source of business facts. Never invent prices, discounts, services, addresses, certifications, contact details, dates, or guarantees. If the request needs a missing fact, produce a useful general post without claiming that fact.

Respect the target audience, regional and language preferences, brand positioning, and CTA when they are present. Keep the poster copy short, readable, and suitable for placing on an image. The caption may be longer but should be ready to publish. Use only a few relevant hashtags. Do not include markdown.

Return these fields:
- title: concise poster headline (maximum 70 characters)
- caption: ready-to-publish social-media caption (maximum 500 characters)
- marketing_instructions: unambiguous text/layout instructions for an image editor. Include headline, supporting line, CTA, and any safe business details to place on the template. Do not ask the image editor to change branding or add unverified claims.
PROMPT;

        $requestPrompt = [
            'business_context' => $businessContext,
            'template_title' => $templateTitle,
            'user_request' => $userPrompt,
        ];

        try {
            $options = [];
            if (app()->environment('local')) {
                $options['proxy'] = '';
            }

            $response = Http::withToken($this->apiKey)
                ->withOptions($options)
                ->acceptJson()
                ->timeout(60)
                ->post('https://api.openai.com/v1/responses', [
                    'model' => $this->model,
                    'input' => [
                        ['role' => 'developer', 'content' => $developerPrompt],
                        ['role' => 'user', 'content' => json_encode($requestPrompt, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)],
                    ],
                    'text' => [
                        'format' => [
                            'type' => 'json_schema',
                            'name' => 'poster_content',
                            'strict' => true,
                            'schema' => [
                                'type' => 'object',
                                'properties' => [
                                    'title' => ['type' => 'string'],
                                    'caption' => ['type' => 'string'],
                                    'marketing_instructions' => ['type' => 'string'],
                                ],
                                'required' => ['title', 'caption', 'marketing_instructions'],
                                'additionalProperties' => false,
                            ],
                        ],
                    ],
                ]);

            if (! $response->successful()) {
                Log::error('OpenAI poster generation failed.', ['status' => $response->status()]);

                return null;
            }

            $content = $this->extractOutputText($response->json());
            $result = json_decode($content, true);

            if (! is_array($result) || ! isset($result['title'], $result['caption'], $result['marketing_instructions'])) {
                Log::error('OpenAI poster generation returned an unexpected response format.');

                return null;
            }

            return [
                'title' => trim((string) $result['title']),
                'caption' => trim((string) $result['caption']),
                'marketing_instructions' => trim((string) $result['marketing_instructions']),
            ];
        } catch (\Throwable $exception) {
            Log::error('OpenAI poster generation exception.', ['message' => $exception->getMessage()]);

            return null;
        }
    }

    /**
     * Get related product/service suggestions based on user keyword using OpenAI.
     */
    public function suggestOfferings(string $keyword): array
    {
        if (blank($this->apiKey)) {
            Log::error('OpenAI suggestion skipped because OpenAI API key is not configured.');
            return [];
        }

        $developerPrompt = "You are an intelligent business category expansion engine.\n"
            ."Your goal is to identify the broader business category or industry of the user's input keyword, and then suggest the 10 most relevant, distinct products, offerings, or services that a business in that same industry would offer.\n"
            ."Rules:\n"
            ."1. Do not just return items containing the keyword. Expand to the wider category.\n"
            ."2. Examples:\n"
            ."   - If user input is 'pizza', identify the category as 'Fast Food Restaurant' and suggest related items like 'Burger', 'Pasta', 'Fries', 'Garlic Bread', 'Chicken Wings', 'Sandwiches', 'Wraps', 'Salad', 'Beverages'.\n"
            ."   - If user input is 'web' or 'website', identify the category as 'Software House / Digital Agency' and suggest related items like 'Mobile Application Development', 'Search Engine Optimization (SEO)', 'Social Media Marketing', 'UI/UX Design', 'Custom Software Development', 'Graphic Design', 'Content Writing'.\n"
            ."3. Return a JSON object containing a 'suggestions' array of strings.";

        $options = [];
        if (app()->environment('local')) {
            $options['proxy'] = '';
        }

        try {
            $response = Http::withToken($this->apiKey)
                ->withOptions($options)
                ->acceptJson()
                ->timeout(30)
                ->post('https://api.openai.com/v1/responses', [
                    'model' => $this->model,
                    'input' => [
                        ['role' => 'developer', 'content' => $developerPrompt],
                        ['role' => 'user', 'content' => "User Keyword: " . $keyword],
                    ],
                    'text' => [
                        'format' => [
                            'type' => 'json_schema',
                            'name' => 'business_suggestions',
                            'strict' => true,
                            'schema' => [
                                'type' => 'object',
                                'properties' => [
                                    'suggestions' => [
                                        'type' => 'array',
                                        'items' => ['type' => 'string']
                                    ]
                                ],
                                'required' => ['suggestions'],
                                'additionalProperties' => false
                            ]
                        ]
                    ]
                ]);

            if (! $response->successful()) {
                Log::error('OpenAI suggestions generation failed.', ['status' => $response->status()]);
                return [];
            }

            $content = $this->extractOutputText($response->json());
            $result = json_decode($content, true);

            if (is_array($result) && isset($result['suggestions']) && is_array($result['suggestions'])) {
                return array_values(array_unique($result['suggestions']));
            }

            return [];
        } catch (\Throwable $exception) {
            Log::error('OpenAI suggestions generation exception.', ['message' => $exception->getMessage()]);
            return [];
        }
    }

    protected function extractOutputText(array $response): string
    {
        if (is_string($response['output_text'] ?? null)) {
            return $response['output_text'];
        }

        foreach ($response['output'] ?? [] as $item) {
            foreach ($item['content'] ?? [] as $content) {
                if (($content['type'] ?? null) === 'output_text' && is_string($content['text'] ?? null)) {
                    return $content['text'];
                }
            }
        }

        return '';
    }

    /**
     * Helper to ensure the template image is a valid PNG format for DALL-E/GPT-Image.
     * If the source file is a JPEG/GIF/WEBP, it creates a temporary PNG copy.
     */
    protected function ensurePng(string $filePath): ?string
    {
        if (!file_exists($filePath)) {
            return null;
        }

        $info = getimagesize($filePath);
        if ($info === false) {
            return null;
        }

        if ($info['mime'] === 'image/png') {
            return $filePath;
        }

        $tempPng = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'temp_poster_' . uniqid() . '.png';
        $image = null;

        switch ($info['mime']) {
            case 'image/jpeg':
                $image = imagecreatefromjpeg($filePath);
                break;
            case 'image/gif':
                $image = imagecreatefromgif($filePath);
                break;
            case 'image/webp':
                if (function_exists('imagecreatefromwebp')) {
                    $image = imagecreatefromwebp($filePath);
                }
                break;
        }

        if (!$image) {
            return null;
        }

        $success = imagepng($image, $tempPng);
        imagedestroy($image);

        return $success ? $tempPng : null;
    }
}
