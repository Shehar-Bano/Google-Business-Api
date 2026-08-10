<?php

namespace App\Jobs;

use App\Models\AiGeneratedPoster;
use App\Services\AiGeneratedPosterService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Throwable;

class GenerateAiPoster implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public int $timeout = 300;

    public array $backoff = [30, 60];

    public function __construct(public int $aiGeneratedPosterId)
    {
        $this->onQueue(env('POSTER_QUEUE', 'default'));
    }

    public function handle(AiGeneratedPosterService $posterService): void
    {
        $posterService->processGeneration($this->aiGeneratedPosterId);
    }

    public function failed(Throwable $exception): void
    {
        AiGeneratedPoster::whereKey($this->aiGeneratedPosterId)->update([
            'generation_status' => 'failed',
            'generation_error' => 'Poster generation could not be completed. Please try again.',
        ]);
    }
}
