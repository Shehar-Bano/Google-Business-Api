<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Business;
use App\Models\Offering;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class BusinessController extends Controller
{
    /**
     * Display a listing of businesses with their associated offerings.
     */
    public function index()
    {
        $businesses = Business::with(['offerings.subcategory.category'])->get();

        return response()->json([
            'success' => true,
            'data' => $businesses,
        ]);
    }

    /**
     * Store a newly created business and sync offerings.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'location' => 'required|string|max:255',
            'phone_number' => 'nullable|string|max:50',
            'address' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255',
            'brand_logo' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg,webp|max:5120',
            'rating' => 'nullable|numeric|between:0,5',
            'reviews' => 'nullable|integer|min:0',
            'isVerified' => 'nullable|boolean',
            'category' => 'nullable|string|max:255',
            'google_place_id' => 'nullable|string|max:255',
            'top_selling_items' => 'required|array',
            'top_selling_items.*.item_name' => 'required|string|max:255',
            'top_selling_items.*.description' => 'nullable|string',
            'top_selling_items.*.price' => 'nullable|numeric|min:0',
            'top_selling_items.*.media' => 'nullable',
            'offering_ids' => 'nullable|array',
            'offering_ids.*' => 'exists:offerings,id',
            'custom_offerings' => 'nullable|array',
            'custom_offerings.*.name' => 'required|string|max:255',
            'custom_offerings.*.type' => 'required|in:product,service',
            'custom_offerings.*.subcategory_id' => 'required|exists:business_subcategories,id',
            'google_scores' => 'nullable|array',
            'google_scores.*.name' => 'required|string|in:google_reviews,active_days,reviews_replied,google_ratings,business_description,primary_category,business_category,contact_phone_number,business_photos,post_upload_frequency,country,state,city,pincode',
            'google_scores.*.points' => 'required|integer|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed.',
                'errors' => $validator->errors(),
            ], 422);
        }

        // Check if user has any suspended business
        $userId = $request->user()?->id;
        if ($userId) {
            $hasSuspendedBusiness = Business::where('user_id', $userId)
                ->where('status', 'suspended')
                ->exists();

            if ($hasSuspendedBusiness) {
                return response()->json([
                    'success' => false,
                    'message' => 'Your business is suspended. You cannot create a new business.',
                ], 403);
            }
        }

        DB::beginTransaction();

        try {
            // Handle logo upload
            $logoPath = null;
            if ($request->hasFile('brand_logo')) {
                $path = $request->file('brand_logo')->store('businesses', 'public');
                $logoPath = 'storage/'.$path;
            }

            // Create Business
            $business = Business::create([
                'user_id' => $userId,
                'name' => $request->input('name'),
                'location' => $request->input('location'),
                'phone_number' => $request->input('phone_number'),
                'address' => $request->input('address'),
                'email' => $request->input('email'),
                'brand_logo' => $logoPath,
                'rating' => $request->input('rating'),
                'reviews' => $request->input('reviews'),
                'isVerified' => $request->boolean('isVerified'),
                'category' => $request->input('category'),
                'google_place_id' => $request->input('google_place_id'),
            ]);

            // Save raw google scores from request
            if ($request->has('google_scores')) {
                foreach ($request->input('google_scores') as $scoreData) {
                    $business->googleScores()->create([
                        'name' => $scoreData['name'],
                        'points' => $scoreData['points'],
                    ]);
                }
            }

            // Save top selling items
            if ($request->has('top_selling_items')) {
                foreach ($request->input('top_selling_items') as $index => $itemData) {
                    $mediaPath = null;
                    if ($request->hasFile("top_selling_items.{$index}.media")) {
                        $path = $request->file("top_selling_items.{$index}.media")->store('items', 'public');
                        $mediaPath = 'storage/'.$path;
                    } elseif (isset($itemData['media']) && is_string($itemData['media'])) {
                        $mediaPath = $itemData['media'];
                    }

                    $business->topSellingItems()->create([
                        'item_name' => $itemData['item_name'],
                        'description' => $itemData['description'] ?? null,
                        'price' => $itemData['price'] ?? null,
                        'media' => $mediaPath,
                    ]);
                }
            }

            // Sync offerings
            $this->syncOfferings($business, $request->input('offering_ids', []), $request->input('custom_offerings', []));

            // Recalculate score after offerings are linked
            \App\Services\BusinessScoreCalculator::recalculate($business);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Business registered successfully.',
                'data' => $business->load(['offerings.subcategory.category', 'user', 'googleScores', 'topSellingItems']),
            ], 200);

        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Error registering business: '.$e->getMessage(),
            ], 500);
        }
    }

    public function show(Request $request, $userId)
    {
        // dd($userId);
        $business = Business::with(['offerings.subcategory.category', 'preferences', 'user', 'topSellingItems'])
            ->where('user_id', $userId)
            ->first();

        if (! $business) {
            return response()->json([
                'success' => false,
                'message' => 'Business not found.',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $business,
        ]);
    }

    /**
     * Update the specified business and sync offerings.
     */
    public function update(Request $request, $id)
    {
        $business = Business::find($id);

        if (! $business) {
            return response()->json([
                'success' => false,
                'message' => 'Business not found.',
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|required|string|max:255',
            'location' => 'sometimes|required|string|max:255',
            'phone_number' => 'nullable|string|max:50',
            'address' => 'nullable|string|max:255',
            'brand_logo' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg,webp|max:5120',
            'rating' => 'nullable|numeric|between:0,5',
            'reviews' => 'nullable|integer|min:0',
            'isVerified' => 'nullable|boolean',
            'category' => 'nullable|string|max:255',
            'top_selling_items' => 'nullable|array',
            'top_selling_items.*.id' => 'nullable|integer|exists:top_selling_items,id',
            'top_selling_items.*.item_name' => 'required|string|max:255',
            'top_selling_items.*.description' => 'nullable|string',
            'top_selling_items.*.price' => 'nullable|numeric|min:0',
            'top_selling_items.*.media' => 'nullable',
            'offering_ids' => 'nullable|array',
            'offering_ids.*' => 'exists:offerings,id',
            'custom_offerings' => 'nullable|array',
            'custom_offerings.*.name' => 'required|string|max:255',
            'custom_offerings.*.type' => 'required|in:product,service',
            'custom_offerings.*.subcategory_id' => 'required|exists:business_subcategories,id',
            'google_scores' => 'nullable|array',
            'google_scores.*.name' => 'required|string|in:google_reviews,active_days,reviews_replied,google_ratings,business_description,primary_category,business_category,contact_phone_number,business_photos,post_upload_frequency,country,state,city,pincode',
            'google_scores.*.points' => 'required|integer|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed.',
                'errors' => $validator->errors(),
            ], 422);
        }

        DB::beginTransaction();

        try {
            // Update fields
            $updateData = $request->only(['name', 'location', 'phone_number', 'address', 'rating', 'reviews', 'category']);

            if ($request->has('isVerified')) {
                $updateData['isVerified'] = $request->boolean('isVerified');
            }

            // Handle logo update
            if ($request->hasFile('brand_logo')) {
                if ($business->brand_logo) {
                    $oldPath = str_replace('storage/', '', $business->brand_logo);
                    if (\Illuminate\Support\Facades\Storage::disk('public')->exists($oldPath)) {
                        \Illuminate\Support\Facades\Storage::disk('public')->delete($oldPath);
                    }
                }
                $path = $request->file('brand_logo')->store('businesses', 'public');
                $updateData['brand_logo'] = 'storage/'.$path;
            }

            $business->update($updateData);

            // Update raw google scores from request
            if ($request->has('google_scores')) {
                $business->googleScores()->delete();
                foreach ($request->input('google_scores') as $scoreData) {
                    $business->googleScores()->create([
                        'name' => $scoreData['name'],
                        'points' => $scoreData['points'],
                    ]);
                }
            }

            // Update top selling items
            if ($request->has('top_selling_items')) {
                $incomingIds = [];

                foreach ($request->input('top_selling_items') as $index => $itemData) {
                    $mediaPath = null;
                    if ($request->hasFile("top_selling_items.{$index}.media")) {
                        $path = $request->file("top_selling_items.{$index}.media")->store('items', 'public');
                        $mediaPath = 'storage/'.$path;
                    } elseif (isset($itemData['media']) && is_string($itemData['media'])) {
                        $mediaPath = $itemData['media'];
                    }

                    $itemId = $itemData['id'] ?? null;
                    if ($itemId) {
                        $existingItem = $business->topSellingItems()->find($itemId);
                        if ($existingItem) {
                            $updateFields = [
                                'item_name' => $itemData['item_name'],
                                'description' => $itemData['description'] ?? null,
                                'price' => $itemData['price'] ?? null,
                            ];
                            if ($mediaPath !== null) {
                                // Delete old media if a new file is uploaded
                                if ($existingItem->media) {
                                    $oldPath = str_replace('storage/', '', $existingItem->media);
                                    if (\Illuminate\Support\Facades\Storage::disk('public')->exists($oldPath)) {
                                        \Illuminate\Support\Facades\Storage::disk('public')->delete($oldPath);
                                    }
                                }
                                $updateFields['media'] = $mediaPath;
                            }
                            $existingItem->update($updateFields);
                            $incomingIds[] = $existingItem->id;
                        }
                    } else {
                        // Create a new one
                        $newItem = $business->topSellingItems()->create([
                            'item_name' => $itemData['item_name'],
                            'description' => $itemData['description'] ?? null,
                            'price' => $itemData['price'] ?? null,
                            'media' => $mediaPath,
                        ]);
                        $incomingIds[] = $newItem->id;
                    }
                }

                // Delete items that were not present in the update request payload
                $business->topSellingItems()->whereNotIn('id', $incomingIds)->delete();
            }

            // If offerings or custom offerings are provided, sync them
            if ($request->has('offering_ids') || $request->has('custom_offerings')) {
                $this->syncOfferings(
                    $business,
                    $request->input('offering_ids', []),
                    $request->input('custom_offerings', [])
                );
            }

            // Recalculate score after update and sync
            \App\Services\BusinessScoreCalculator::recalculate($business);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Business updated successfully.',
                'data' => $business->load(['offerings.subcategory.category', 'user', 'googleScores', 'topSellingItems']),
            ]);

        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Error updating business: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * Remove the specified business.
     */
    public function destroy($id)
    {
        $business = Business::find($id);

        if (! $business) {
            return response()->json([
                'success' => false,
                'message' => 'Business not found.',
            ], 404);
        }

        DB::beginTransaction();

        try {
            // Detach offerings first to clean up pivot records
            $business->offerings()->detach();
            $business->delete();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Business deleted successfully.',
            ]);

        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Error deleting business: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * Helper method to parse custom offerings and sync offerings with business pivot table
     */
    protected function syncOfferings(Business $business, array $offeringIds, array $customOfferings)
    {
        // Process custom offerings on the fly
        foreach ($customOfferings as $custom) {
            $offering = Offering::firstOrCreate(
                [
                    'subcategory_id' => $custom['subcategory_id'],
                    'name' => trim($custom['name']),
                    'type' => $custom['type'],
                ],
                [
                    'slug' => Str::slug($custom['name']),
                    'keywords' => 'custom',
                    'status' => 'active',
                ]
            );

            $offeringIds[] = $offering->id;
        }

        // Clean & Sync via Eloquent belongsToMany relationship
        $offeringIds = array_unique($offeringIds);
        $business->offerings()->sync($offeringIds);
    }

    /**
     * Get only name and points from estimated scores for a business.
     */
    public function getEstimatedScores($businessId)
    {
        $business = Business::find($businessId);
        if (! $business) {
            return response()->json([
                'success' => false,
                'message' => 'Business not found.',
            ], 404);
        }

        $scores = $business->estimatedScores()->select('name', 'points')->get();

        return response()->json([
            'success' => true,
            'data' => $scores,
        ], 200);
    }
}
