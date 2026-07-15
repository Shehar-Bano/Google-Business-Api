<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Otp\ResendOtpRequest;
use App\Http\Requests\Api\v1\Otp\SendOtpRequest;
use App\Http\Requests\Api\V1\Otp\VerifyOtpRequest;
use App\Services\OtpService;
use Illuminate\Http\JsonResponse;

class OtpController extends Controller
{
    public function __construct(
        protected readonly OtpService $otpService
    ) {}

    /**
     * Send OTP to user's mobile number.
     */
    public function sendOtp(SendOtpRequest $request): JsonResponse
    {
        $result = $this->otpService->sendOtp($request->input('mobile_number'));

        return response()->json($result, 200);
    }

    /**
     * Resend OTP to user's mobile number.
     */
    public function resendOtp(ResendOtpRequest $request): JsonResponse
    {
        $result = $this->otpService->resendOtp($request->input('mobile_number'));

        return response()->json($result, 200);
    }

    /**
     * Verify OTP submitted by the user.
     */
    public function verifyOtp(VerifyOtpRequest $request): JsonResponse
    {
        $result = $this->otpService->verifyOtp(
            $request->input('mobile_number'),
            $request->input('otp')
        );

        if (! $result['success']) {
            return response()->json([
                'success' => false,
                'message' => $result['message'],
            ], $result['status']);
        }

        return response()->json([
            'success' => true,
            'message' => $result['message'],
            'access_token' => $result['access_token'] ?? null,
            'refresh_token' => $result['refresh_token'] ?? null,
            'user' => $result['user'] ?? null,
        ], 200);
    }
}
