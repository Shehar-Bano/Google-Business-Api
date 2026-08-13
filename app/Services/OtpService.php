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
        if ($mobileNumber === '+923086659864') {
            $otpCode = '1234';
        }
        $expiresAt = now()->addSeconds(60);

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

        $sid = config('services.twilio.sid');
        $token = config('services.twilio.token');
        $verifySid = config('services.twilio.verify_sid');

        if ($mobileNumber !== '+966561234567' && $mobileNumber !== '+923086659864' && !empty($sid) && !empty($token) && !empty($verifySid)) {
            if (file_exists(base_path('vendor/twilio/sdk/src/Twilio/autoload.php'))) {
                require_once base_path('vendor/twilio/sdk/src/Twilio/autoload.php');
            }

            try {
                $twilio = new \Twilio\Rest\Client($sid, $token);
                $twilio->verify->v2->services($verifySid)
                    ->verifications
                    ->create($mobileNumber, 'sms');
            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Log::error("Twilio Verify Send Error for {$mobileNumber}: " . $e->getMessage());
                $this->otpSender->send($mobileNumber, $otpCode);
            }
        } else {
            $this->otpSender->send($mobileNumber, $otpCode);
        }

        return [
            'success' => true,
            'message' => 'OTP sent successfully.',
            'expires_in' => 60,
        ];
    }

    /**
     * Resend OTP to a mobile number.
     */
    public function resendOtp(string $mobileNumber): array
    {
        Otp::where('mobile_number', $mobileNumber)
            ->where('verified', false)
            ->delete();

        $otpCode = (string) random_int(1000, 9999);
        if ($mobileNumber === '+923086659864') {
            $otpCode = '1234';
        }
        $expiresAt = now()->addSeconds(60);

        Otp::create([
            'mobile_number' => $mobileNumber,
            'otp' => $otpCode,
            'verified' => false,
            'expires_at' => $expiresAt,
        ]);

        $sid = config('services.twilio.sid');
        $token = config('services.twilio.token');
        $verifySid = config('services.twilio.verify_sid');

        if ($mobileNumber !== '+966561234567' && $mobileNumber !== '+923086659864' && !empty($sid) && !empty($token) && !empty($verifySid)) {
            if (file_exists(base_path('vendor/twilio/sdk/src/Twilio/autoload.php'))) {
                require_once base_path('vendor/twilio/sdk/src/Twilio/autoload.php');
            }

            try {
                $twilio = new \Twilio\Rest\Client($sid, $token);
                $twilio->verify->v2->services($verifySid)
                    ->verifications
                    ->create($mobileNumber, 'sms');
            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Log::error("Twilio Verify Resend Error for {$mobileNumber}: " . $e->getMessage());
                $this->otpSender->send($mobileNumber, $otpCode);
            }
        } else {
            $this->otpSender->send($mobileNumber, $otpCode);
        }

        return [
            'success' => true,
            'message' => 'OTP resent successfully.',
            'expires_in' => 60,
        ];
    }

    /**
     * Verify OTP for a mobile number using Twilio Verify API.
     */
    public function verifyOtp(string $mobileNumber, string $otp): array
    {
        $sid = config('services.twilio.sid');
        $token = config('services.twilio.token');
        $verifySid = config('services.twilio.verify_sid');

        $isVerified = false;

        // Bypass for testing dummy phone
        if (($mobileNumber === '+966561234567' || $mobileNumber === '+923086659864') && in_array($otp, ['1234', '123456'], true)) {
            $isVerified = true;
        } elseif (!empty($sid) && !empty($token) && !empty($verifySid)) {
            if (file_exists(base_path('vendor/twilio/sdk/src/Twilio/autoload.php'))) {
                require_once base_path('vendor/twilio/sdk/src/Twilio/autoload.php');
            }

            try {
                $twilio = new \Twilio\Rest\Client($sid, $token);
                $check = $twilio->verify->v2->services($verifySid)
                    ->verificationChecks
                    ->create([
                        'to' => $mobileNumber,
                        'code' => $otp,
                    ]);

                if ($check->status === 'approved') {
                    $isVerified = true;
                }
            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Log::error("Twilio Verify Check Error for {$mobileNumber}: " . $e->getMessage());
            }
        }

        // Fallback to database check (for local testing / test suite)
        if (! $isVerified) {
            $otpRecord = Otp::where('mobile_number', $mobileNumber)
                ->where('verified', false)
                ->latest('id')
                ->first();

            if ($otpRecord && $otpRecord->otp === $otp && ! $otpRecord->expires_at->isPast()) {
                $otpRecord->update(['verified' => true]);
                $isVerified = true;
            }
        }

        if (! $isVerified) {
            return [
                'success' => false,
                'status' => 422,
                'message' => 'Invalid or expired OTP.',
            ];
        }

        // Invalidate any other unverified OTP records for the same mobile number to clean up
        Otp::where('mobile_number', $mobileNumber)
            ->where('verified', false)
            ->delete();

        // 1. Find or create the user using their mobile number (format-insensitive check to prevent duplicates)
        $cleanNumber = preg_replace('/[^0-9]/', '', $mobileNumber);
        $user = User::where(function($query) use ($mobileNumber, $cleanNumber) {
            $query->where('phone', $mobileNumber)
                  ->orWhereRaw("REPLACE(REPLACE(REPLACE(REPLACE(phone, ' ', ''), '-', ''), '+', ''), '(', '') = ?", [$cleanNumber]);
        })->first();

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
            // Check if user is suspended
            if ($user->status === User::STATUS_SUSPENDED) {
                return [
                    'success' => false,
                    'status' => 403,
                    'message' => 'Your account has been suspended. Please contact support.',
                ];
            }

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
