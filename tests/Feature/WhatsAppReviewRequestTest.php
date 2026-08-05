<?php

namespace Tests\Feature;

use App\Models\Business;
use App\Models\User;
use App\Models\ReviewRequest;
use App\Jobs\SendWhatsAppReviewRequest;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class WhatsAppReviewRequestTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected Business $business;
    protected string $token = 'test-bearer-token';

    protected function setUp(): void
    {
        parent::setUp();

        // Create authenticated user with token
        $this->user = User::factory()->create([
            'phone' => '+923001111111',
            'api_access_token' => hash('sha256', $this->token),
        ]);

        // Create a business belonging to this user
        $this->business = Business::create([
            'user_id' => $this->user->id,
            'name' => 'Pizza House',
            'location' => 'Lahore, Pakistan',
            'google_place_id' => 'ChIJT7T_8v0DxkcR3A7NnB5V9yA',
        ]);
    }

    /**
     * Test successful personal channel review request.
     */
    public function test_send_whatsapp_review_request_personal_channel_success(): void
    {
        Queue::fake();

        $payload = [
            'business_id' => $this->business->id,
            'channel' => 'personal',
            'message' => 'Please rate us!',
            'customers' => [
                [
                    'name' => 'Alice Doe',
                    'phone' => '+923001234567',
                ]
            ]
        ];

        $response = $this->withToken($this->token)
            ->postJson('/api/v1/whatsapp/review-request', $payload);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Review requests queued successfully.',
                'data' => [
                    'count' => 1,
                ]
            ]);

        $this->assertDatabaseHas('review_requests', [
            'business_id' => $this->business->id,
            'sender_id' => (string) $this->user->id,
            'phone_number' => '+923001234567',
            'customer_name' => 'Alice Doe',
            'channel' => 'personal',
            'status' => 'requested',
        ]);

        Queue::assertPushed(SendWhatsAppReviewRequest::class, 1);
    }

    /**
     * Test personal channel fails if multiple customers are provided.
     */
    public function test_send_whatsapp_review_request_personal_channel_fails_if_multiple_customers(): void
    {
        $payload = [
            'business_id' => $this->business->id,
            'channel' => 'personal',
            'customers' => [
                [
                    'name' => 'Alice Doe',
                    'phone' => '+923001234567',
                ],
                [
                    'name' => 'Bob Smith',
                    'phone' => '+923007654321',
                ]
            ]
        ];

        $response = $this->withToken($this->token)
            ->postJson('/api/v1/whatsapp/review-request', $payload);

        $response->assertStatus(422)
            ->assertJson([
                'success' => false,
                'message' => 'Validation failed.',
            ])
            ->assertJsonValidationErrors(['customers']);
    }

    /**
     * Test successful app channel review request with multiple customers.
     */
    public function test_send_whatsapp_review_request_app_channel_success(): void
    {
        Queue::fake();

        $payload = [
            'business_id' => $this->business->id,
            'channel' => 'app',
            'customers' => [
                [
                    'name' => 'Alice Doe',
                    'phone' => '+923001234567',
                ],
                [
                    'name' => 'Bob Smith',
                    'phone' => '+923007654321',
                ]
            ]
        ];

        $response = $this->withToken($this->token)
            ->postJson('/api/v1/whatsapp/review-request', $payload);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'count' => 2,
                ]
            ]);

        $this->assertDatabaseHas('review_requests', [
            'business_id' => $this->business->id,
            'sender_id' => 'app',
            'phone_number' => '+923001234567',
            'customer_name' => 'Alice Doe',
            'channel' => 'app',
        ]);

        $this->assertDatabaseHas('review_requests', [
            'business_id' => $this->business->id,
            'sender_id' => 'app',
            'phone_number' => '+923007654321',
            'customer_name' => 'Bob Smith',
            'channel' => 'app',
        ]);

        Queue::assertPushed(SendWhatsAppReviewRequest::class, 2);
    }

    /**
     * Test failure when empty customers array is provided.
     */
    public function test_send_whatsapp_review_request_fails_if_empty_customers(): void
    {
        $payload = [
            'business_id' => $this->business->id,
            'channel' => 'app',
            'customers' => []
        ];

        $response = $this->withToken($this->token)
            ->postJson('/api/v1/whatsapp/review-request', $payload);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['customers']);
    }
}
