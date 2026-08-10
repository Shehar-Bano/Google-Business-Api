<?php

namespace App\Support;

use Illuminate\Http\JsonResponse;

trait ApiResponder
{
    protected function successResponse(
        string $message,
        mixed $data = [],
        int $status = 200
    ): JsonResponse {
        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => $data,
        ], $status);
    }

    protected function errorResponse(
        string $message,
        string $errorCode,
        array $errors = [],
        int $status = 400
    ): JsonResponse {
        return response()->json([
            'success' => false,
            'message' => $message,
            'error_code' => $errorCode,
            'errors' => empty($errors) ? new \stdClass() : $errors,
        ], $status);
    }
}
