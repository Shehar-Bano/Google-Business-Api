<?php

namespace App\Repositories;

use App\Models\Poster;
use Illuminate\Pagination\LengthAwarePaginator;

class PosterRepository
{
    /**
     * Get all active posters.
     */
    public function getActivePosters()
    {
        return Poster::where('status', 'Active')->get();
    }

    /**
     * Find a poster by ID.
     */
    public function find(int $id): ?Poster
    {
        return Poster::find($id);
    }

    /**
     * Paginated list of posters for admin.
     */
    public function paginate(int $perPage = 10, ?string $search = null): LengthAwarePaginator
    {
        return Poster::query()
            ->when($search, function ($query) use ($search) {
                $query->where('title', 'like', "%{$search}%");
            })
            ->latest()
            ->paginate($perPage);
    }

    /**
     * Create a poster.
     */
    public function create(array $data): Poster
    {
        return Poster::create($data);
    }

    /**
     * Update a poster.
     */
    public function update(Poster $poster, array $data): bool
    {
        return $poster->update($data);
    }

    /**
     * Delete a poster.
     */
    public function delete(Poster $poster): bool
    {
        return $poster->delete();
    }
}
