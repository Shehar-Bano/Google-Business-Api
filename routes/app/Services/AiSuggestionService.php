<?php

namespace App\Services;

class AiSuggestionService
{
    public function __construct(protected OpenAiPosterService $openAiService) {}

    /**
     * Get suggestions based on keyword query.
     */
    public function getSuggestions(string $query): array
    {
        if (empty(trim($query))) {
            return [];
        }

        return $this->openAiService->suggestOfferings($query);
    }
}
