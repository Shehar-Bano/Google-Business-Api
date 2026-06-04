<?php

use App\Http\Middleware\RedirectIfRole;
use App\Support\ApiErrorCode;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\QueryException;
use Spatie\Permission\Middleware\PermissionMiddleware;
use Spatie\Permission\Middleware\RoleMiddleware;
use Spatie\Permission\Middleware\RoleOrPermissionMiddleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->alias([
            'is_role' => RedirectIfRole::class,
            'role' => RoleMiddleware::class,
            'permission' => PermissionMiddleware::class,
            'role_or_permission' => RoleOrPermissionMiddleware::class,
            'api.token' => \App\Http\Middleware\EnsureApiTokenIsValid::class,
            'api.role' => \App\Http\Middleware\EnsureUserHasRole::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        $exceptions->render(function (ValidationException $exception, Request $request) {
            if (! $request->is('api/*')) {
                return null;
            }

            return response()->json([
                'success' => false,
                'message' => 'Validation failed.',
                'error_code' => ApiErrorCode::VALIDATION_ERROR,
                'errors' => $exception->errors(),
            ], 422);
        });

        $exceptions->render(function (AuthenticationException $exception, Request $request) {
            if (! $request->is('api/*')) {
                return null;
            }

            return response()->json([
                'success' => false,
                'message' => 'Unauthorized. Token is missing or invalid.',
                'error_code' => ApiErrorCode::UNAUTHORIZED,
                'errors' => new stdClass(),
            ], 401);
        });

        $exceptions->render(function (AuthorizationException $exception, Request $request) {
            if (! $request->is('api/*')) {
                return null;
            }

            return response()->json([
                'success' => false,
                'message' => 'User does not have permission.',
                'error_code' => ApiErrorCode::FORBIDDEN,
                'errors' => new stdClass(),
            ], 403);
        });

        $exceptions->render(function (ModelNotFoundException $exception, Request $request) {
            if (! $request->is('api/*')) {
                return null;
            }

            return response()->json([
                'success' => false,
                'message' => 'Record not found.',
                'error_code' => ApiErrorCode::RECORD_NOT_FOUND,
                'errors' => new stdClass(),
            ], 404);
        });

        $exceptions->render(function (QueryException $exception, Request $request) {
            if (! $request->is('api/*')) {
                return null;
            }

            $sqlState = $exception->errorInfo[0] ?? null;
            if ($sqlState === '23000') {
                return response()->json([
                    'success' => false,
                    'message' => 'Duplicate record detected.',
                    'error_code' => ApiErrorCode::DUPLICATE_RESOURCE,
                    'errors' => new stdClass(),
                ], 409);
            }

            return null;
        });
    })->create();
