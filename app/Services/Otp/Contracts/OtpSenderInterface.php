<?php

namespace App\Services\Otp\Contracts;

interface OtpSenderInterface
{
    /**
     * Send OTP code to the mobile number.
     *
     * @param string $mobileNumber
     * @param string $otp
     * @return void
     */
    public function send(string $mobileNumber, string $otp): void;
}
