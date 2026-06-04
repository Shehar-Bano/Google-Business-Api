<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserHasRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @param  string  $role
     */
    public function handle(Request $request, Closure $next, string $role): Response
    {
        $user = $request->user();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized. Authentication required.',
                'error_code' => 'UNAUTHORIZED',
                'errors' => new \stdClass(),
            ], 401);
        }

        // Check if user has the required role
        if ($user->role !== $role) {
            return response()->json([
                'success' => false,
                'message' => "Access denied. This endpoint is only accessible to {$role}s.",
                'error_code' => 'FORBIDDEN',
                'required_role' => $role,
                'user_role' => $user->role,
                'errors' => new \stdClass(),
            ], 403);
        }

        // Additional status checks
        if ($user->status === 'suspended') {
            return response()->json([
                'success' => false,
                'message' => 'Your account has been suspended. Please contact support.',
                'error_code' => 'ACCOUNT_SUSPENDED',
                'errors' => new \stdClass(),
            ], 403);
        }

        if ($user->role === 'club' && in_array($user->status, ['pending', 'rejected'])) {
            return response()->json([
                'success' => false,
                'message' => $user->status === 'pending' 
                    ? 'Your club profile is pending admin approval.' 
                    : 'Your club profile has been rejected by admin.',
                'error_code' => $user->status === 'pending' ? 'CLUB_PENDING_APPROVAL' : 'CLUB_REJECTED',
                'errors' => new \stdClass(),
            ], 403);
        }

        return $next($request);
    }
}
