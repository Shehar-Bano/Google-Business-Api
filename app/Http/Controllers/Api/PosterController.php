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
            $generated = $this->aiPosterService->generatePosterWithTemplate(
                $user,
                (int) $request->input('poster_id'),
                $request->input('prompt')
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
}
