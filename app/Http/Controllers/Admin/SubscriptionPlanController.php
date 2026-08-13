<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PlanFeature;
use App\Models\SubscriptionPlan;
use App\Models\AdminAuditLog;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SubscriptionPlanController extends Controller
{
    /**
     * Display a listing of the subscription plans.
     */
    public function index(Request $request): View
    {
        $search = trim($request->string('search')->toString());
        $status = trim($request->string('status')->toString());
        $perPage = (int) $request->integer('per_page', 10);
        $perPage = in_array($perPage, [10, 15, 20, 25, 50, 100], true) ? $perPage : 10;

        $sort = in_array(
            $request->string('sort', 'id')->toString(),
            ['id', 'title', 'price', 'billing_period', 'status', 'is_popular', 'created_at'],
            true
        ) ? $request->string('sort', 'id')->toString() : 'id';

        $direction = in_array($request->string('direction', 'desc')->toString(), ['asc', 'desc'], true)
            ? $request->string('direction', 'desc')->toString() : 'desc';

        $plans = SubscriptionPlan::query()
            ->with(['features'])
            ->when($search !== '', function ($query) use ($search) {
                $query->where('title', 'like', "%{$search}%");
            })
            ->when($status !== '', function ($query) use ($status) {
                $query->where('status', $status);
            })
            ->orderBy($sort, $direction)
            ->paginate($perPage)
            ->withQueryString();

        return view('content.admin.subscription-plans.index', compact('plans', 'search', 'perPage', 'sort', 'direction', 'status'));
    }

    /**
     * Show the form for creating a new plan.
     */
    public function create(): View
    {
        $features = PlanFeature::where('status', 'active')->orderBy('name')->get();
        return view('content.admin.subscription-plans.create', compact('features'));
    }

    /**
     * Store a newly created plan in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'status' => 'required|in:active,inactive',
            'is_popular' => 'nullable|boolean',
            'feature_ids' => 'nullable|array',
            'feature_ids.*' => 'exists:plan_features,id',
            'price' => 'required|numeric|min:0',
            'billing_period' => 'required|string|max:100',
        ]);

        $plan = SubscriptionPlan::create([
            'title' => $request->input('title'),
            'status' => $request->input('status'),
            'is_popular' => $request->boolean('is_popular'),
            'price' => $request->input('price'),
            'billing_period' => $request->input('billing_period'),
        ]);

        if ($request->has('feature_ids')) {
            $plan->features()->sync($request->input('feature_ids', []));
        }

        AdminAuditLog::log(
            'subscription_plan_create',
            'SubscriptionPlan',
            (string) $plan->id,
            "Created subscription plan '{$plan->title}' with price Rs. {$plan->price}.",
            ['plan_id' => $plan->id, 'title' => $plan->title]
        );

        return redirect()->route('admin.subscription-plans.index')
            ->with('success', 'Subscription plan created successfully.');
    }

    /**
     * Show the form for editing the specified plan.
     */
    public function edit(SubscriptionPlan $subscriptionPlan): View
    {
        $features = PlanFeature::where('status', 'active')->orderBy('name')->get();
        $selectedFeatureIds = $subscriptionPlan->features()->pluck('plan_features.id')->toArray();

        return view('content.admin.subscription-plans.edit', compact('subscriptionPlan', 'features', 'selectedFeatureIds'));
    }

    /**
     * Update the specified plan in storage.
     */
    public function update(Request $request, SubscriptionPlan $subscriptionPlan): RedirectResponse
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'status' => 'required|in:active,inactive',
            'is_popular' => 'nullable|boolean',
            'feature_ids' => 'nullable|array',
            'feature_ids.*' => 'exists:plan_features,id',
            'price' => 'required|numeric|min:0',
            'billing_period' => 'required|string|max:100',
        ]);

        $subscriptionPlan->update([
            'title' => $request->input('title'),
            'status' => $request->input('status'),
            'is_popular' => $request->boolean('is_popular'),
            'price' => $request->input('price'),
            'billing_period' => $request->input('billing_period'),
        ]);

        $subscriptionPlan->features()->sync($request->input('feature_ids', []));

        AdminAuditLog::log(
            'subscription_plan_update',
            'SubscriptionPlan',
            (string) $subscriptionPlan->id,
            "Updated subscription plan '{$subscriptionPlan->title}'.",
            ['plan_id' => $subscriptionPlan->id, 'title' => $subscriptionPlan->title]
        );

        return redirect()->route('admin.subscription-plans.index')
            ->with('success', 'Subscription plan updated successfully.');
    }

    /**
     * Remove the specified plan from storage.
     */
    public function destroy(SubscriptionPlan $subscriptionPlan): RedirectResponse
    {
        $planTitle = $subscriptionPlan->title;
        $planId = $subscriptionPlan->id;

        $subscriptionPlan->features()->detach();
        $subscriptionPlan->delete();

        AdminAuditLog::log(
            'subscription_plan_delete',
            'SubscriptionPlan',
            (string) $planId,
            "Deleted subscription plan '{$planTitle}'.",
            ['plan_title' => $planTitle]
        );

        return redirect()->route('admin.subscription-plans.index')
            ->with('success', 'Subscription plan deleted successfully.');
    }
}
