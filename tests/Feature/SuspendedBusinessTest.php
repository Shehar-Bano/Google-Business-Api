<?php

namespace Tests\Feature;

use App\Models\AiGeneratedPoster;
use App\Models\Business;
use App\Models\Poster;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SuspendedBusinessTest extends TestCase
{
    use RefreshDatabase;

    protected $user;
    protected $token = 'test-suspended-business-token';
    protected $suspendedBusiness;
    protected $activePosterTemplate;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create([
            'api_access_token' => hash('sha256', $this->token),
            'status' => 'active',
        ]);

        $this->suspendedBusiness = Business::create([
            'user_id' => $this->user->id,
            'name' => 'Suspended Business Corp',
            'location' => 'New York, USA',
            'status' => 'suspended',
        ]);

        $this->activePosterTemplate = Poster::create([
            'title' => 'Summer Sale',
            'image' => 'templates/summer.png',
            'status' => 'Active',
        ]);
    }

    /**
     * Test suspended business cannot delete preferences.
     */
    public function test_suspended_business_cannot_delete_preferences(): void
    {
        // Setup preferences for the business
        $this->suspendedBusiness->preferences()->create([
            'business_tagline' => 'Suspended and Sad',
        ]);

        $response = $this->withToken($this->token)
            ->deleteJson("/api/v1/businesses/{$this->suspendedBusiness->id}/preferences");

        $response->assertStatus(403)
            ->assertJson([
                'success' => false,
                'message' => 'Your business has been suspended. Please contact support.',
                'error_code' => 'BUSINESS_SUSPENDED',
                'status' => 'suspended',
            ]);
    }

    /**
     * Test suspended business cannot generate poster with template.
     */
    public function test_suspended_business_cannot_generate_poster_with_template(): void
    {
        $payload = [
            'poster_id' => $this->activePosterTemplate->id,
            'prompt' => 'Cool discount poster',
            'business_id' => $this->suspendedBusiness->id,
        ];

        $response = $this->withToken($this->token)
            ->postJson('/api/v1/business/generate-poster', $payload);

        $response->assertStatus(403)
            ->assertJson([
                'success' => false,
                'message' => 'Your business has been suspended. Please contact support.',
                'error_code' => 'BUSINESS_SUSPENDED',
                'status' => 'suspended',
            ]);
    }

    /**
     * Test suspended business cannot generate poster direct.
     */
    public function test_suspended_business_cannot_generate_poster_direct(): void
    {
        $payload = [
            'prompt' => 'Direct discount poster',
            'business_id' => $this->suspendedBusiness->id,
        ];

        $response = $this->withToken($this->token)
            ->postJson('/api/v1/business/generate-poster-direct', $payload);

        $response->assertStatus(403)
            ->assertJson([
                'success' => false,
                'message' => 'Your business has been suspended. Please contact support.',
                'error_code' => 'BUSINESS_SUSPENDED',
                'status' => 'suspended',
            ]);
    }

    /**
     * Test suspended business cannot approve poster.
     */
    public function test_suspended_business_cannot_approve_poster(): void
    {
        $generated = AiGeneratedPoster::create([
            'user_id' => $this->user->id,
            'business_id' => $this->suspendedBusiness->id,
            'poster_id' => $this->activePosterTemplate->id,
            'prompt' => 'A poster to approve',
            'status' => 'pending',
            'generation_status' => 'completed',
        ]);

        $response = $this->withToken($this->token)
            ->postJson("/api/v1/business/generated-posters/{$generated->id}/approve");

        $response->assertStatus(403)
            ->assertJson([
                'success' => false,
                'message' => 'Your business has been suspended. Please contact support.',
                'error_code' => 'BUSINESS_SUSPENDED',
                'status' => 'suspended',
            ]);
    }

    /**
     * Test suspended business cannot reject poster.
     */
    public function test_suspended_business_cannot_reject_poster(): void
    {
        $generated = AiGeneratedPoster::create([
            'user_id' => $this->user->id,
            'business_id' => $this->suspendedBusiness->id,
            'poster_id' => $this->activePosterTemplate->id,
            'prompt' => 'A poster to reject',
            'status' => 'pending',
            'generation_status' => 'completed',
        ]);

        $response = $this->withToken($this->token)
            ->postJson("/api/v1/business/generated-posters/{$generated->id}/reject");

        $response->assertStatus(403)
            ->assertJson([
                'success' => false,
                'message' => 'Your business has been suspended. Please contact support.',
                'error_code' => 'BUSINESS_SUSPENDED',
                'status' => 'suspended',
            ]);
}
