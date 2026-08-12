<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\AiGeneratedPosterService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PosterController extends Controller
{
    public function __construct(protected AiGeneratedPosterService $aiPosterService) {}

    /**
     * Generate poster with template.
     * POST /api/v1/business/generate-poster
     */
    public function generateWithTemplate(Request $request): JsonResponse
    {
        $request->validate([
            'poster_id' => 'required|integer|exists:posters,id',
            'prompt' => 'nullable|string|max:2000',
            'business_id' => 'nullable|integer',
        ]);

        $user = $request->user();
        if (! $user) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized.',
            ], 401);
        }

        try {
            $prompt = $request->input('prompt') ?: 'Generate a high-quality marketing poster matching this template design.';
            $generated = $this->aiPosterService->generatePosterWithTemplate(
                $user,
                (int) $request->input('poster_id'),
                $prompt,
                $request->integer('business_id') ?: null
            );

            if (! $generated) {
                return response()->json([
                    'success' => false,
                    'message' => 'Could not queue poster generation.',
                ], 500);
            }

            $generated->refresh();

            return response()->json([
                'success' => true,
                'message' => $generated->generation_status === 'completed' ? 'Poster generated successfully.' : 'Poster generation queued.',
                'data' => [
                    'id' => $generated->id,
                    'generation_status' => $generated->generation_status,
                    'status' => $generated->status,
                    'title' => $generated->generated_title,
                    'caption' => $generated->generated_caption,
                    'image' => $generated->generated_image,
                    'error' => $generated->generation_error,
                    'status_url' => url("/api/v1/business/generated-posters/{$generated->id}"),
                ],
            ], $generated->generation_status === 'completed' ? 200 : 202);

        } catch (\InvalidArgumentException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        } catch (\Exception $e) {
            Log::error('API generateWithTemplate Error: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Error generating poster: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * Generate poster directly using prompt (without template).
     * POST /api/v1/business/generate-poster-direct
     */
    public function generateDirect(Request $request): JsonResponse
    {
        $request->validate([
            'prompt' => 'required|string|min:5|max:2000',
            'business_id' => 'nullable|integer',
        ]);

        $user = $request->user();
        if (! $user) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized.',
            ], 401);
        }

        try {
            $generated = $this->aiPosterService->generatePosterDirect(
                $user,
                $request->input('prompt'),
                $request->integer('business_id') ?: null
            );

            if (! $generated) {
                return response()->json([
                    'success' => false,
                    'message' => 'Could not queue poster generation.',
                ], 500);
            }

            $generated->refresh();

            return response()->json([
                'success' => true,
                'message' => $generated->generation_status === 'completed' ? 'Poster generated successfully.' : 'Poster generation queued.',
                'data' => [
                    'id' => $generated->id,
                    'generation_status' => $generated->generation_status,
                    'status' => $generated->status,
                    'title' => $generated->generated_title,
                    'caption' => $generated->generated_caption,
                    'image' => $generated->generated_image,
                    'error' => $generated->generation_error,
                    'status_url' => url("/api/v1/business/generated-posters/{$generated->id}"),
                ],
            ], $generated->generation_status === 'completed' ? 200 : 202);

        } catch (\InvalidArgumentException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        } catch (\Exception $e) {
            Log::error('API generateDirect Error: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Error generating poster: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * Approve AI generated poster.
     * POST /api/v1/business/generated-posters/{id}/approve
     */
    public function approve(Request $request, $id): JsonResponse
    {
        $user = $request->user();
        if (! $user) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized.',
            ], 401);
        }

        $generated = \App\Models\AiGeneratedPoster::find($id);
        if (! $generated) {
            return response()->json([
                'success' => false,
                'message' => 'Generated poster not found.',
            ], 404);
        }

        // Check if date and/or time are provided in the request
        $scheduledAt = null;
        if ($request->filled('scheduled_at')) {
            try {
                $scheduledAt = \Carbon\Carbon::parse($request->input('scheduled_at'));
            } catch (\Throwable $e) {}
        } elseif ($request->filled('scheduled_date')) {
            $time = $request->input('scheduled_time', '00:00:00');
            try {
                $scheduledAt = \Carbon\Carbon::parse($request->input('scheduled_date') . ' ' . $time);
            } catch (\Throwable $e) {}
        } elseif ($request->filled('date')) {
            $time = $request->input('time', '00:00:00');
            try {
                $scheduledAt = \Carbon\Carbon::parse($request->input('date') . ' ' . $time);
            } catch (\Throwable $e) {}
        }

        $isScheduled = $scheduledAt && $scheduledAt->isFuture();

        $generated->update([
            'status' => 'approved',
            'approved_by' => $user->id,
            'approved_at' => now(),
            'scheduled_at' => $isScheduled ? $scheduledAt : now(),
        ]);

        if ($isScheduled) {
            // Queue Job dispatched with delay for scheduled time
            \App\Jobs\PublishPosterToSocialMedia::dispatch($generated->id)->delay($scheduledAt);

            return response()->json([
                'success' => true,
                'message' => 'Poster approved and scheduled for publishing at ' . $scheduledAt->format('Y-m-d H:i:s') . '.',
                'data' => $generated->fresh(),
                'is_scheduled' => true,
                'scheduled_at' => $scheduledAt->format('Y-m-d H:i:s'),
            ]);
        }

        // Immediate publishing (Now)
        $socialResult = null;
        try {
            $posterService = app(\App\Services\AiGeneratedPosterService::class);
            $socialResult = $posterService->publishToSocialMedia($generated);
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error("Social auto-publish failed on poster approve: " . $e->getMessage());
        }

        return response()->json([
            'success' => true,
            'message' => 'Poster approved and published successfully.',
            'data' => $generated->fresh(),
            'is_scheduled' => false,
            'social_published' => ! empty($socialResult['facebook']) || ! empty($socialResult['instagram']),
            'social_publish_data' => $socialResult,
        ]);
    }

    /**
     * Reject AI generated poster.
     * POST /api/v1/business/generated-posters/{id}/reject
     */
    public function reject(Request $request, $id): JsonResponse
    {
        $user = $request->user();
        if (! $user) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized.',
            ], 401);
        }

        $generated = \App\Models\AiGeneratedPoster::find($id);
        if (! $generated) {
            return response()->json([
                'success' => false,
                'message' => 'Generated poster not found.',
            ], 404);
        }

        $request->validate([
            'rejection_reason' => 'nullable|string|max:1000',
        ]);

        $generated->update([
            'status' => 'rejected',
            'rejection_reason' => $request->input('rejection_reason'),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Poster rejected successfully.',
            'data' => $generated,
        ]);
    }

    /** GET /api/v1/business/generated-posters/{id} */
    public function generationStatus(Request $request, int $id): JsonResponse
    {
        $generated = \App\Models\AiGeneratedPoster::whereKey($id)
            ->where('user_id', $request->user()->id)
            ->first();

        if (! $generated) {
            return response()->json(['success' => false, 'message' => 'Generated poster not found.'], 404);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $generated->id,
                'generation_status' => $generated->generation_status,
                'status' => $generated->status,
                'error' => $generated->generation_status === 'failed' ? $generated->generation_error : null,
                'title' => $generated->generation_status === 'completed' ? $generated->generated_title : null,
                'caption' => $generated->generation_status === 'completed' ? $generated->generated_caption : null,
                'image' => $generated->generation_status === 'completed' ? $generated->generated_image : null,
            ],
        ]);
    }

    /**
     * Get active poster templates.
     * GET /api/v1/business/posters
     */
    public function indexTemplates(Request $request): JsonResponse
    {
        $templates = \App\Models\Poster::where('status', 'Active')
            ->select('id', 'title', 'image')
            ->get()
            ->map(function ($template) {
                return [
                    'id' => $template->id,
                    'title' => $template->title,
                    'image' => asset($template->image),
                ];
            });

        return response()->json([
            'success' => true,
            'data' => $templates,
        ]);
    }

    /**
     * Get user's AI generated posters with social publishing status.
     * GET /api/v1/business/generated-posters
     */
    public function indexGenerated(Request $request): JsonResponse
    {
        $user = $request->user();
        if (! $user && $request->bearerToken()) {
            $hashed = hash('sha256', $request->bearerToken());
            $user = \App\Models\User::where('api_access_token', $hashed)->first();
        }

        if (! $user) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized.',
            ], 401);
        }

        $posters = \App\Models\AiGeneratedPoster::where('user_id', $user->id)
            ->with(['poster', 'latestSocialPublish'])
            ->orderBy('id', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $posters,
        ]);
    }

    /**
     * Get social publishing logs for user's generated posters.
     * GET /api/v1/business/poster-publish-logs
     * GET /api/v1/business/posters/{posterId}/publish-logs
     */
    public function getPublishLogs(Request $request, ?int $posterId = null): JsonResponse
    {
        $user = $request->user();
        if (! $user && $request->bearerToken()) {
            $hashed = hash('sha256', $request->bearerToken());
            $user = \App\Models\User::where('api_access_token', $hashed)->first();
        }

        if (! $user) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized.',
            ], 401);
        }

        $query = \App\Models\PosterSocialPublish::where('user_id', $user->id)
            ->with(['aiGeneratedPoster:id,prompt,generated_title,generated_image,status,scheduled_at,published_at'])
            ->orderBy('id', 'desc');

        if ($posterId) {
            $query->where('ai_generated_post_id', $posterId);
        }

        $logs = $query->get();

        return response()->json([
            'success' => true,
            'data' => $logs,
        ]);
    }
}
