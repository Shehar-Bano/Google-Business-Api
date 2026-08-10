<?php

namespace App\Services\Otp\Senders;

use App\Services\Otp\Contracts\OtpSenderInterface;
use Illuminate\Support\Facades\Log;
use Twilio\Rest\Client;

class TwilioOtpSender implements OtpSenderInterface
{
    protected ?Client $client = null;

    public function __construct()
    {
        $sid = config('services.twilio.sid');
        $token = config('services.twilio.token');

        if (!empty($sid) && !empty($token)) {
            if (file_exists(base_path('vendor/twilio/sdk/src/Twilio/autoload.php'))) {
                require_once base_path('vendor/twilio/sdk/src/Twilio/autoload.php');
            }

            if (class_exists(Client::class)) {
                $this->client = new Client($sid, $token);
            }
        }
    }

    /**
     * Send 4-digit OTP via Twilio SMS.
     *
     * @param string $mobileNumber
     * @param string $otp
     * @return void
     */
    public function send(string $mobileNumber, string $otp): void
    {
        Log::info("Twilio OTP Sender: Attempting to send 4-digit OTP {$otp} to {$mobileNumber}");

        if (! $this->client) {
            Log::warning("Twilio Client is not configured. Fallback logged OTP: {$otp}");
            return;
        }

        try {
            $from = config('services.twilio.from');
            $messagingSid = config('services.twilio.messaging_sid');
            $appHash = config('services.twilio.app_hash');

            $body = "Your verification code is: {$otp}";
            if (!empty($appHash)) {
                $body .= "\n" . trim($appHash, "'\"");
            }

            $messagePayload = [
                'body' => $body,
            ];

            if (!empty($messagingSid)) {
                $messagePayload['messagingServiceSid'] = trim($messagingSid, "'\"");
            } elseif (!empty($from)) {
                $messagePayload['from'] = trim($from, "'\"");
            }

            $this->client->messages->create($mobileNumber, $messagePayload);

            Log::info("Twilio OTP SMS sent successfully to {$mobileNumber}");
        } catch (\Throwable $e) {
            Log::error("Twilio OTP SMS failed for {$mobileNumber}: " . $e->getMessage());
        }
    }
}
