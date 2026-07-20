<?php

namespace App\Services;

class AiSuggestionService
{
    public function __construct(protected GeminiService $geminiService) {}

    /**
     * Get suggestions based on keyword query.
     */
    public function getSuggestions(string $query): array
    {
        if (empty(trim($query))) {
            return [];
        }

        return $this->geminiService->suggestOfferings($query);
    }
}
