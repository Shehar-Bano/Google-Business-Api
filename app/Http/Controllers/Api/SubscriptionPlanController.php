<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\SubscriptionPlan;
use Illuminate\Http\JsonResponse;

class SubscriptionPlanController extends Controller
{
    /**
     * Get list of active subscription plans.
     *
     * GET /api/v1/subscription-plans
     *
     * @return JsonResponse
     */
    public function index(): JsonResponse
    {
        try {
            $plans = SubscriptionPlan::where('status', 'active')
                ->with(['features' => function ($q) {
                    $q->where('plan_features.status', 'active')
                      ->select('plan_features.id', 'plan_features.name', 'plan_features.slug', 'plan_features.description');
                }])
                ->orderBy('price', 'asc')
                ->get();

            return response()->json([
                'success' => true,
                'message' => 'Subscription plans retrieved successfully.',
                'data' => $plans
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve plans: ' . $e->getMessage()
            ], 500);
        }
    }
}
