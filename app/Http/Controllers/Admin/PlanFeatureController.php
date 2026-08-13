<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PlanFeature;
use App\Models\AdminAuditLog;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class PlanFeatureController extends Controller
{
    /**
     * Display a listing of plan features.
     */
    public function index(Request $request): View
    {
        $search = trim($request->string('search')->toString());
        $status = trim($request->string('status')->toString());
        $perPage = (int) $request->integer('per_page', 10);
        $perPage = in_array($perPage, [10, 15, 20, 25, 50, 100], true) ? $perPage : 10;

        $sort = in_array(
            $request->string('sort', 'id')->toString(),
            ['id', 'name', 'slug', 'status', 'created_at'],
            true
        ) ? $request->string('sort', 'id')->toString() : 'id';

        $direction = in_array($request->string('direction', 'desc')->toString(), ['asc', 'desc'], true)
            ? $request->string('direction', 'desc')->toString() : 'desc';

        $features = PlanFeature::query()
            ->withCount('subscriptionPlans')
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('slug', 'like', "%{$search}%")
                      ->orWhere('description', 'like', "%{$search}%");
                });
            })
            ->when($status !== '', function ($query) use ($status) {
                $query->where('status', $status);
            })
            ->orderBy($sort, $direction)
            ->paginate($perPage)
            ->withQueryString();

        return view('content.admin.plan-features.index', compact('features', 'search', 'perPage', 'sort', 'direction', 'status'));
    }

    /**
     * Show the form for creating a new plan feature.
     */
    public function create(): View
    {
        return view('content.admin.plan-features.create');
    }

    /**
     * Store a newly created plan feature in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $slug = $request->filled('slug')
            ? Str::slug($request->input('slug'))
            : Str::slug($request->input('name', ''));

        $request->merge(['slug' => $slug]);

        $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:plan_features,slug',
            'status' => 'required|in:active,inactive',
            'description' => 'nullable|string|max:1000',
        ]);

        $feature = PlanFeature::create([
            'name' => $request->input('name'),
            'slug' => $request->input('slug'),
            'status' => $request->input('status'),
            'description' => $request->input('description'),
        ]);

        AdminAuditLog::log(
            'plan_feature_create',
            'PlanFeature',
            (string) $feature->id,
            "Created plan feature '{$feature->name}'.",
            ['feature_id' => $feature->id, 'name' => $feature->name, 'slug' => $feature->slug]
        );

        return redirect()->route('admin.plan-features.index')
            ->with('success', 'Plan feature created successfully.');
    }

    /**
     * Show the form for editing the specified plan feature.
     */
    public function edit(PlanFeature $planFeature): View
    {
        return view('content.admin.plan-features.edit', compact('planFeature'));
    }

    /**
     * Update the specified plan feature in storage.
     */
    public function update(Request $request, PlanFeature $planFeature): RedirectResponse
    {
        $slug = $request->filled('slug')
            ? Str::slug($request->input('slug'))
            : Str::slug($request->input('name', $planFeature->name));

        $request->merge(['slug' => $slug]);

        $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:plan_features,slug,' . $planFeature->id,
            'status' => 'required|in:active,inactive',
            'description' => 'nullable|string|max:1000',
        ]);

        $planFeature->update([
            'name' => $request->input('name'),
            'slug' => $request->input('slug'),
            'status' => $request->input('status'),
            'description' => $request->input('description'),
        ]);

        AdminAuditLog::log(
            'plan_feature_update',
            'PlanFeature',
            (string) $planFeature->id,
            "Updated plan feature '{$planFeature->name}'.",
            ['feature_id' => $planFeature->id, 'name' => $planFeature->name]
        );

        return redirect()->route('admin.plan-features.index')
            ->with('success', 'Plan feature updated successfully.');
    }

    /**
     * Remove the specified plan feature from storage.
     */
    public function destroy(PlanFeature $planFeature): RedirectResponse
    {
        $featureName = $planFeature->name;
        $featureId = $planFeature->id;

        $planFeature->delete();

        AdminAuditLog::log(
            'plan_feature_delete',
            'PlanFeature',
            (string) $featureId,
            "Deleted plan feature '{$featureName}'.",
            ['feature_id' => $featureId, 'name' => $featureName]
        );

        return redirect()->route('admin.plan-features.index')
            ->with('success', 'Plan feature deleted successfully.');
    }
}
