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
     * Test getting review requests list with statistics and reminder sent timestamp.
     */
    public function test_list_whatsapp_review_requests_success(): void
    {
        // Setup existing review requests
        $req1 = ReviewRequest::create([
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

        $req2 = ReviewRequest::create([
            'business_id' => $this->business->id,
            'sender_id' => 'app',
            'phone_number' => '+923007654321',
            'customer_name' => 'Bob Smith',
            'channel' => 'app',
            'status' => 'reminder_sent',
            'redirection_url' => 'https://google.com',
            'sent_at' => now(),
        ]);

        // Insert reminder entry
        \Illuminate\Support\Facades\DB::table('request_reminders')->insert([
            'request_id' => $req2->id,
            'sent_by' => $this->user->id,
            'channel' => 'app',
            'created_at' => '2026-07-27 12:00:00',
            'updated_at' => '2026-07-27 12:00:00',
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
            ]);

        $data = $response->json('data.requests');
        // Since list is ordered by id desc, the second created request is index 0
        $this->assertEquals($req2->id, $data[0]['request_id']);
        $this->assertEquals('2026-07-27 12:00:00', $data[0]['reminder_sent_at']);
        $this->assertEquals(1, $data[0]['reminders_count']);
        $this->assertCount(1, $data[0]['reminders']);
        $this->assertEquals($this->user->id, $data[0]['reminders'][0]['sent_by']);
        $this->assertEquals('app', $data[0]['reminders'][0]['channel']);

        $this->assertNull($data[1]['reminder_sent_at']);
        $this->assertEquals(0, $data[1]['reminders_count']);
        $this->assertCount(0, $data[1]['reminders']);

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
     * Test personal channel reminder dispatch fails since only 'app' is allowed.
     */
    public function test_send_follow_up_reminders_personal_channel_fails(): void
    {
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

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['channel']);
    }

    /**
     * Test successful reminder dispatch via 'app' channel for a single request.
     */
    public function test_send_follow_up_reminders_app_single_success(): void
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
            'channel' => 'app',
        ];

        $response = $this->withToken($this->token)
            ->postJson('/api/v1/review-requests/send-reminders', $payload);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Reminders successfully dispatched via Application.',
                'data' => [
                    'reminders_sent' => 1,
                ]
            ]);

        $req->refresh();
        $this->assertEquals('personal', $req->channel);
        $this->assertEquals('requested', $req->status);

        Queue::assertPushed(SendWhatsAppReviewRequest::class, function ($job) {
            return $job->isReminder === true && $job->sentByUserId === $this->user->id;
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
     * Test reminder validation fails if a request already has 3 reminders sent.
     */
    public function test_send_follow_up_reminders_fails_if_exceeds_limit(): void
    {
        $req = ReviewRequest::create([
            'business_id' => $this->business->id,
            'sender_id' => (string) $this->user->id,
            'phone_number' => '+923001234567',
            'customer_name' => 'Alice Doe',
            'channel' => 'app',
            'status' => 'sent',
        ]);

        // Insert 3 reminder records for this request
        for ($i = 0; $i < 3; $i++) {
            \Illuminate\Support\Facades\DB::table('request_reminders')->insert([
                'request_id' => $req->id,
                'sent_by' => $this->user->id,
                'channel' => 'app',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        $payload = [
            'business_id' => $this->business->id,
            'request_ids' => [$req->id],
            'channel' => 'app',
        ];

        $response = $this->withToken($this->token)
            ->postJson('/api/v1/review-requests/send-reminders', $payload);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['request_ids.0']);
    }

    /**
     * Test redirection does not update status if requested by a crawler/bot.
     */
    public function test_redirection_does_not_update_status_if_crawler(): void
    {
        $req = ReviewRequest::create([
            'business_id' => $this->business->id,
            'sender_id' => (string) $this->user->id,
            'phone_number' => '+923001234567',
            'customer_name' => 'Alice Doe',
            'channel' => 'app',
            'status' => 'sent',
        ]);

        // Send request with WhatsApp User-Agent
        $response = $this->withHeaders([
            'User-Agent' => 'WhatsApp/2.21.12.21 A',
        ])->get("/r/{$req->id}");

        $response->assertRedirect();
        
        // Assert status is still 'sent'
        $req->refresh();
        $this->assertEquals('sent', $req->status);
        $this->assertNull($req->clicked_at);

        // Send request with standard browser User-Agent
        $responseBrowser = $this->withHeaders([
            'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64)',
        ])->get("/r/{$req->id}");

        $responseBrowser->assertRedirect();

        // Assert status is now 'clicked'
        $req->refresh();
        $this->assertEquals('clicked', $req->status);
        $this->assertNotNull($req->clicked_at);
    }
}
