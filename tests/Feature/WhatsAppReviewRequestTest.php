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

        $requests = $response->json('data.requests');
        $this->assertStringContainsString('/r/', $requests[0]['redirection_url']);

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

        $requests = $response->json('data.requests');
        $this->assertStringContainsString('/r/', $requests[0]['redirection_url']);
        $this->assertStringContainsString('/r/', $requests[1]['redirection_url']);

        $this->assertDatabaseHas('review_requests', [
            'business_id' => $this->business->id,
            'sender_id' => (string) $this->user->id,
            'phone_number' => '+923001234567',
            'customer_name' => 'Alice Doe',
            'channel' => 'app',
        ]);

        $this->assertDatabaseHas('review_requests', [
            'business_id' => $this->business->id,
            'sender_id' => (string) $this->user->id,
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

    /**
     * Test getting review requests list with statistics.
     */
    public function test_list_whatsapp_review_requests_success(): void
    {
        // Setup existing review requests
        ReviewRequest::create([
            'business_id' => $this->business->id,
            'sender_id' => (string) $this->user->id,
            'phone_number' => '+923001234567',
            'customer_name' => 'Alice Doe',
            'channel' => 'personal',
            'status' => 'clicked',
            'redirection_url' => 'https://google.com',
            'sent_at' => now(),
            'clicked_at' => now(),
        ]);

        ReviewRequest::create([
            'business_id' => $this->business->id,
            'sender_id' => 'app',
            'phone_number' => '+923007654321',
            'customer_name' => 'Bob Smith',
            'channel' => 'app',
            'status' => 'sent',
            'redirection_url' => 'https://google.com',
            'sent_at' => now(),
        ]);

        $response = $this->withToken($this->token)
            ->getJson('/api/v1/whatsapp/review-requests');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'total_requests' => 2,
                    'sent_via_personal' => 1,
                    'sent_via_app' => 1,
                ]
            ])
            ->assertJsonStructure([
                'success',
                'data' => [
                    'total_requests',
                    'sent_via_personal',
                    'sent_via_app',
                    'requests' => [
                        '*' => [
                            'request_id',
                            'customer_name',
                            'customer_phone',
                            'channel',
                            'status',
                            'redirection_url',
                            'sent_at',
                            'clicked_at',
                            'reminder_sent_at',
                        ]
                    ]
                ]
            ]);

        // Verify redirection_url contains the tracking route format
        $data = $response->json('data.requests');
        $this->assertStringContainsString('/r/', $data[0]['redirection_url']);
    }

    /**
     * Test list returns empty if user has no businesses/requests.
     */
    public function test_list_whatsapp_review_requests_empty_if_no_requests(): void
    {
        $response = $this->withToken($this->token)
            ->getJson('/api/v1/whatsapp/review-requests');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'total_requests' => 0,
                    'sent_via_personal' => 0,
                    'sent_via_app' => 0,
                    'requests' => [],
                ]
            ]);
    }

    /**
     * Test successful personal channel reminder dispatch.
     */
    public function test_send_follow_up_reminders_personal_success(): void
    {
        Queue::fake();

        $req = ReviewRequest::create([
            'business_id' => $this->business->id,
            'sender_id' => (string) $this->user->id,
            'phone_number' => '+923001234567',
            'customer_name' => 'Alice Doe',
            'channel' => 'personal',
            'status' => 'sent',
        ]);

        $payload = [
            'business_id' => $this->business->id,
            'request_ids' => [$req->id],
            'channel' => 'personal',
        ];

        $response = $this->withToken($this->token)
            ->postJson('/api/v1/review-requests/send-reminders', $payload);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Reminders successfully dispatched via Personal.',
                'data' => [
                    'reminders_sent' => 1,
                ]
            ]);

        Queue::assertPushed(SendWhatsAppReviewRequest::class, function ($job) {
            return $job->isReminder === true;
        });
    }

    /**
     * Test successful app channel reminder dispatch for multiple requests.
     */
    public function test_send_follow_up_reminders_app_success(): void
    {
        Queue::fake();

        $req1 = ReviewRequest::create([
            'business_id' => $this->business->id,
            'sender_id' => (string) $this->user->id,
            'phone_number' => '+923001234567',
            'customer_name' => 'Alice Doe',
            'channel' => 'app',
            'status' => 'sent',
        ]);

        $req2 = ReviewRequest::create([
            'business_id' => $this->business->id,
            'sender_id' => (string) $this->user->id,
            'phone_number' => '+923007654321',
            'customer_name' => 'Bob Smith',
            'channel' => 'app',
            'status' => 'sent',
        ]);

        $payload = [
            'business_id' => $this->business->id,
            'request_ids' => [$req1->id, $req2->id],
            'channel' => 'app',
        ];

        $response = $this->withToken($this->token)
            ->postJson('/api/v1/review-requests/send-reminders', $payload);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Reminders successfully dispatched via Application.',
                'data' => [
                    'reminders_sent' => 2,
                ]
            ]);

        Queue::assertPushed(SendWhatsAppReviewRequest::class, 2);
    }

    /**
     * Test reminder validation fails if channel and request_ids size don't match.
     */
    public function test_send_follow_up_reminders_validation_fails_mismatched_channel(): void
    {
        $payload = [
            'business_id' => $this->business->id,
            'request_ids' => [1, 2],
            'channel' => 'personal', // personal requires exactly 1 request_id
        ];

        $response = $this->withToken($this->token)
            ->postJson('/api/v1/review-requests/send-reminders', $payload);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['channel']);
    }
}
