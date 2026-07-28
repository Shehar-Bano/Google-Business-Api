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
        ]);

        $user = $request->user();
        if (!$user) {
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
                $prompt
            );

            if (!$generated) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to generate poster. Template may be inactive or Gemini error occurred.',
                ], 500);
            }

            return response()->json([
                'success' => true,
                'message' => 'Poster generated successfully.',
                'data' => [
                    'id' => $generated->id,
                    'status' => $generated->status,
                    'title' => $generated->generated_title,
                    'caption' => $generated->generated_caption,
                    'image' => $generated->generated_image,
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('API generateWithTemplate Error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error generating poster: ' . $e->getMessage(),
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
        ]);

        $user = $request->user();
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized.',
            ], 401);
        }

        try {
            $generated = $this->aiPosterService->generatePosterDirect(
                $user,
                $request->input('prompt')
            );

            if (!$generated) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to generate poster. Gemini error occurred.',
                ], 500);
            }

            return response()->json([
                'success' => true,
                'message' => 'Poster generated successfully.',
                'data' => [
                    'id' => $generated->id,
                    'status' => $generated->status,
                    'title' => $generated->generated_title,
                    'caption' => $generated->generated_caption,
                    'image' => $generated->generated_image,
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('API generateDirect Error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error generating poster: ' . $e->getMessage(),
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
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized.',
            ], 401);
        }

        $generated = \App\Models\AiGeneratedPoster::find($id);
        if (!$generated) {
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
            'data' => $generated
        ]);
    }

    /**
     * Reject AI generated poster.
     * POST /api/v1/business/generated-posters/{id}/reject
     */
    public function reject(Request $request, $id): JsonResponse
    {
        $user = $request->user();
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized.',
            ], 401);
        }

        $generated = \App\Models\AiGeneratedPoster::find($id);
        if (!$generated) {
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
            'data' => $generated
        ]);
    }
}
