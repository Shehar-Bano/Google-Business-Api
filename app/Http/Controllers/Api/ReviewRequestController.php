<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\SendWhatsAppReviewRequest;
use App\Http\Requests\SendFollowUpReminders;
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

            $businessId = $request->input('business_id');
            $business = \App\Models\Business::find($businessId);
            if ($business && $business->status === 'suspended') {
                return response()->json([
                    'success' => false,
                    'message' => 'Your business has been suspended. Please contact support.',
                    'error_code' => 'BUSINESS_SUSPENDED',
                    'status' => 'suspended',
                ], 403);
            }

            $result = $this->reviewRequestService->sendRequests($request->validated(), $authUser);

            Log::info("ReviewRequest API Response sent successfully", [
                'count' => $result['count']
            ]);

            $formattedRequests = collect($result['requests'])->map(function ($req) {
                return [
                    'id' => $req->id,
                    'business_id' => $req->business_id,
                    'sender_id' => $req->sender_id,
                    'sent_to' => $req->sent_to,
                    'phone_number' => $req->phone_number,
                    'customer_name' => $req->customer_name,
                    'channel' => $req->channel,
                    'status' => $req->status,
                    'redirection_url' => route('link.redirect', ['id' => $req->id]),
                    'created_at' => $req->created_at,
                    'updated_at' => $req->updated_at,
                ];
            });

            return response()->json([
                'success' => true,
                'message' => 'Review requests queued successfully.',
                'data' => [
                    'count' => $result['count'],
                    'requests' => $formattedRequests
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

    /**
     * Get review requests list with statistics for the logged-in user.
     *
     * GET /api/v1/whatsapp/review-requests
     *
     * @param \Illuminate\Http\Request $request
     * @return JsonResponse
     */
    public function listRequests(\Illuminate\Http\Request $request): JsonResponse
    {
        try {
            $authUser = $request->user();
            if (!$authUser) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized.'
                ], 401);
            }

            $businessId = $request->input('business_id');
            $business = $businessId 
                ? $authUser->businesses()->find($businessId) 
                : $authUser->businesses()->first();

            if ($business && $business->status === 'suspended') {
                return response()->json([
                    'success' => false,
                    'message' => 'Your business has been suspended. Please contact support.',
                    'error_code' => 'BUSINESS_SUSPENDED',
                    'status' => 'suspended',
                ], 403);
            }

            // Get business IDs belonging to the authenticated user
            $businessIds = $authUser->businesses()->pluck('id');

            $query = \App\Models\ReviewRequest::whereIn('business_id', $businessIds);

            // Optional filtering by business_id
            if ($request->has('business_id')) {
                $query->where('business_id', $request->input('business_id'));
            }

            $totalRequests = (clone $query)->count();
            $sentViaPersonal = (clone $query)->where('channel', 'personal')->count();
            $sentViaApp = (clone $query)->where('channel', 'app')->count();

            $requests = $query->orderBy('id', 'desc')->get();

            $formattedRequests = $requests->map(function ($req) {
                $reminders = \Illuminate\Support\Facades\DB::table('request_reminders')
                    ->where('request_id', $req->id)
                    ->orderBy('id', 'asc')
                    ->get();

                $latestReminder = $reminders->last();

                $remindersData = $reminders->map(function ($r) {
                    return [
                        'reminder_id' => $r->id,
                        'sent_by' => $r->sent_by,
                        'channel' => $r->channel,
                        'sent_at' => \Carbon\Carbon::parse($r->created_at)->format('Y-m-d H:i:s'),
                    ];
                });

                return [
                    'request_id' => $req->id,
                    'customer_name' => $req->customer_name,
                    'customer_phone' => $req->phone_number,
                    'channel' => $req->channel,
                    'status' => $req->status,
                    'redirection_url' => route('link.redirect', ['id' => $req->id]),
                    'sent_at' => ($req->created_at ?? $req->sent_at)?->format('Y-m-d H:i:s'),
                    'clicked_at' => $req->clicked_at?->format('Y-m-d H:i:s'),
                    'reminder_sent_at' => $latestReminder ? \Carbon\Carbon::parse($latestReminder->created_at)->format('Y-m-d H:i:s') : null,
                    'reminders_count' => $reminders->count(),
                    'reminders' => $remindersData,
                ];
            });

            return response()->json([
                'success' => true,
                'data' => [
                    'total_requests' => $totalRequests,
                    'sent_via_personal' => $sentViaPersonal,
                    'sent_via_app' => $sentViaApp,
                    'requests' => $formattedRequests
                ]
            ], 200);

        } catch (Exception $e) {
            Log::error("ListReviewRequests API Error: " . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve review requests: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Send follow-up reminders.
     *
     * POST /api/v1/review-requests/send-reminders
     *
     * @param SendFollowUpReminders $request
     * @return JsonResponse
     */
    public function sendReminders(SendFollowUpReminders $request): JsonResponse
    {
        try {
            $authUser = $request->user();

            $businessId = $request->input('business_id');
            $business = \App\Models\Business::find($businessId);
            if ($business && $business->status === 'suspended') {
                return response()->json([
                    'success' => false,
                    'message' => 'Your business has been suspended. Please contact support.',
                    'error_code' => 'BUSINESS_SUSPENDED',
                    'status' => 'suspended',
                ], 403);
            }

            $result = $this->reviewRequestService->sendFollowUpReminders($request->validated(), $authUser);

            $channel = $request->input('channel');
            $channelName = $channel === 'personal' ? 'Personal' : 'Application';

            return response()->json([
                'success' => true,
                'message' => "Reminders successfully dispatched via {$channelName}.",
                'data' => [
                    'reminders_sent' => $result['reminders_sent'],
                    'sent_at' => $result['sent_at']
                ]
            ], 200);

        } catch (Exception $e) {
            Log::error("SendReminders API Error: " . $e->getMessage(), [
                'payload' => $request->all(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to dispatch reminders: ' . $e->getMessage()
            ], 500);
        }
    }
}
