<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Business;
use App\Models\BusinessSubcategory;
use App\Models\Offering;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class BusinessManagementController extends Controller
{
    /**
     * Display a listing of businesses.
     */
    public function index(Request $request)
    {
        $search = trim($request->string('search')->toString());
        $perPage = in_array((int) $request->integer('per_page', 10), [10, 25, 50, 100], true)
            ? (int) $request->integer('per_page', 10) : 10;

        $sort = in_array(
            $request->string('sort', 'created_at')->toString(),
            ['name', 'location', 'created_at'],
            true
        ) ? $request->string('sort', 'created_at')->toString() : 'created_at';

        $direction = in_array($request->string('direction', 'desc')->toString(), ['asc', 'desc'], true)
            ? $request->string('direction', 'desc')->toString() : 'desc';

        $businesses = Business::query()
            ->when($search !== '', function ($query) use ($search) {
                $query->where('name', 'like', "%{$search}%")
                      ->orWhere('location', 'like', "%{$search}%");
            })
            ->orderBy($sort, $direction)
            ->paginate($perPage)
            ->withQueryString();

        $stats = [
            'total' => Business::count(),
            'with_offerings' => Business::has('offerings')->count(),
            'locations_count' => Business::distinct('location')->count(),
        ];

        return view('content.admin.business-management.index', compact(
            'businesses', 'stats', 'search', 'sort', 'direction', 'perPage'
        ));
    }

    /**
     * Show the form for creating a new business.
     */
    public function create()
    {
        $subcategories = BusinessSubcategory::with('offerings')->get();

        return view('content.admin.business-management.create', compact('subcategories'));
    }

    /**
     * Store a newly created business in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'location' => 'required|string|max:255',
            'top_selling_items' => 'required|string',
            'offering_ids' => 'nullable|array',
            'offering_ids.*' => 'exists:offerings,id',
        ]);

        // Convert comma-separated string to clean array
        $items = array_map('trim', explode(',', $request->input('top_selling_items')));
        $items = array_filter($items); // Remove empty values

        DB::beginTransaction();

        try {
            $business = Business::create([
                'user_id' => $request->user()?->id,
                'name' => $request->input('name'),
                'location' => $request->input('location'),
                'top_selling_items' => array_values($items),
            ]);

            // Sync offerings
            if ($request->has('offering_ids')) {
                $business->offerings()->sync($request->input('offering_ids'));
            }

            DB::commit();

            return redirect()->route('admin.business-management.index')
                ->with('success', 'Business registered successfully.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Error creating business: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified business details.
     */
    public function show(Business $business)
    {
        $business->load(['offerings.subcategory.category']);

        return view('content.admin.business-management.show', compact('business'));
    }

    /**
     * Show the form for editing the specified business.
     */
    public function edit(Business $business)
    {
        $business->load('offerings');
        $subcategories = BusinessSubcategory::with('offerings')->get();
        $selectedOfferingIds = $business->offerings->pluck('id')->all();

        // Convert top selling items array back to comma-separated string for editing
        $topSellingString = is_array($business->top_selling_items) 
            ? implode(', ', $business->top_selling_items) 
            : '';

        return view('content.admin.business-management.edit', compact(
            'business', 'subcategories', 'selectedOfferingIds', 'topSellingString'
        ));
    }

    /**
     * Update the specified business in storage.
     */
    public function update(Request $request, Business $business)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'location' => 'required|string|max:255',
            'top_selling_items' => 'required|string',
            'offering_ids' => 'nullable|array',
            'offering_ids.*' => 'exists:offerings,id',
        ]);

        $items = array_map('trim', explode(',', $request->input('top_selling_items')));
        $items = array_filter($items);

        DB::beginTransaction();

        try {
            $business->update([
                'name' => $request->input('name'),
                'location' => $request->input('location'),
                'top_selling_items' => array_values($items),
            ]);

            // Sync offerings
            $offeringIds = $request->input('offering_ids', []);
            $business->offerings()->sync($offeringIds);

            DB::commit();

            return redirect()->route('admin.business-management.index')
                ->with('success', 'Business updated successfully.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Error updating business: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified business from storage.
     */
    public function destroy(Business $business)
    {
        DB::beginTransaction();

        try {
            $business->offerings()->detach();
            $business->delete();

            DB::commit();

            return redirect()->route('admin.business-management.index')
                ->with('success', 'Business deleted successfully.');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->route('admin.business-management.index')
                ->with('error', 'Error deleting business: ' . $e->getMessage());
        }
    }
}
