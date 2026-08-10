<?php

namespace App\Http\Middleware;

use App\Models\User;
use App\Support\ApiErrorCode;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureApiTokenIsValid
{
    public function handle(Request $request, Closure $next): Response
    {
        $token = $request->bearerToken();

        if (! $token) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized. Token is missing or invalid.',
                'error_code' => ApiErrorCode::UNAUTHORIZED,
                'errors' => new \stdClass(),
            ], 401);
        }

        $hashedToken = hash('sha256', $token);
        $user = User::where('api_access_token', $hashedToken)->first();

        if (! $user) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized. Token is missing or invalid.',
                'error_code' => ApiErrorCode::UNAUTHORIZED,
                'errors' => new \stdClass(),
            ], 401);
        }

        if ($user->status === User::STATUS_SUSPENDED) {
            return response()->json([
                'success' => false,
                'message' => 'Your account has been suspended. Please contact support.',
                'error_code' => 'ACCOUNT_SUSPENDED',
                'errors' => new \stdClass(),
            ], 403);
        }

        $request->setUserResolver(fn () => $user);

        return $next($request);
    }
}
