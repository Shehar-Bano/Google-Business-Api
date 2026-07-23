<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\GoogleLoginRequest;
use App\Services\AuthService;
use Illuminate\Http\JsonResponse;

class AuthController extends Controller
{
    public function __construct(protected AuthService $authService) {}

    /**
     * Authenticate user using Google ID Token.
     */
    public function googleLogin(GoogleLoginRequest $request): JsonResponse
    {
        $result = $this->authService->loginWithGoogle(
            $request->input('id_token'),
            $request->user(),
            $request->bearerToken()
        );

        if (!$result) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid Google ID Token or verification failed.',
            ], 401);
        }

        return response()->json([
            'success' => true,
            'message' => 'Login Successful',
            'token' => $result['token'],
            'user' => $result['user'],
        ]);
    }
}
