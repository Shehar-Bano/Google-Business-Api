<?php

namespace Database\Seeders;

use App\Models\PlanFeature;
use App\Models\SubscriptionPlan;
use Illuminate\Database\Seeder;

class SubscriptionPlanAndFeatureSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Seed Plan Features
        $featuresData = [
            [
                'name' => 'AI Poster Generation',
                'slug' => 'ai-poster-generation',
                'status' => 'active',
                'description' => 'Generate customized branded marketing posters using AI prompt engineering.',
            ],
            [
                'name' => 'Social Media Auto-Posting',
                'slug' => 'social-media-auto-posting',
                'status' => 'active',
                'description' => 'Auto-publish approved posters directly to connected Facebook Pages and Instagram Accounts.',
            ],
            [
                'name' => 'Scheduled Post Publishing',
                'slug' => 'scheduled-post-publishing',
                'status' => 'active',
                'description' => 'Schedule marketing posts for future dates and times with automated background jobs.',
            ],
            [
                'name' => 'Google Business Profile Sync',
                'slug' => 'google-business-sync',
                'status' => 'active',
                'description' => 'Connect and manage Google Business Profile listings and location data.',
            ],
            [
                'name' => 'Google Keyword Ideas & SEO',
                'slug' => 'google-keyword-ideas',
                'status' => 'active',
                'description' => 'Access live Google Ads search volumes, competition ratings, and bid estimates.',
            ],
            [
                'name' => 'WhatsApp Review Requests',
                'slug' => 'whatsapp-review-requests',
                'status' => 'active',
                'description' => 'Send personalized WhatsApp review collection links directly to customers.',
            ],
            [
                'name' => 'Automated Review Reminders',
                'slug' => 'automated-review-reminders',
                'status' => 'active',
                'description' => 'Automatic follow-up reminders to maximize Google customer review conversion rates.',
            ],
            [
                'name' => 'Business Analytics & Scores',
                'slug' => 'business-analytics-scores',
                'status' => 'active',
                'description' => 'Track business performance, estimated scores, reviews, and offering growth insights.',
            ],
            [
                'name' => 'Video Shorts & Templates Library',
                'slug' => 'video-shorts-templates',
                'status' => 'active',
                'description' => 'Access to full catalog of video shorts, poster templates, and marketing materials.',
            ],
            [
                'name' => '24/7 Priority Support',
                'slug' => 'priority-support',
                'status' => 'active',
                'description' => 'Dedicated technical assistance and priority customer onboarding support.',
            ],
        ];

        $featureModels = [];
        foreach ($featuresData as $data) {
            $featureModels[$data['slug']] = PlanFeature::updateOrCreate(
                ['slug' => $data['slug']],
                $data
            );
        }

        // 2. Seed Subscription Plans
        $plansData = [
            [
                'title' => 'Basic Plan',
                'price' => 999.00,
                'billing_period' => 'monthly',
                'status' => 'active',
                'is_popular' => false,
                'features' => [
                    'ai-poster-generation',
                    'google-business-sync',
                    'whatsapp-review-requests',
                    'video-shorts-templates',
                ],
            ],
            [
                'title' => 'Standard Plan',
                'price' => 2000.00,
                'billing_period' => 'monthly',
                'status' => 'active',
                'is_popular' => true,
                'features' => [
                    'ai-poster-generation',
                    'social-media-auto-posting',
                    'scheduled-post-publishing',
                    'google-business-sync',
                    'google-keyword-ideas',
                    'whatsapp-review-requests',
                    'automated-review-reminders',
                    'business-analytics-scores',
                    'video-shorts-templates',
                ],
            ],
            [
                'title' => 'Free Tier',
                'price' => 0.00,
                'billing_period' => 'yearly',
                'status' => 'active',
                'is_popular' => false,
                'features' => [
                    'ai-poster-generation',
                    'social-media-auto-posting',
                    'scheduled-post-publishing',
                    'google-business-sync',
                    'google-keyword-ideas',
                    'whatsapp-review-requests',
                    'automated-review-reminders',
                    'business-analytics-scores',
                    'video-shorts-templates',
                    'priority-support',
                ],
            ],
        ];

        foreach ($plansData as $pData) {
            $featureSlugs = $pData['features'];
            unset($pData['features']);

            $plan = SubscriptionPlan::updateOrCreate(
                ['title' => $pData['title']],
                $pData
            );

            // Sync features
            $featureIds = [];
            foreach ($featureSlugs as $slug) {
                if (isset($featureModels[$slug])) {
                    $featureIds[] = $featureModels[$slug]->id;
                }
            }

            $plan->features()->sync($featureIds);
        }
    }
}
