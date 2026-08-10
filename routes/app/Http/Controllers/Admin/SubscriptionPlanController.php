<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SubscriptionPlan;
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
        return view('content.admin.subscription-plans.create');
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
            'features' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'billing_period' => 'required|string|max:100',
        ]);

        // Convert new lines to clean array
        $featuresArray = [];
        if ($request->filled('features')) {
            $featuresArray = array_filter(array_map('trim', explode("\n", str_replace("\r", "", $request->input('features')))));
        }

        $plan = SubscriptionPlan::create([
            'title' => $request->input('title'),
            'status' => $request->input('status'),
            'is_popular' => $request->boolean('is_popular'),
            'features' => array_values($featuresArray),
            'price' => $request->input('price'),
            'billing_period' => $request->input('billing_period'),
        ]);

        \App\Models\AdminAuditLog::log(
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
        $featuresString = is_array($subscriptionPlan->features) 
            ? implode("\n", $subscriptionPlan->features) 
            : '';

        return view('content.admin.subscription-plans.edit', compact('subscriptionPlan', 'featuresString'));
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
            'features' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'billing_period' => 'required|string|max:100',
        ]);

        // Convert new lines to clean array
        $featuresArray = [];
        if ($request->filled('features')) {
            $featuresArray = array_filter(array_map('trim', explode("\n", str_replace("\r", "", $request->input('features')))));
        }

        $subscriptionPlan->update([
            'title' => $request->input('title'),
            'status' => $request->input('status'),
            'is_popular' => $request->boolean('is_popular'),
            'features' => array_values($featuresArray),
            'price' => $request->input('price'),
            'billing_period' => $request->input('billing_period'),
        ]);

        \App\Models\AdminAuditLog::log(
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
        $subscriptionPlan->delete();

        \App\Models\AdminAuditLog::log(
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
