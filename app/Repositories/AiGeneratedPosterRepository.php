<?php

namespace App\Repositories;

use App\Models\AiGeneratedPoster;
use Illuminate\Pagination\LengthAwarePaginator;

class AiGeneratedPosterRepository
{
    /**
     * Find AI Generated Poster by ID.
     */
    public function find(int $id): ?AiGeneratedPoster
    {
        return AiGeneratedPoster::with(['user', 'business', 'poster', 'approver'])->find($id);
    }

    /**
     * Paginate AI Generated Posters for admin.
     */
    public function paginate(int $perPage = 10, ?string $search = null): LengthAwarePaginator
    {
        return AiGeneratedPoster::query()
            ->with(['user', 'business', 'poster'])
            ->when($search, function ($query) use ($search) {
                $query->where('prompt', 'like', "%{$search}%")
                    ->orWhereHas('user', function ($q) use ($search) {
                        $q->where('name', 'like', "%{$search}%");
                    })
                    ->orWhereHas('business', function ($q) use ($search) {
                        $q->where('name', 'like', "%{$search}%");
                    });
            })
            ->latest()
            ->paginate($perPage);
    }

    /**
     * Create an AI Generated Poster entry.
     */
    public function create(array $data): AiGeneratedPoster
    {
        return AiGeneratedPoster::create($data);
    }

    /**
     * Update an AI Generated Poster entry.
     */
    public function update(AiGeneratedPoster $poster, array $data): bool
    {
        return $poster->update($data);
    }

    /**
     * Delete an AI Generated Poster entry.
     */
    public function delete(AiGeneratedPoster $poster): bool
    {
        return $poster->delete();
    }
}
