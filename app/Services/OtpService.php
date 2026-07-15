<?php

namespace App\Services;

use App\Models\Otp;
use App\Models\User;
use App\Services\Otp\Contracts\OtpSenderInterface;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class OtpService
{
    public function __construct(
        protected readonly OtpSenderInterface $otpSender
    ) {}

    /**
     * Send OTP to a mobile number.
     */
    public function sendOtp(string $mobileNumber): array
    {
        $otpCode = (string) random_int(1000, 9999);
        $expiresAt = now()->addSeconds(30);

        // Check if an unexpired, unverified OTP already exists for the same mobile number
        $existingOtp = Otp::where('mobile_number', $mobileNumber)
            ->where('verified', false)
            ->where('expires_at', '>', now())
            ->first();

        if ($existingOtp) {
            $existingOtp->update([
                'otp' => $otpCode,
                'expires_at' => $expiresAt,
            ]);
        } else {
            Otp::create([
                'mobile_number' => $mobileNumber,
                'otp' => $otpCode,
                'verified' => false,
                'expires_at' => $expiresAt,
            ]);
        }

        // Send OTP using the configured channel sender (SMS and/or WhatsApp)
        $this->otpSender->send($mobileNumber, $otpCode);

        return [
            'success' => true,
            'message' => 'OTP sent successfully.',
            'otp' => $otpCode,
            'expires_in' => 30,
        ];
    }

    /**
     * Resend OTP to a mobile number.
     */
    public function resendOtp(string $mobileNumber): array
    {
        // "Resending OTP should invalidate the previous OTP."
        // We delete or mark as verified/expired any existing unverified OTPs for this number
        Otp::where('mobile_number', $mobileNumber)
            ->where('verified', false)
            ->delete();

        $otpCode = (string) random_int(1000, 9999);
        $expiresAt = now()->addSeconds(30);

        Otp::create([
            'mobile_number' => $mobileNumber,
            'otp' => $otpCode,
            'verified' => false,
            'expires_at' => $expiresAt,
        ]);

        // Send OTP using the configured channel sender (SMS and/or WhatsApp)
        $this->otpSender->send($mobileNumber, $otpCode);

        return [
            'success' => true,
            'message' => 'OTP resent successfully.',
            'otp' => $otpCode,
            'expires_in' => 30,
        ];
    }

    /**
     * Verify OTP for a mobile number.
     */
    public function verifyOtp(string $mobileNumber, string $otp): array
    {
        // Retrieve the latest unverified OTP for this number
        $otpRecord = Otp::where('mobile_number', $mobileNumber)
            ->where('verified', false)
            ->latest('id')
            ->first();

        if (! $otpRecord) {
            return [
                'success' => false,
                'status' => 422,
                'message' => 'Invalid OTP.',
            ];
        }

        // Verify if OTP matches
        if ($otpRecord->otp !== $otp) {
            return [
                'success' => false,
                'status' => 422,
                'message' => 'Invalid OTP.',
            ];
        }

        // Verify if OTP is expired
        if ($otpRecord->expires_at->isPast()) {
            return [
                'success' => false,
                'status' => 422,
                'message' => 'OTP has expired.',
            ];
        }

        // OTP can only be used once, mark it as verified
        $otpRecord->update([
            'verified' => true,
        ]);

        // Invalidate any other unverified OTP records for the same mobile number to clean up
        Otp::where('mobile_number', $mobileNumber)
            ->where('verified', false)
            ->delete();

        // 1. Find or create the user using their mobile number
        $user = User::where('phone', $mobileNumber)->first();

        if (! $user) {
            $user = User::create([
                'name' => null,
                'email' => null,
                'phone' => $mobileNumber,
                'password' => null,
                'role' => 'user',
                'status' => 'active',
                'otp_verified' => true,
            ]);

            // Assign standard role if Spatie is present
            if (class_exists(\Spatie\Permission\Models\Role::class) && \Spatie\Permission\Models\Role::where('name', 'player')->exists()) {
                $user->assignRole('player');
            }
        } else {
            // Update phone if not set
            if (empty($user->phone)) {
                $user->phone = $mobileNumber;
            }
            // Update otp_verified status if not verified
            if (! $user->otp_verified) {
                $user->otp_verified = true;
                if ($user->status === 'otp_pending') {
                    $user->status = 'active';
                }
                $user->save();
            }
        }

        // 2. Issue session tokens (matching EnsureApiTokenIsValid custom token design)
        $plainAccessToken = bin2hex(random_bytes(32));
        $plainRefreshToken = bin2hex(random_bytes(32));

        $user->api_access_token = hash('sha256', $plainAccessToken);
        $user->api_refresh_token = hash('sha256', $plainRefreshToken);
        $user->save();

        return [
            'success' => true,
            'status' => 200,
            'message' => 'OTP verified successfully.',
            'access_token' => $plainAccessToken,
            'refresh_token' => $plainRefreshToken,
            'user' => $user,
        ];
    }
}
