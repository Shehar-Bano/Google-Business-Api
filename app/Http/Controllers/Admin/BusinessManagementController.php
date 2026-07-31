<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Business;
use App\Models\BusinessSubcategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BusinessManagementController extends Controller
{
    /**
     * Display a listing of businesses.
     */
    public function index(Request $request)
    {
        $search = trim($request->string('search')->toString());
        $businessName = trim($request->string('business_name')->toString());
        $status = trim($request->string('status')->toString());
        $location = trim($request->string('location')->toString());
        $dateFrom = trim($request->string('date_from')->toString());
        $dateTo = trim($request->string('date_to')->toString());

        $perPage = in_array((int) $request->integer('per_page', 10), [10, 25, 50, 100], true)
            ? (int) $request->integer('per_page', 10) : 10;

        $sort = in_array(
            $request->string('sort', 'created_at')->toString(),
            ['name', 'location', 'status', 'created_at'],
            true
        ) ? $request->string('sort', 'created_at')->toString() : 'created_at';

        $direction = in_array($request->string('direction', 'desc')->toString(), ['asc', 'desc'], true)
            ? $request->string('direction', 'desc')->toString() : 'desc';

        $businesses = Business::query()
            ->with('topSellingItems')
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('location', 'like', "%{$search}%")
                      ->orWhere('phone_number', 'like', "%{$search}%")
                      ->orWhere('address', 'like', "%{$search}%")
                      ->orWhere('category', 'like', "%{$search}%")
                      ->orWhereHas('user', function($uq) use ($search) {
                          $uq->where('email', 'like', "%{$search}%")
                            ->orWhere('name', 'like', "%{$search}%");
                      });
                });
            })
            ->when($businessName !== '', function ($query) use ($businessName) {
                $query->where('name', 'like', "%{$businessName}%");
            })
            ->when($status !== '', function ($query) use ($status) {
                $query->where('status', $status);
            })
            ->when($location !== '', function ($query) use ($location) {
                $query->where('location', 'like', "%{$location}%");
            })
            ->when($dateFrom !== '', function ($query) use ($dateFrom) {
                $query->whereDate('created_at', '>=', $dateFrom);
            })
            ->when($dateTo !== '', function ($query) use ($dateTo) {
                $query->whereDate('created_at', '<=', $dateTo);
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
            'businesses', 'stats', 'search', 'businessName', 'status', 'location', 'dateFrom', 'dateTo', 'sort', 'direction', 'perPage'
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
            'phone_number' => 'nullable|string|max:50',
            'address' => 'nullable|string|max:255',
            'brand_logo' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg,webp|max:5120',
            'rating' => 'nullable|numeric|between:0,5',
            'reviews' => 'nullable|integer|min:0',
            'isVerified' => 'nullable|boolean',
            'category' => 'nullable|string|max:255',
            'top_selling_items' => 'required|string',
            'offering_ids' => 'nullable|array',
            'offering_ids.*' => 'exists:offerings,id',
        ]);

        // Convert comma-separated string to clean array
        $items = array_map('trim', explode(',', $request->input('top_selling_items')));
        $items = array_filter($items); // Remove empty values

        DB::beginTransaction();

        try {
            // Handle logo upload
            $logoPath = null;
            if ($request->hasFile('brand_logo')) {
                $path = $request->file('brand_logo')->store('businesses', 'public');
                $logoPath = 'storage/'.$path;
            }

            $business = Business::create([
                'user_id' => $request->user()?->id,
                'name' => $request->input('name'),
                'location' => $request->input('location'),
                'phone_number' => $request->input('phone_number'),
                'address' => $request->input('address'),
                'brand_logo' => $logoPath,
                'rating' => $request->input('rating'),
                'reviews' => $request->input('reviews'),
                'isVerified' => $request->has('isVerified'),
                'category' => $request->input('category'),
                'top_selling_items' => array_values($items),
            ]);

            // Sync offerings
            if ($request->has('offering_ids')) {
                $business->offerings()->sync($request->input('offering_ids'));
            }

            // Recalculate score after offerings are synced
            \App\Services\BusinessScoreCalculator::recalculate($business);

            DB::commit();

            return redirect()->route('admin.business-management.index')
                ->with('success', 'Business registered successfully.');

        } catch (\Exception $e) {
            DB::rollBack();

            return back()->withInput()->with('error', 'Error creating business: '.$e->getMessage());
        }
    }

    /**
     * Display the specified business details.
     */
    public function show(Business $business)
    {
        $business->load(['offerings.subcategory.category', 'topSellingItems', 'estimatedScores', 'googleScores', 'keywordIdeas']);

        return view('content.admin.business-management.show', compact('business'));
    }

    /**
     * Show the form for editing the specified business.
     */
    public function edit(Business $business)
    {
        return view('content.admin.business-management.edit', compact('business'));
    }

    /**
     * Update the specified business status in storage.
     */
    public function update(Request $request, Business $business)
    {
        $request->validate([
            'status' => 'required|in:approved,suspended',
        ]);

        $oldStatus = $business->status ?? 'approved';
        $newStatus = $request->input('status');

        $business->update([
            'status' => $newStatus,
        ]);

        // Log the action to admin audit logs
        \App\Models\AdminAuditLog::log(
            'business_status_update',
            'Business',
            (string) $business->id,
            "Updated business '{$business->name}' status from '{$oldStatus}' to '{$newStatus}'.",
            ['business_id' => $business->id, 'old_status' => $oldStatus, 'new_status' => $newStatus]
        );

        return redirect()->route('admin.business-management.index')
            ->with('success', 'Business status updated successfully.');
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
                ->with('error', 'Error deleting business: '.$e->getMessage());
        }
    }

    /**
     * Display preferences of the business.
     */
    public function showPreferences(Business $business)
    {
        $business->load('preferences.images');

        return view('content.admin.business-management.preferences', compact('business'));
    }

    /**
     * Display keyword ideas of the business.
     */
    public function showKeywordIdeas(Business $business)
    {
        $business->load('keywordIdeas');

        return view('content.admin.business-management.keyword-ideas', compact('business'));
    }
}
