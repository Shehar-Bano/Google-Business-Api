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

    public function handle(FacebookService $facebookService): void
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
        $result = $facebookService->publishPost($poster->user_id, $caption, $poster->generated_image);

        if ($result && ! empty($result['id'])) {
            $poster->update([
                'published_at' => now(),
                'social_post_id' => $result['id'],
            ]);
            Log::info("PublishPosterToSocialMedia: Poster {$this->aiGeneratedPosterId} successfully published to Facebook. Post ID: {$result['id']}");
        } else {
            Log::warning("PublishPosterToSocialMedia: Poster {$this->aiGeneratedPosterId} publish returned empty or failed result.", ['result' => $result]);
        }
    }

    public function failed(Throwable $exception): void
    {
        Log::error("PublishPosterToSocialMedia failed for poster {$this->aiGeneratedPosterId}: " . $exception->getMessage());
    }
}
