<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Business;
use App\Models\Preference;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class PreferenceController extends Controller
{
    /**
     * Get preferences for a business.
     * GET /api/businesses/{businessId}/preferences
     */
    public function show($businessId): JsonResponse
    {
        $business = Business::find($businessId);
        if (! $business) {
            return response()->json([
                'success' => false,
                'message' => 'Business not found.',
            ], 404);
        }

        $preferences = Preference::with(['business', 'images'])->where('business_id', $businessId)->first();

        if (! $preferences) {
            $preferences = new Preference;
            $preferences->business_id = $business->id;
            $preferences->setRelation('business', $business);
            $preferences->setRelation('images', collect());
        }

        return response()->json([
            'success' => true,
            'data' => $preferences,
        ], 200);
    }

    /**
     * Store or Update preferences for a business.
     * POST /api/businesses/{businessId}/preferences
     */
    public function storeOrUpdate(Request $request, $businessId)
    {

        $business = Business::find($businessId);
        if (! $business) {
            return response()->json([
                'success' => false,
                'message' => 'Business not found.',
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'business_tagline' => 'nullable|string|max:255',
            'business_description' => 'nullable|string',
            'different_than_competition' => 'nullable|string',
            'why_visit_us' => 'nullable|string',
            'low_standards_of_industry' => 'nullable|string',
            'solutions_for_low_standards' => 'nullable|string',
            'malpractices_in_industry' => 'nullable|string',
            'solutions_for_malpractices' => 'nullable|string',
            'common_mistakes_by_customers' => 'nullable|string',
            'guidelines_to_customer' => 'nullable|string',
            'nearest_landmark' => 'nullable|string|max:255',
            'target_gender' => 'nullable|string|max:100',
            'target_age_group' => 'nullable|string|max:100',
            'region' => 'nullable|string|max:255',
            'model_ethnicity' => 'nullable|string|max:255',
            'audience' => 'nullable|string|max:255',
            'cta' => 'nullable|string|max:255',
            'stop_creative_auto_approval' => 'nullable|boolean',
            'images' => 'nullable|array',
            'images.*.type' => 'required|string|in:interior_photos,team_photos',
            'images.*.label' => 'nullable|string|max:255',
            'images.*.image' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed.',
                'errors' => $validator->errors(),
            ], 422);
        }

        $data = $request->only([
            'business_tagline',
            'business_description',
            'different_than_competition',
            'why_visit_us',
            'low_standards_of_industry',
            'solutions_for_low_standards',
            'malpractices_in_industry',
            'solutions_for_malpractices',
            'common_mistakes_by_customers',
            'guidelines_to_customer',
            'nearest_landmark',
            'target_gender',
            'target_age_group',
            'region',
            'model_ethnicity',
            'audience',
            'cta',
        ]);

        if ($request->has('stop_creative_auto_approval')) {
            $data['stop_creative_auto_approval'] = $request->boolean('stop_creative_auto_approval');
        }

        // Create or Update using the relationship
        $preferences = $business->preferences()->updateOrCreate(
            ['business_id' => $business->id],
            $data
        );

        // Store preference images
        if ($request->has('images')) {
            // Delete old images associated with this preference
            $preferences->images()->delete();

            foreach ($request->input('images') as $index => $imageData) {
                $imagePath = null;
                if ($request->hasFile("images.{$index}.image")) {
                    $path = $request->file("images.{$index}.image")->store('preferences', 'public');
                    $imagePath = 'storage/'.$path;
                } elseif (is_string($imageData['image'] ?? null)) {
                    $imagePath = $imageData['image'];
                }

                if ($imagePath) {
                    $preferences->images()->create([
                        'type' => $imageData['type'],
                        'label' => $imageData['label'] ?? null,
                        'image' => $imagePath,
                    ]);
                }
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Preferences saved successfully.',
            'data' => $preferences->load('images'),
        ], 200);
    }

    /**
     * Delete preferences for a business.
     * DELETE /api/businesses/{businessId}/preferences
     */
    public function destroy($businessId): JsonResponse
    {
        $business = Business::find($businessId);
        if (! $business) {
            return response()->json([
                'success' => false,
                'message' => 'Business not found.',
            ], 404);
        }

        if ($business->preferences) {
            $business->preferences->delete();
        }

        return response()->json([
            'success' => true,
            'message' => 'Preferences deleted successfully.',
        ], 200);
    }
}
