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
            'top_selling_items' => 'required|array',
            'top_selling_items.*' => 'required|string|max:255',
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

        DB::beginTransaction();

        try {
            // Create Business (top_selling_items will be automatically cast to json due to model casting)
            $business = Business::create([
                'name' => $request->input('name'),
                'location' => $request->input('location'),
                'top_selling_items' => $request->input('top_selling_items'),
            ]);

            // Sync offerings
            $this->syncOfferings($business, $request->input('offering_ids', []), $request->input('custom_offerings', []));

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Business registered successfully.',
                'data' => $business->load('offerings.subcategory.category'),
            ], 211); // Standard created code or 201. Let's use 201 for standard created or 200. Let's return 201.

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error registering business: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Display the specified business with offerings.
     */
    public function show($id)
    {
        $business = Business::with(['offerings.subcategory.category'])->find($id);

        if (!$business) {
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

        if (!$business) {
            return response()->json([
                'success' => false,
                'message' => 'Business not found.',
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|required|string|max:255',
            'location' => 'sometimes|required|string|max:255',
            'top_selling_items' => 'sometimes|required|array',
            'top_selling_items.*' => 'required|string|max:255',
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

        DB::beginTransaction();

        try {
            // Update fields
            $business->update($request->only(['name', 'location', 'top_selling_items']));

            // If offerings or custom offerings are provided, sync them
            if ($request->has('offering_ids') || $request->has('custom_offerings')) {
                $this->syncOfferings(
                    $business,
                    $request->input('offering_ids', []),
                    $request->input('custom_offerings', [])
                );
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Business updated successfully.',
                'data' => $business->load('offerings.subcategory.category'),
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error updating business: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Remove the specified business.
     */
    public function destroy($id)
    {
        $business = Business::find($id);

        if (!$business) {
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
                'message' => 'Error deleting business: ' . $e->getMessage(),
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
                    'type' => $custom['type']
                ],
                [
                    'slug' => Str::slug($custom['name']),
                    'keywords' => 'custom',
                    'status' => 'active'
                ]
            );

            $offeringIds[] = $offering->id;
        }

        // Clean & Sync via Eloquent belongsToMany relationship
        $offeringIds = array_unique($offeringIds);
        $business->offerings()->sync($offeringIds);
    }
}
