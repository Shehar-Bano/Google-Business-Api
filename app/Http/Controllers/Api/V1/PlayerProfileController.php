<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Player\UpdatePlayerDetailsRequest;
use App\Http\Requests\Api\V1\Player\UpdatePlayerLogoRequest;
use App\Http\Resources\Api\V1\PlayerProfileResource;
use App\Services\PlayerProfileService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PlayerProfileController extends Controller
{
    public function __construct(private readonly PlayerProfileService $playerProfileService)
    {
    }

    public function show(Request $request): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => 'Player profile fetched successfully',
            'data' => new PlayerProfileResource($request->user()),
        ]);
    }

    public function updatePlayerDetails(UpdatePlayerDetailsRequest $request): JsonResponse
    {
        $player = $this->playerProfileService->updateDetails($request->user(), $request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Player details updated successfully.',
            'data' => new PlayerProfileResource($player),
        ]);
    }

    public function updatePlayerLogo(UpdatePlayerLogoRequest $request): JsonResponse
    {
        $player = $this->playerProfileService->updateLogo($request->user(), $request->file('profile_image'));

        return response()->json([
            'success' => true,
            'message' => 'Player logo updated successfully.',
            'data' => new PlayerProfileResource($player),
        ]);
    }
}
