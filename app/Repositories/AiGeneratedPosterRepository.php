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
    public function paginate(int $perPage = 10, ?string $search = null, string $sort = 'created_at', string $direction = 'desc', ?string $status = null): LengthAwarePaginator
    {
        $query = AiGeneratedPoster::query()
            ->select('ai_generated_posters.*')
            ->with(['user', 'business', 'poster']);

        // Handle relation sorting
        if ($sort === 'user') {
            $query->join('users', 'ai_generated_posters.user_id', '=', 'users.id')
                  ->orderBy('users.name', $direction);
        } elseif ($sort === 'business') {
            $query->leftJoin('businesses', 'ai_generated_posters.business_id', '=', 'businesses.id')
                  ->orderBy('businesses.name', $direction);
        } else {
            // Protect column mapping
            $sortColumn = in_array($sort, ['id', 'prompt', 'status', 'created_at'], true) ? $sort : 'created_at';
            $query->orderBy('ai_generated_posters.' . $sortColumn, $direction);
        }

        $query->when($search, function ($q) use ($search) {
            $q->where(function($sub) use ($search) {
                $sub->where('ai_generated_posters.prompt', 'like', "%{$search}%")
                    ->orWhereHas('user', function ($uq) use ($search) {
                        $uq->where('name', 'like', "%{$search}%");
                    })
                    ->orWhereHas('business', function ($bq) use ($search) {
                        $bq->where('name', 'like', "%{$search}%");
                    });
            });
        });

        $query->when($status !== null && $status !== '', function ($q) use ($status) {
            $q->where('ai_generated_posters.status', $status);
        });

        return $query->paginate($perPage);
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
