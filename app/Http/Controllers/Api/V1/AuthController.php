<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Auth\ChangePasswordRequest;
use App\Http\Requests\Api\V1\Auth\CompletePlayerProfileRequest;
use App\Http\Requests\Api\V1\Auth\ForgotPasswordRequest;
use App\Http\Requests\Api\V1\Auth\LoginRequest;
use App\Http\Requests\Api\V1\Auth\RegisterClubRequest;
use App\Http\Requests\Api\V1\Auth\RegisterPlayerRequest;
use App\Http\Requests\Api\V1\Auth\ResendOtpRequest;
use App\Http\Requests\Api\V1\Auth\ResetPasswordRequest;
use App\Http\Requests\Api\V1\Auth\VerifyForgotPasswordOtpRequest;
use App\Http\Requests\Api\V1\Auth\VerifyOtpRequest;
use App\Http\Resources\Api\V1\AuthSessionResource;
use App\Http\Resources\Api\V1\AuthUserResource;
use App\Services\Api\V1\AuthService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    public function __construct(private readonly AuthService $authService) {}

    public function registerPlayer(RegisterPlayerRequest $request): JsonResponse
    {
        // dd($request->all());
        $result = $this->authService->registerPlayer($request);

        return response()->json([
            'success' => true,
            'message' => 'User registered successfully. OTP sent to email.',
            'data' => AuthSessionResource::make($result),
        ], 201);
    }

    public function registerClub(RegisterClubRequest $request): JsonResponse
    {
        $result = $this->authService->registerClub($request);

        return response()->json([
            'success' => true,
            'message' => 'Club registered successfully. OTP sent to email.',
            'data' => [
                'user' => AuthUserResource::make($result['user']),
            ],
        ], 201);
    }

    public function verifyOtp(VerifyOtpRequest $request): JsonResponse
    {
        $result = $this->authService->verifyOtp($request);
        $user = $result['user'];

        return response()->json([
            'success' => true,
            'message' => 'OTP verified successfully.',
            'data' => [
                'user' => AuthUserResource::make($user),
            ],
        ]);
    }

    public function resendOtp(ResendOtpRequest $request): JsonResponse
    {
        $this->authService->resendOtp($request);

        return response()->json([
            'success' => true,
            'message' => 'OTP resent successfully. Please check your email.',
        ]);
    }

    public function login(LoginRequest $request): JsonResponse
    {
        $result = $this->authService->login($request);

        return response()->json([
            'success' => true,
            'message' => 'Login successful.',
            'data' => AuthSessionResource::make($result),
        ]);
    }

    public function forgotPassword(ForgotPasswordRequest $request): JsonResponse
    {
        $result = $this->authService->forgotPassword($request);

        return response()->json([
            'success' => true,
            'message' => 'OTP sent to your email for password reset.',
            'data' => $result,
        ]);
    }

    public function verifyForgotPasswordOtp(VerifyForgotPasswordOtpRequest $request): JsonResponse
    {
        $result = $this->authService->verifyForgotPasswordOtp($request);

        return response()->json([
            'success' => true,
            'message' => 'OTP verified successfully. You can now reset your password.',
            'data' => $result,
        ]);
    }

    public function resetPassword(ResetPasswordRequest $request): JsonResponse
    {
        $this->authService->resetPassword($request);

        return response()->json([
            'success' => true,
            'message' => 'Password reset successfully. Please login with your new password.',
        ]);
    }

    public function completePlayerProfile(CompletePlayerProfileRequest $request): JsonResponse
    {
        $user = $this->authService->completePlayerProfile($request->user(), $request);

        return response()->json([
            'success' => true,
            'message' => 'Profile completed successfully.',
            'data' => [
                'user' => AuthUserResource::make($user),
            ],
        ]);
    }

    public function changePassword(ChangePasswordRequest $request): JsonResponse
    {
        $this->authService->changePassword($request->user(), $request);

        return response()->json([
            'success' => true,
            'message' => 'Password changed successfully.',
        ]);
    }

    public function logout(Request $request): JsonResponse
    {
        $this->authService->logout($request->user());

        return response()->json([
            'success' => true,
            'message' => 'Logged out successfully',
        ]);
    }

    public function deleteAccount(Request $request): JsonResponse
    {
        $this->authService->deleteAccount($request->user());

        return response()->json([
            'success' => true,
            'message' => 'Account deleted successfully.',
        ]);
    }
}
