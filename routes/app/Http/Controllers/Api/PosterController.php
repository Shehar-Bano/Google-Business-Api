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

            return response()->json([
                'success' => true,
                'message' => 'Poster generation queued.',
                'data' => [
                    'id' => $generated->id,
                    'generation_status' => $generated->generation_status,
                    'status_url' => url("/api/v1/business/generated-posters/{$generated->id}"),
                ],
            ], 202);

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

            return response()->json([
                'success' => true,
                'message' => 'Poster generation queued.',
                'data' => [
                    'id' => $generated->id,
                    'generation_status' => $generated->generation_status,
                    'status_url' => url("/api/v1/business/generated-posters/{$generated->id}"),
                ],
            ], 202);

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

        $generated->update([
            'status' => 'approved',
            'approved_by' => $user->id,
            'approved_at' => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Poster approved successfully.',
            'data' => $generated,
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
     * Get user's AI generated posters.
     * GET /api/v1/business/generated-posters
     */
    public function indexGenerated(Request $request): JsonResponse
    {
        $user = $request->user();
        if (! $user) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized.',
            ], 401);
        }

        $posters = \App\Models\AiGeneratedPoster::where('user_id', $user->id)
            ->with(['poster'])
            ->orderBy('id', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $posters,
        ]);
    }
}
