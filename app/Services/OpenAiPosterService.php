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

        $business = $businessContext['business'] ?? [];
        $preferences = $businessContext['preferences'] ?? [];
        $imagePrompt = "Edit this marketing-poster template for the supplied business. Preserve the template's visual style, layout, colours, imagery, borders and spacing. Replace only placeholder copy with the exact copy below. Keep every word legible and correctly spelled. Do not add facts, prices, contact details, or offers not included below.\n\n"
            . 'BUSINESS: '.json_encode($business, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)."\n"
            . 'PREFERENCES: '.json_encode($preferences, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)."\n"
            . 'POSTER HEADLINE: '.($content['title'] ?? '')."\n"
            . 'POSTER INSTRUCTIONS: '.($content['marketing_instructions'] ?? '');

        try {
            $imageData = null;

            if (PHP_OS_FAMILY === 'Windows') {
                $promptFile = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'openai_prompt_' . uniqid() . '_' . time() . '.txt';
                file_put_contents($promptFile, $imagePrompt);

                $process = \Illuminate\Support\Facades\Process::timeout(180)->run([
                    'curl.exe', '-s', '-X', 'POST', 'https://api.openai.com/v1/images/edits',
                    '-H', 'Authorization: Bearer ' . $this->apiKey,
                    '-F', 'image[]=@' . $fullPath,
                    '-F', 'model=' . $this->imageModel,
                    '-F', 'prompt=<' . $promptFile,
                    '-F', 'size=auto',
                    '-F', 'quality=high',
                    '-F', 'output_format=png'
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
                    ->withOptions(['proxy' => ''])
                    ->acceptJson()
                    ->timeout(120)
                    ->attach('image[]', file_get_contents($fullPath), basename($fullPath))
                    ->post('https://api.openai.com/v1/images/edits', [
                        'model' => $this->imageModel,
                        'prompt' => $imagePrompt,
                        'size' => 'auto',
                        'quality' => 'high',
                        'output_format' => 'png',
                    ]);

                if ($response->successful()) {
                    $imageData = $response->json('data.0.b64_json');
                } else {
                    Log::error('OpenAI poster image generation failed.', ['status' => $response->status()]);
                }
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
            $response = Http::withToken($this->apiKey)
                // The server environment has a broken localhost proxy; OpenAI must be reached directly.
                ->withOptions(['proxy' => ''])
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
}
