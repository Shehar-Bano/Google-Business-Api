<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\CityResource;
use App\Models\City;
use Illuminate\Http\JsonResponse;

class PublicCityController extends Controller
{
    public function index(): JsonResponse
    {
        $cities = City::query()
            ->forPakistan()
            ->active()
            ->orderBy('name')
            ->get();

        return response()->json([
            'success' => true,
            'message' => 'Pakistan cities fetched successfully.',
            'data' => CityResource::collection($cities),
        ]);
    }
}
