<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\TermsCondition;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TermsConditionController extends Controller
{
    /**
     * Get active terms and conditions.
     *
     * GET /api/v1/terms-conditions
     * GET /api/v1/terms-conditions?slug=terms-of-service
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $slug = $request->string('slug')->trim()->toString();

            if ($slug !== '') {
                $terms = TermsCondition::where('slug', $slug)
                    ->where('status', 'Active')
                    ->first();

                if (!$terms) {
                    return response()->json([
                        'success' => false,
                        'message' => "Terms and condition page with slug '{$slug}' not found or inactive."
                    ], 404);
                }

                return response()->json([
                    'success' => true,
                    'message' => 'Terms and condition page retrieved successfully.',
                    'data' => $terms
                ], 200);
            }

            $terms = TermsCondition::where('status', 'Active')->get();

            return response()->json([
                'success' => true,
                'message' => 'Active terms and conditions retrieved successfully.',
                'data' => $terms
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve terms: ' . $e->getMessage()
            ], 500);
        }
    }
}
