<?php

namespace App\Services;

use App\Models\Business;
use App\Models\ReviewRequest;
use App\Models\User;
use App\Jobs\SendWhatsAppReviewRequest;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Exception;

class ReviewRequestService
{
    /**
     * Send review requests via WhatsApp.
     *
     * @param array $data Input payload data.
     * @param User|null $authUser Authenticated user model.
     * @return array Service operation result.
     * @throws Exception
     */
    public function sendRequests(array $data, ?User $authUser): array
    {
        $businessId = $data['business_id'];
        $channel = $data['channel'];
        $customMessage = $data['message'] ?? null;

        $business = Business::find($businessId);
        if (!$business) {
            throw new Exception("Business with ID {$businessId} not found.");
        }

        // Generate dynamic redirection URL using Google Place ID
        $placeId = $business->google_place_id ?? '';
        $redirectionUrl = "https://search.google.com/local/writereview?placeid={$placeId}";

        Log::info("Initiating WhatsApp Review Request Service", [
            'business_id' => $businessId,
            'channel' => $channel,
            'sender_id' => $authUser ? $authUser->id : 'app'
        ]);

        $createdRequests = [];

        DB::beginTransaction();
        try {
            if ($channel === 'personal') {
                if (!$authUser) {
                    throw new Exception("Authenticated user required for personal channel.");
                }

                $customer = $data['customers'][0];
                $targetUser = User::where('phone', $customer['phone'])->first();

                $reviewRequest = ReviewRequest::create([
                    'business_id' => $business->id,
                    'sender_id' => (string) $authUser->id,
                    'sent_to' => $targetUser ? $targetUser->id : null,
                    'phone_number' => $customer['phone'],
                    'customer_name' => $customer['name'],
                    'channel' => 'personal',
                    'status' => 'requested',
                    'redirection_url' => $redirectionUrl,
                ]);

                // Dispatch Queue Job
                SendWhatsAppReviewRequest::dispatch($reviewRequest, $customMessage);

                $createdRequests[] = $reviewRequest;

            } elseif ($channel === 'app') {
                $customers = $data['customers'] ?? [];

                foreach ($customers as $customer) {
                    // Check if the phone number belongs to any user in the system
                    $targetUser = User::where('phone', $customer['phone'])->first();

                    $reviewRequest = ReviewRequest::create([
                        'business_id' => $business->id,
                        'sender_id' => $authUser ? (string) $authUser->id : 'app',
                        'sent_to' => $targetUser ? $targetUser->id : null,
                        'phone_number' => $customer['phone'],
                        'customer_name' => $customer['name'],
                        'channel' => 'app',
                        'status' => 'requested',
                        'redirection_url' => $redirectionUrl,
                    ]);

                    // Dispatch Queue Job
                    SendWhatsAppReviewRequest::dispatch($reviewRequest, $customMessage);

                    $createdRequests[] = $reviewRequest;
                }
            }

            DB::commit();

            return [
                'success' => true,
                'count' => count($createdRequests),
                'requests' => $createdRequests
            ];

        } catch (Exception $e) {
            DB::rollBack();
            Log::error("Failed to process review requests: " . $e->getMessage(), [
                'payload' => $data,
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }
}
