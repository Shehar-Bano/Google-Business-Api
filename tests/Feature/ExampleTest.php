<?php

namespace Tests\Feature;

// use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    /**
     * A basic test example.
     */
    public function test_the_application_returns_a_successful_response(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);
    }

    public function test_openai_config_endpoint(): void
    {
        config(['services.openai.api_key' => 'mock-key']);

        $response = $this->getJson('/api/v1/config/openai');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'openai_key' => 'mock-key',
            ]);
    }
}
