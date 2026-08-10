<?php

namespace App\Services\Api\V1;

use App\Models\ContentPage;
use App\Models\SupportOption;
use App\Models\Video;
use Illuminate\Database\Eloquent\Collection;

class ContentService
{
    public function helpSupport(): Collection
    {
        return SupportOption::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();
    }

    public function privacyPolicy(): ContentPage
    {
        return ContentPage::query()
            ->where('slug', 'privacy-policy')
            ->where('is_active', true)
            ->first() ?? new ContentPage([
                'slug' => 'privacy-policy',
                'title' => 'Privacy Policy',
                'content' => '',
                'is_active' => true,
            ]);
    }

    public function videos(): array
    {
        return Video::query()
            ->where('is_active', true)
            ->pluck('video_url', 'screen_name')
            ->toArray();
    }
}
