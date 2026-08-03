<?php

namespace Tests\Feature;

use App\Models\AiGeneratedPoster;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PosterGenerationStatusTest extends TestCase
{
    use RefreshDatabase;

    public function test_owner_can_poll_a_queued_poster_without_waiting_for_generation(): void
    {
        $token = 'poster-status-token';
        $user = User::factory()->create(['api_access_token' => hash('sha256', $token)]);
        $generated = AiGeneratedPoster::create([
            'user_id' => $user->id,
            'prompt' => 'Create a summer promotion.',
            'status' => 'pending',
            'generation_status' => 'queued',
        ]);

        $this->withToken($token)
            ->getJson("/api/v1/business/generated-posters/{$generated->id}")
            ->assertOk()
            ->assertJsonPath('data.id', $generated->id)
            ->assertJsonPath('data.generation_status', 'queued')
            ->assertJsonPath('data.image', null);
    }
}
