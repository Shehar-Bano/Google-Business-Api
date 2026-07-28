<?php

namespace App\Services;

use App\Models\Poster;
use App\Repositories\PosterRepository;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class PosterService
{
    public function __construct(protected PosterRepository $posterRepo) {}

    /**
     * Get paginated poster list.
     */
    public function paginatePosters(int $perPage = 10, ?string $search = null, string $sort = 'created_at', string $direction = 'desc')
    {
        return $this->posterRepo->paginate($perPage, $search, $sort, $direction);
    }

    /**
     * Get active poster templates.
     */
    public function getActiveTemplates()
    {
        return $this->posterRepo->getActivePosters();
    }

    /**
     * Find template by ID.
     */
    public function findPoster(int $id): ?Poster
    {
        return $this->posterRepo->find($id);
    }

    /**
     * Store new poster template.
     */
    public function storePoster(array $data, ?UploadedFile $imageFile): Poster
    {
        if ($imageFile) {
            $path = $imageFile->store('poster-templates', 'public');
            $data['image'] = 'storage/' . $path;
        }

        return $this->posterRepo->create($data);
    }

    /**
     * Update existing poster template.
     */
    public function updatePoster(Poster $poster, array $data, ?UploadedFile $imageFile): bool
    {
        if ($imageFile) {
            // Delete old file if exists
            $oldPath = str_replace('storage/', '', $poster->image);
            if (Storage::disk('public')->exists($oldPath)) {
                Storage::disk('public')->delete($oldPath);
            }

            $path = $imageFile->store('poster-templates', 'public');
            $data['image'] = 'storage/' . $path;
        }

        return $this->posterRepo->update($poster, $data);
    }

    /**
     * Delete poster template.
     */
    public function deletePoster(Poster $poster): bool
    {
        $oldPath = str_replace('storage/', '', $poster->image);
        if (Storage::disk('public')->exists($oldPath)) {
            Storage::disk('public')->delete($oldPath);
        }

        return $this->posterRepo->delete($poster);
    }
}
