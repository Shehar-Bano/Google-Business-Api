<?php

namespace App\Jobs;

use App\Models\AiGeneratedPoster;
use App\Services\AiGeneratedPosterService;
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

    public function __construct(
        public int $aiGeneratedPosterId,
        public ?array $platforms = null
    ) {
        $this->onQueue(env('POSTER_QUEUE', 'default'));
    }

    public function handle(AiGeneratedPosterService $posterService): void
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

        Log::info("PublishPosterToSocialMedia Job executing for Poster {$this->aiGeneratedPosterId}", [
            'platforms' => $this->platforms
        ]);

        $socialResult = $posterService->publishToSocialMedia($poster, $this->platforms);

        Log::info("PublishPosterToSocialMedia Job completed.", [
            'poster_id' => $this->aiGeneratedPosterId,
            'result' => $socialResult
        ]);
    }

    public function failed(Throwable $exception): void
    {
        Log::error("PublishPosterToSocialMedia failed for poster {$this->aiGeneratedPosterId}: " . $exception->getMessage());
    }
}
