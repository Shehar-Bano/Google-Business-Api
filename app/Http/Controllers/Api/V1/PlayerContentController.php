<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\ContentPage;
use App\Models\SupportOption;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;

class PlayerContentController extends Controller
{
    public function helpSupport(): JsonResponse
    {
        $options = SupportOption::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get()
            ->map(fn (SupportOption $option) => [
                'id' => $option->id,
                'title' => $option->title,
                'type' => $option->type,
                'value' => $option->value,
                'image' => $this->imageUrl($option->image),
                'is_active' => (bool) $option->is_active,
            ])
            ->values();

        return response()->json([
            'success' => true,
            'message' => 'Help and support options fetched successfully.',
            'data' => $options,
        ]);
    }

    public function privacyPolicy(): JsonResponse
    {
        $page = ContentPage::query()
            ->where('slug', 'privacy-policy')
            ->where('is_active', true)
            ->first();

        return response()->json([
            'success' => true,
            'message' => 'Privacy policy fetched successfully.',
            'data' => [
                'title' => $page?->title ?? 'Privacy Policy',
                'content' => $page?->content ?? '',
                'last_updated' => $page?->updated_at?->toDateString(),
            ],
        ]);
    }

    private function imageUrl(?string $path): ?string
    {
        if (! $path) {
            return null;
        }

        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            return $path;
        }

        return Storage::disk('public')->url($path);
    }
}
