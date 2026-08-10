<?php

namespace Database\Seeders;

use App\Models\Video;
use Illuminate\Database\Seeder;

class VideoSeeder extends Seeder
{
    public function run(): void
    {
        $defaultUrl = 'https://youtube.com/shorts/HHtKChENSt8?si=Y7Ebf_cYvdRh5gJR';
        $screens = [
            'business_name',
            'top_selling_items',
            'location_business',
            'google_business_picker',
            'customer_searches',
            'ai_agent_replies',
        ];

        foreach ($screens as $screen) {
            Video::firstOrCreate(
                ['screen_name' => $screen],
                [
                    'video_url' => $defaultUrl,
                    'is_active' => true,
                ]
            );
        }
    }
}
