<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Offering;
use App\Models\BusinessSubcategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class OfferingController extends Controller
{
    public function search(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'q' => 'required|string|min:1',
            'category_id' => 'nullable|integer|exists:business_categories,id',
            'subcategory_id' => 'nullable|integer|exists:business_subcategories,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Search validation failed.',
                'errors' => $validator->errors(),
            ], 420);
        }

        $query = $request->input('q');
        $categoryId = $request->input('category_id');
        $subcategoryId = $request->input('subcategory_id');

        $offeringsQuery = Offering::with(['subcategory.category'])
            ->where('status', 'active')
            ->where(function ($q) use ($query) {
                $q->where('name', 'like', '%' . $query . '%')
                  ->orWhere('keywords', 'like', '%' . $query . '%');
            });

        // Restrict to category if specified
        if ($categoryId) {
            $offeringsQuery->whereHas('subcategory', function ($q) use ($categoryId) {
                $q->where('category_id', $categoryId);
            });
        }

        // Restrict to subcategory if specified
        if ($subcategoryId) {
            $offeringsQuery->where('subcategory_id', $subcategoryId);
        }

        $offerings = $offeringsQuery->limit(20)->get();

        // Transform collection to match the required response structure
        $results = $offerings->map(function ($offering) {
            return [
                'id' => $offering->id,
                'name' => $offering->name,
                'type' => $offering->type,
                'category' => $offering->subcategory->category->name ?? null,
                'subcategory' => $offering->subcategory->name ?? null,
            ];
        });

        return response()->json([
            'success' => true,
            'results' => $results,
        ]);
    }

    /**
     * Save selected offerings (and dynamically create custom ones) for a business.
     */
    public function saveBusinessOfferings(Request $request, $businessId)
    {
        $validator = Validator::make($request->all(), [
            'offering_ids' => 'nullable|array',
            'offering_ids.*' => 'exists:offerings,id',
            'custom_offerings' => 'nullable|array',
            'custom_offerings.*.name' => 'required|string|max:255',
            'custom_offerings.*.type' => 'required|in:product,service',
            'custom_offerings.*.subcategory_id' => 'required|exists:business_subcategories,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed.',
                'errors' => $validator->errors(),
            ], 422);
        }

        $business = \App\Models\Business::find($businessId);
        if (! $business) {
            return response()->json([
                'success' => false,
                'message' => 'Business not found.',
            ], 404);
        }

        if ($business->status === 'suspended') {
            return response()->json([
                'success' => false,
                'message' => 'Your business has been suspended. Please contact support.',
                'error_code' => 'BUSINESS_SUSPENDED',
                'status' => 'suspended',
            ], 403);
        }

        $offeringIds = $request->input('offering_ids', []);
        $customOfferings = $request->input('custom_offerings', []);

        DB::beginTransaction();

        try {
            // Process custom offerings on the fly
            foreach ($customOfferings as $custom) {
                // To avoid duplicate custom offerings within the same subcategory, match or create
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

            // Clean list of IDs
            $offeringIds = array_unique($offeringIds);

            // Sync with business_offerings pivot table
            // Delete old records and bulk insert new ones
            DB::table('business_offerings')
                ->where('business_id', $businessId)
                ->delete();

            $pivotData = [];
            foreach ($offeringIds as $id) {
                $pivotData[] = [
                    'business_id' => $businessId,
                    'offering_id' => $id,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }

            if (! empty($pivotData)) {
                DB::table('business_offerings')->insertOrIgnore($pivotData);
            }

            // Recalculate score after updating offerings
            $business = \App\Models\Business::find($businessId);
            if ($business) {
                \App\Services\BusinessScoreCalculator::recalculate($business);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Business offerings saved successfully.',
                'offering_count' => count($offeringIds),
            ]);

        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Error saving business offerings: '.$e->getMessage(),
            ], 500);
        }
    }
}
