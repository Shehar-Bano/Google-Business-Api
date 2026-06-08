<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Account\StoreFcmTokenRequest;
use App\Http\Resources\Api\V1\UserFcmTokenResource;
use App\Services\FcmTokenService;
use Illuminate\Http\JsonResponse;

class AccountFcmTokenController extends Controller
{
    public function __construct(private readonly FcmTokenService $fcmTokenService) {}

    public function store(StoreFcmTokenRequest $request): JsonResponse
    {
        $fcmToken = $this->fcmTokenService->saveForUser($request->user(), $request->validated());

        return response()->json([
            'success' => true,
            'message' => 'FCM token saved successfully.',
            'data' => new UserFcmTokenResource($fcmToken),
        ]);
    }
}
