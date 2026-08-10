<?php

namespace App\Services\Otp\Senders;

use App\Services\Otp\Contracts\OtpSenderInterface;
use Illuminate\Support\Facades\Log;

class DummyOtpSender implements OtpSenderInterface
{
    /**
     * Send OTP code to the mobile number (dummy logger implementation).
     *
     * @param string $mobileNumber
     * @param string $otp
     * @return void
     */
    public function send(string $mobileNumber, string $otp): void
    {
        Log::info("Dummy OTP Sender: OTP {$otp} generated for mobile number {$mobileNumber}");
    }
}
