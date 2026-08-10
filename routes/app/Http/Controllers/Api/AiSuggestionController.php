<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\AiSuggestionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AiSuggestionController extends Controller
{
    public function __construct(protected AiSuggestionService $suggestionService) {}

    /**
     * Get related business product/service suggestions.
     * POST /api/ai/suggestions
     */
    public function getSuggestions(Request $request): JsonResponse
    {
        $request->validate([
            'query' => 'required|string|min:1|max:255',
        ]);

        $query = $request->input('query');
        $suggestions = $this->suggestionService->getSuggestions($query);

        return response()->json([
            'success' => true,
            'suggestions' => $suggestions,
        ], 200);
    }
}
