<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;

class ConfigController extends Controller
{
    /**
     * Get Google login configuration credentials.
     * GET /api/config/google
     */
    public function googleConfig(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'client_id' => config('services.google.client_id'),
            'client_secret' => config('services.google.client_secret'),
            'redirect_uri' => config('services.google.redirect'),
        ]);
    }

    /**
     * Get Meta / Facebook connection configuration credentials.
     * GET /api/config/meta
     */
    public function metaConfig(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'client_id' => config('services.facebook.client_id'),
            'client_secret' => config('services.facebook.client_secret'),
            'redirect_uri' => config('services.facebook.redirect'),

            'graph_version' => config('meta.graph_version'),
            'graph_base_url' => config('meta.base_url'),

        ]);
    }
}
