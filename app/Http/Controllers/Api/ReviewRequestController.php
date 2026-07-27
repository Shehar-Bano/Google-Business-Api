<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\SendWhatsAppReviewRequest;
use App\Services\ReviewRequestService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Exception;

class ReviewRequestController extends Controller
{
    /**
     * @var ReviewRequestService
     */
    protected $reviewRequestService;

    /**
     * ReviewRequestController constructor.
     */
    public function __construct(ReviewRequestService $reviewRequestService)
    {
        $this->reviewRequestService = $reviewRequestService;
    }

    /**
     * Send Google Review request via WhatsApp.
     *
     * POST /api/v1/whatsapp/review-request
     *
     * @param SendWhatsAppReviewRequest $request
     * @return JsonResponse
     */
    public function sendWhatsAppRequest(SendWhatsAppReviewRequest $request): JsonResponse
    {
        Log::info("ReviewRequest API Request received", [
            'payload' => $request->except(['phone_numbers'])
        ]);

        try {
            $authUser = $request->user();
            $result = $this->reviewRequestService->sendRequests($request->validated(), $authUser);

            Log::info("ReviewRequest API Response sent successfully", [
                'count' => $result['count']
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Review requests queued successfully.',
                'data' => [
                    'count' => $result['count'],
                    'requests' => $result['requests']
                ]
            ], 200);

        } catch (Exception $e) {
            Log::error("ReviewRequest API Error: " . $e->getMessage(), [
                'payload' => $request->all(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to queue review requests: ' . $e->getMessage()
            ], 500);
        }
    }
}
