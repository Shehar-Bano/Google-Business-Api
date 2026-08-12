<?php

namespace App\Jobs;

use App\Models\AiGeneratedPoster;
use App\Services\FacebookService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Throwable;

class PublishPosterToSocialMedia implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public int $timeout = 180;

    public array $backoff = [30, 60];

    public function __construct(public int $aiGeneratedPosterId)
    {
        $this->onQueue(env('POSTER_QUEUE', 'default'));
    }

    public function handle(FacebookService $facebookService, \App\Services\InstagramService $instagramService): void
    {
        $poster = AiGeneratedPoster::find($this->aiGeneratedPosterId);
        if (! $poster || $poster->status !== 'approved') {
            Log::info("PublishPosterToSocialMedia: Poster {$this->aiGeneratedPosterId} not found or not approved.");
            return;
        }

        // Avoid double publishing if already published
        if ($poster->published_at && $poster->social_post_id) {
            Log::info("PublishPosterToSocialMedia: Poster {$this->aiGeneratedPosterId} already published at {$poster->published_at}.");
            return;
        }

        $caption = $poster->generated_caption ?: ($poster->generated_title ?: $poster->prompt);
        
        // 1. Publish to Facebook Page
        $fbResult = $facebookService->publishPost($poster->user_id, $caption, $poster->generated_image);

        // 2. Publish to linked Instagram Account
        $igResult = $instagramService->publishPost($poster->user_id, $caption, $poster->generated_image);

        $postId = $fbResult['id'] ?? ($igResult['id'] ?? null);

        if ($postId) {
            $poster->update([
                'published_at' => now(),
                'social_post_id' => $postId,
            ]);
            Log::info("PublishPosterToSocialMedia: Poster {$this->aiGeneratedPosterId} successfully published. FB Post ID: " . ($fbResult['id'] ?? 'none') . ", IG Post ID: " . ($igResult['id'] ?? 'none'));
        } else {
            Log::warning("PublishPosterToSocialMedia: Poster {$this->aiGeneratedPosterId} publish returned empty or failed result.", ['fb' => $fbResult, 'ig' => $igResult]);
        }
    }

    public function failed(Throwable $exception): void
    {
        Log::error("PublishPosterToSocialMedia failed for poster {$this->aiGeneratedPosterId}: " . $exception->getMessage());
    }
}
