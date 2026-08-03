<?php

namespace Tests\Unit;

use App\Services\OpenAiPosterService;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class OpenAiPosterServiceTest extends TestCase
{
    public function test_it_sends_business_and_preferences_to_openai_and_returns_structured_copy(): void
    {
        config()->set('services.openai.api_key', 'test-key');
        config()->set('services.openai.model', 'test-model');

        Http::fake([
            'https://api.openai.com/v1/responses' => Http::response([
                'output_text' => json_encode([
                    'title' => 'Fresh coffee, made for you',
                    'caption' => 'Visit us today. #coffee',
                    'marketing_instructions' => 'Place the headline at the top and the CTA at the bottom.',
                ]),
            ]),
        ]);

        $result = app(OpenAiPosterService::class)->generatePosterContent([
            'business' => ['name' => 'Bean House', 'city' => 'Lahore'],
            'preferences' => ['audience' => 'Students', 'cta' => 'Visit today'],
        ], 'Create a back-to-school coffee promotion.', 'Coffee special template');

        $this->assertSame('Fresh coffee, made for you', $result['title']);
        $this->assertSame('Visit us today. #coffee', $result['caption']);

        Http::assertSent(function ($request) {
            $data = $request->data();
            $userInput = $data['input'][1]['content'] ?? '';

            return $request->url() === 'https://api.openai.com/v1/responses'
                && $request->hasHeader('Authorization', 'Bearer test-key')
                && $data['model'] === 'test-model'
                && str_contains($userInput, 'Bean House')
                && str_contains($userInput, 'Students')
                && ($data['text']['format']['type'] ?? null) === 'json_schema';
        });
    }
}
