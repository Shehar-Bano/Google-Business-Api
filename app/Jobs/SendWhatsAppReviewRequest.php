<?php

namespace App\Jobs;

use App\Models\ReviewRequest;
use App\Services\WhatsApp\WhatsAppProviderInterface;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Exception;

class SendWhatsAppReviewRequest implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * @var ReviewRequest
     */
    protected $reviewRequest;

    /**
     * @var string|null
     */
    protected $customMessage;

    /**
     * @var bool
     */
    public $isReminder;

    /**
     * Create a new job instance.
     */
    public function __construct(ReviewRequest $reviewRequest, ?string $customMessage = null, bool $isReminder = false)
    {
        $this->reviewRequest = $reviewRequest;
        $this->customMessage = $customMessage;
        $this->isReminder = $isReminder;
    }

    /**
     * Execute the job.
     */
    public function handle(WhatsAppProviderInterface $whatsAppProvider): void
    {
        Log::info("Processing SendWhatsAppReviewRequest Job", [
            'review_request_id' => $this->reviewRequest->id,
            'phone' => $this->reviewRequest->phone_number,
        ]);

        try {
            $business = $this->reviewRequest->business;
            $businessName = $business ? $business->name : 'Our Business';

            // Generate Short Tracking URL
            $trackingUrl = route('link.redirect', ['id' => $this->reviewRequest->id]);

            // Construct final message
            if (!empty($this->customMessage)) {
                $message = $this->customMessage;
                // Substitute if present
                $message = str_replace('{{BusinessName}}', $businessName, $message);
                $message = str_replace('{{redirection_url}}', $trackingUrl, $message);
            } elseif ($this->isReminder) {
                $message = "Hi! This is a quick friendly reminder from {$businessName} 🌸\n\n"
                    . "If you have a moment, we would really appreciate it if you could rate us ⭐⭐⭐⭐⭐ on Google. It helps us grow and serve you better!\n\n"
                    . "Review Link:\n"
                    . "{$trackingUrl}\n\n"
                    . "Thank you so much! 😊";
            } else {
                $message = "Thank you for visiting {$businessName} 🙏\n\n"
                    . "If you loved our service, please rate us ⭐⭐⭐⭐⭐ on Google.\n\n"
                    . "A review costs you nothing, but it helps small businesses like ours grow and serve you even better 🚀💛\n\n"
                    . "Review Link:\n"
                    . "{$trackingUrl}\n\n"
                    . "To activate the link, simply reply with \"Hi\". 💬";
            }

            // Call the provider
            $result = $whatsAppProvider->sendMessage($this->reviewRequest->phone_number, $message);

            if ($result['success']) {
                $status = $this->isReminder ? 'reminder_sent' : 'sent';
                $this->reviewRequest->update([
                    'status' => $status,
                    'sent_at' => now(),
                    'whatsapp_message_id' => $result['message_id'],
                ]);

                Log::info("WhatsApp review request sent successfully.", [
                    'review_request_id' => $this->reviewRequest->id,
                    'message_id' => $result['message_id']
                ]);
            } else {
                $this->reviewRequest->update([
                    'status' => 'failed',
                    'failure_reason' => $result['error'] ?? 'Unknown WhatsApp service failure.',
                ]);

                Log::warning("WhatsApp review request sending failed.", [
                    'review_request_id' => $this->reviewRequest->id,
                    'error' => $result['error']
                ]);
            }

        } catch (Exception $e) {
            $this->reviewRequest->update([
                'status' => 'failed',
                'failure_reason' => $e->getMessage(),
            ]);

            Log::error("Exception in SendWhatsAppReviewRequest Job: " . $e->getMessage(), [
                'review_request_id' => $this->reviewRequest->id,
                'trace' => $e->getTraceAsString()
            ]);
        }
    }
}
