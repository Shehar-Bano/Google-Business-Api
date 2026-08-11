<?php

namespace App\Services;

use App\Models\AiGeneratedPoster;
use App\Models\User;
use App\Models\Poster;
use App\Jobs\GenerateAiPoster;
use App\Repositories\AiGeneratedPosterRepository;
use App\Repositories\PosterRepository;
use Illuminate\Support\Facades\Log;

class AiGeneratedPosterService
{
    public function __construct(
        protected AiGeneratedPosterRepository $aiPosterRepo,
        protected PosterRepository $posterRepo,
        protected OpenAiPosterService $openAiPosterService
    ) {}

    public function paginateGenerated(int $perPage = 10, ?string $search = null, string $sort = 'created_at', string $direction = 'desc', ?string $status = null)
    {
        return $this->aiPosterRepo->paginate($perPage, $search, $sort, $direction, $status);
    }

    /**
     * Find generated poster by ID.
     */
    public function findGenerated(int $id): ?AiGeneratedPoster
    {
        return $this->aiPosterRepo->find($id);
    }

    /**
     * Generate Poster using a Poster template.
     */
    public function generatePosterWithTemplate(User $user, int $posterId, string $prompt, ?int $businessId = null): ?AiGeneratedPoster
    {
        // 1. Fetch Poster
        $poster = $this->posterRepo->find($posterId);
        if (!$poster || $poster->status !== 'Active') {
            Log::warning("Poster template not active or not found: {$posterId}");
            return null;
        }

        // 2. Build Business Context
        [$business] = $this->getBusinessContext($user, $businessId);

        return $this->queueGeneration($user, $business->id, $poster, $prompt);
    }

    protected function queueGeneration(User $user, int $businessId, Poster $poster, string $prompt): AiGeneratedPoster
    {
        $generated = $this->aiPosterRepo->create([
            'user_id' => $user->id,
            'business_id' => $businessId,
            'poster_id' => $poster->id,
            'prompt' => $prompt,
            'status' => 'pending',
            'generation_status' => 'queued',
        ]);

        GenerateAiPoster::dispatch($generated->id);

        return $generated;
    }

    /**
     * Generate Poster directly using Prompt only.
     */
    public function generatePosterDirect(User $user, string $prompt, ?int $businessId = null): ?AiGeneratedPoster
    {
        // 1. Fetch first active poster template as the base design
        $poster = Poster::where('status', 'Active')->first();
        if (!$poster) {
            Log::warning("No active poster templates found for direct generation.");
            return null;
        }

        // 2. Build Business Context
        [$business] = $this->getBusinessContext($user, $businessId);

        return $this->queueGeneration($user, $business->id, $poster, $prompt);
    }

    /** Process the long-running OpenAI calls outside the HTTP request. */
    public function processGeneration(int $aiGeneratedPosterId): void
    {
        $generated = AiGeneratedPoster::with(['user', 'poster'])->find($aiGeneratedPosterId);
        if (! $generated || $generated->generation_status === 'completed') {
            return;
        }

        if (! $generated->user || ! $generated->poster) {
            $this->markGenerationFailed($generated, 'Required user or poster data is unavailable.');
            return;
        }

        $generated->update(['generation_status' => 'processing', 'generation_error' => null]);

        try {
            [, $businessInfo] = $this->getBusinessContext($generated->user, $generated->business_id);
            $result = $this->openAiPosterService->generatePosterContent($businessInfo, $generated->prompt, $generated->poster->title);

            if (! $result) {
                $this->markGenerationFailed($generated, 'OpenAI could not generate the poster content.');
                return;
            }

            $imagePath = $this->openAiPosterService->editPosterTemplate($generated->poster->image, $businessInfo, $result);
            if (! $imagePath) {
                $this->markGenerationFailed($generated, 'OpenAI could not generate the poster image.');
                return;
            }

            $generated->update([
                'generated_title' => $result['title'],
                'generated_caption' => $result['caption'],
                'generated_image' => $imagePath,
                'generation_status' => 'completed',
                'generation_error' => null,
            ]);
        } catch (\Throwable $exception) {
            Log::error('AI poster background generation failed.', ['poster_id' => $aiGeneratedPosterId, 'message' => $exception->getMessage()]);
            $this->markGenerationFailed($generated, 'Poster generation could not be completed. Please try again.');
        }
    }

    protected function markGenerationFailed(AiGeneratedPoster $generated, string $message): void
    {
        $generated->update(['generation_status' => 'failed', 'generation_error' => $message]);
    }

    /**
     * Approve AI Poster and publish to connected social pages (Facebook Page).
     */
    public function approvePoster(AiGeneratedPoster $poster, int $adminId): bool
    {
        $updated = $this->aiPosterRepo->update($poster, [
            'status' => 'approved',
            'approved_by' => $adminId,
            'approved_at' => now(),
        ]);

        if ($updated) {
            $this->publishToSocialMedia($poster);
        }

        return $updated;
    }

    /**
     * Publish approved poster to connected social channels (Facebook Page).
     */
    public function publishToSocialMedia(AiGeneratedPoster $poster): ?array
    {
        try {
            $facebookService = app(\App\Services\FacebookService::class);
            $caption = $poster->generated_caption ?: ($poster->generated_title ?: $poster->prompt);
            $imagePath = $poster->generated_image;

            return $facebookService->publishPost($poster->user_id, $caption, $imagePath);
        } catch (\Throwable $e) {
            Log::error("Failed to auto-publish approved poster #{$poster->id} to social media: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Reject AI Poster.
     */
    public function rejectPoster(AiGeneratedPoster $poster, string $reason): bool
    {
        return $this->aiPosterRepo->update($poster, [
            'status' => 'rejected',
            'rejection_reason' => $reason,
        ]);
    }

    /**
     * Build business and preference context, restricted to the authenticated user's business.
     */
    protected function getBusinessContext(User $user, ?int $businessId = null): array
    {
        $query = $user->businesses()->with(['preferences', 'offerings', 'topSellingItems']);
        $business = $businessId ? $query->whereKey($businessId)->first() : $query->first();

        if (! $business) {
            throw new \InvalidArgumentException('No business belonging to this user was found.');
        }

        $preferences = $business->preferences;
        $context = [
            'business' => [
                'name' => $business->name,
                'category' => $business->category,
                'location' => $business->location,
                'address' => $business->address,
                'city' => $business->city,
                'state' => $business->state,
                'country' => $business->country,
                'phone' => $business->phone_number,
                'offerings' => $business->offerings->map(fn ($offering) => [
                    'name' => $offering->name,
                    'type' => $offering->type,
                ])->values()->all(),
                'top_selling_items' => $business->topSellingItems->map(fn ($item) => [
                    'name' => $item->item_name,
                    'description' => $item->description,
                    'price' => $item->price,
                ])->values()->all(),
            ],
            'preferences' => $preferences ? $preferences->only([
                'business_tagline', 'business_description', 'different_than_competition', 'why_visit_us',
                'target_gender', 'target_age_group', 'region', 'model_ethnicity', 'audience', 'cta',
                'nearest_landmark', 'guidelines_to_customer',
            ]) : [],
        ];

        return [$business, $context];
    }
}
