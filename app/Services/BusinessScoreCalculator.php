<?php

namespace App\Services;

use App\Models\Business;
use App\Models\BusinessEstimatedScore;
use Illuminate\Support\Facades\DB;

class BusinessScoreCalculator
{
    /**
     * Recalculate estimated scores for the given business.
     */
    public static function recalculate(Business $business): void
    {
        $business->loadMissing('preferences');

        // Rule 1: google_reviews
        $reviews = (int) ($business->reviews ?? 0);
        $googleReviewsPoints = 0;
        if ($reviews >= 1 && $reviews <= 50) {
            $googleReviewsPoints = 5;
        } elseif ($reviews >= 51 && $reviews <= 100) {
            $googleReviewsPoints = 7;
        } elseif ($reviews >= 101 && $reviews <= 250) {
            $googleReviewsPoints = 9;
        } elseif ($reviews >= 251 && $reviews <= 500) {
            $googleReviewsPoints = 11;
        } elseif ($reviews > 500) {
            $googleReviewsPoints = 15;
        }

        // Rule 2: active_days
        $activeDays = $business->created_at ? now()->diffInDays($business->created_at) : 0;
        $activeDaysPoints = ($activeDays >= 1 && $activeDays <= 30) ? 1 : 0;

        // Rule 3: reviews_replied
        $reviewsReplied = (int) ($business->reviews_replied ?? $business->preferences->reviews_replied ?? 0);
        $reviewsRepliedPoints = ($reviewsReplied > 50) ? 20 : 0;

        // Rule 4: google_ratings
        $rating = (float) ($business->rating ?? 0);
        $googleRatingsPoints = ($rating > 4.2) ? 5 : 0;

        // Rule 5: business_description
        $description = $business->preferences->business_description ?? '';
        $businessDescriptionPoints = (mb_strlen($description) > 300) ? 3 : 0;

        // Rule 6: primary_category
        $primaryCategoryPoints = (!empty($business->category)) ? 3 : 0;

        // Rule 7: business_category
        $categoriesCount = DB::table('business_offerings')
            ->join('offerings', 'business_offerings.offering_id', '=', 'offerings.id')
            ->join('business_subcategories', 'offerings.subcategory_id', '=', 'business_subcategories.id')
            ->where('business_offerings.business_id', $business->id)
            ->distinct('business_subcategories.category_id')
            ->count('business_subcategories.category_id');
        $businessCategoryPoints = ($categoriesCount >= 5) ? 4 : 0;

        // Rule 8: contact_phone_number
        $contactPhoneNumberPoints = (!empty($business->phone_number)) ? 3 : 0;

        // Rule 9: business_photos
        $interiorPhotos = is_array($business->preferences->interior_photos ?? null) ? $business->preferences->interior_photos : [];
        $teamPhotos = is_array($business->preferences->team_photos ?? null) ? $business->preferences->team_photos : [];
        $photosCount = count(array_filter($interiorPhotos)) + count(array_filter($teamPhotos));
        $businessPhotosPoints = ($photosCount >= 10) ? 3 : 0;

        // Rule 10: post_upload_frequency
        $postUploadFrequency = (int) ($business->post_upload_frequency ?? $business->preferences->post_upload_frequency ?? 0);
        $postUploadFrequencyPoints = ($postUploadFrequency >= 5) ? 5 : 0;

        // Rule 11: country
        $country = $business->country ?? $business->preferences->region ?? null; // using region/location as fallback
        $countryPoints = (!empty($country)) ? 1 : 0;

        // Rule 12: state
        $state = $business->state ?? $business->preferences->state ?? null;
        $statePoints = (!empty($state)) ? 1 : 0;

        // Rule 13: city
        $city = $business->city ?? $business->preferences->city ?? null;
        $cityPoints = (!empty($city)) ? 1 : 0;

        // Rule 14: pincode
        $pincode = $business->pincode ?? $business->preferences->pincode ?? null;
        $pincodePoints = (!empty($pincode)) ? 1 : 0;

        $rawScores = DB::table('google_scores')->where('business_id', $business->id)->pluck('points', 'name')->toArray();

        $scores = [
            'google_reviews' => $rawScores['google_reviews'] ?? $googleReviewsPoints,
            'active_days' => $rawScores['active_days'] ?? $activeDaysPoints,
            'reviews_replied' => $rawScores['reviews_replied'] ?? $reviewsRepliedPoints,
            'google_ratings' => $rawScores['google_ratings'] ?? $googleRatingsPoints,
            'business_description' => $rawScores['business_description'] ?? $businessDescriptionPoints,
            'primary_category' => $rawScores['primary_category'] ?? $primaryCategoryPoints,
            'business_category' => $rawScores['business_category'] ?? $businessCategoryPoints,
            'contact_phone_number' => $rawScores['contact_phone_number'] ?? $contactPhoneNumberPoints,
            'business_photos' => $rawScores['business_photos'] ?? $businessPhotosPoints,
            'post_upload_frequency' => $rawScores['post_upload_frequency'] ?? $postUploadFrequencyPoints,
            'country' => $rawScores['country'] ?? $countryPoints,
            'state' => $rawScores['state'] ?? $statePoints,
            'city' => $rawScores['city'] ?? $cityPoints,
            'pincode' => $rawScores['pincode'] ?? $pincodePoints,
        ];

        foreach ($scores as $name => $points) {
            BusinessEstimatedScore::updateOrCreate(
                [
                    'business_id' => $business->id,
                    'name' => $name,
                ],
                [
                    'points' => $points,
                ]
            );
        }
    }
}
