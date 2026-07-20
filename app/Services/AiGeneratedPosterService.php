<?php

namespace App\Services;

use App\Models\AiGeneratedPoster;
use App\Models\User;
use App\Models\Poster;
use App\Models\Business;
use App\Repositories\AiGeneratedPosterRepository;
use App\Repositories\PosterRepository;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AiGeneratedPosterService
{
    public function __construct(
        protected AiGeneratedPosterRepository $aiPosterRepo,
        protected PosterRepository $posterRepo,
        protected GeminiService $geminiService,
        protected NanoBananaService $nanoBananaService
    ) {}

    /**
     * Get paginated generated posters.
     */
    public function paginateGenerated(int $perPage = 10, ?string $search = null)
    {
        return $this->aiPosterRepo->paginate($perPage, $search);
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
    public function generatePosterWithTemplate(User $user, int $posterId, string $prompt): ?AiGeneratedPoster
    {
        // 1. Fetch Poster
        $poster = $this->posterRepo->find($posterId);
        if (!$poster || $poster->status !== 'Active') {
            Log::warning("Poster template not active or not found: {$posterId}");
            return null;
        }

        // 2. Build Business Context
        $businessInfo = $this->getBusinessContext($user);

        // 3. Convert template image to base64 if it exists locally
        $imageBase64 = null;
        if (!empty($poster->image)) {
            $imagePath = public_path($poster->image);
            if (file_exists($imagePath)) {
                $imageBase64 = base64_encode(file_get_contents($imagePath));
            }
        }

        // 4. Call Gemini for marketing content text
        $result = $this->geminiService->generatePosterContent($businessInfo, $prompt, $imageBase64);

        if (!$result) {
            return null;
        }

        // 5. Use Nano Banana to edit the original template image
        $instructions = $result['marketing_instructions'] ?? $prompt;
        if (is_array($instructions)) {
            $instructions = json_encode($instructions, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        }

        $editedImagePath = $this->nanoBananaService->editPosterTemplate(
            $poster->image,
            $instructions,
            $businessInfo
        );

        if (!$editedImagePath) {
            Log::error("Nano Banana editing failed for poster template {$posterId}");
            return null;
        }

        // 6. Link matching Business from businesses table if found
        $business = Business::where('name', $businessInfo['name'])->first();

        // 7. Save in database
        return $this->aiPosterRepo->create([
            'user_id' => $user->id,
            'business_id' => $business?->id,
            'poster_id' => $poster->id,
            'prompt' => $prompt,
            'generated_title' => $result['title'],
            'generated_caption' => $result['caption'],
            'generated_image' => asset($editedImagePath),
            'status' => 'pending',
        ]);
    }

    /**
     * Generate Poster directly using Prompt only.
     */
    public function generatePosterDirect(User $user, string $prompt): ?AiGeneratedPoster
    {
        // 1. Fetch first active poster template as the base design
        $poster = Poster::where('status', 'Active')->first();
        if (!$poster) {
            Log::warning("No active poster templates found for direct generation.");
            return null;
        }

        // 2. Build Business Context
        $businessInfo = $this->getBusinessContext($user);

        // 3. Convert template image to base64
        $imageBase64 = null;
        if (!empty($poster->image)) {
            $imagePath = public_path($poster->image);
            if (file_exists($imagePath)) {
                $imageBase64 = base64_encode(file_get_contents($imagePath));
            }
        }

        // 4. Call Gemini (without template image context)
        $result = $this->geminiService->generatePosterContent($businessInfo, $prompt, $imageBase64);

        if (!$result) {
            return null;
        }

        // 5. Use Nano Banana to edit the original template image
        $instructions = $result['marketing_instructions'] ?? $prompt;
        if (is_array($instructions)) {
            $instructions = json_encode($instructions, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        }

        $editedImagePath = $this->nanoBananaService->editPosterTemplate(
            $poster->image,
            $instructions,
            $businessInfo
        );

        if (!$editedImagePath) {
            Log::error("Nano Banana editing failed for direct generation using template {$poster->id}");
            return null;
        }

        // 6. Link matching Business if found
        $business = Business::where('name', $businessInfo['name'])->first();

        // 7. Save in database
        return $this->aiPosterRepo->create([
            'user_id' => $user->id,
            'business_id' => $business?->id,
            'poster_id' => $poster->id,
            'prompt' => $prompt,
            'generated_title' => $result['title'],
            'generated_caption' => $result['caption'],
            'generated_image' => asset($editedImagePath),
            'status' => 'pending',
        ]);
    }

    /**
     * Approve AI Poster.
     */
    public function approvePoster(AiGeneratedPoster $poster, int $adminId): bool
    {
        return $this->aiPosterRepo->update($poster, [
            'status' => 'approved',
            'approved_by' => $adminId,
            'approved_at' => now(),
        ]);
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
     * Build rich Business Context from User profile fields & matching Business profile catalog.
     */
    protected function getBusinessContext(User $user): array
    {
        $context = [
            'name' => $user->club_name ?? $user->name,
            'email' => $user->email,
            'phone' => $user->phone ?? '',
            'address' => $user->address ?? '',
            'city' => $user->city ?? '',
            'description' => $user->bio ?? '',
            'business_timing' => $user->working_hours ?? '',
            'facilities' => $user->facilities ?? [],
            'logo' => $user->club_logo ? asset('storage/' . $user->club_logo) : null,
            'cover_image' => $user->profile_image ? asset('storage/' . $user->profile_image) : null,
            'products' => [],
            'services' => [],
            'top_selling_items' => []
        ];

        // Fetch registered business matches to retrieve products, services, and top-selling items
        $business = Business::where('name', $context['name'])->first();
        if ($business) {
            $context['top_selling_items'] = $business->top_selling_items ?? [];
            
            // Load offerings
            $offerings = $business->offerings()->get();
            foreach ($offerings as $offering) {
                if ($offering->type === 'product') {
                    $context['products'][] = $offering->name;
                } else {
                    $context['services'][] = $offering->name;
                }
            }
        }

        return $context;
    }
}
