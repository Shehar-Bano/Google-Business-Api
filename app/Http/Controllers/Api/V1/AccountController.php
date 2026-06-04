<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Account\DashboardRequest;
use App\Http\Requests\Api\V1\Account\ShowProfileRequest;
use App\Http\Requests\Api\V1\Account\UpdateProfileDetailsRequest;
use App\Http\Requests\Api\V1\Account\UpdateProfileLogoRequest;
use App\Http\Resources\Api\V1\AccountProfileResource;
use App\Http\Resources\Api\V1\DashboardResource;
use App\Services\Api\V1\AccountService;
use Illuminate\Http\JsonResponse;

class AccountController extends Controller
{
    public function __construct(private readonly AccountService $accountService)
    {
    }

    public function dashboard(DashboardRequest $request): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => 'Dashboard fetched successfully.',
            'data' => DashboardResource::make($this->accountService->dashboard($request->user())),
        ]);
    }

    public function profile(ShowProfileRequest $request): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => 'Profile fetched successfully.',
            'data' => [
                'user' => AccountProfileResource::make($request->user()),
            ],
        ]);
    }

    public function updateDetails(UpdateProfileDetailsRequest $request): JsonResponse
    {
        $user = $this->accountService->updateDetails($request->user(), $request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Profile details updated successfully.',
            'data' => [
                'user' => AccountProfileResource::make($user),
            ],
        ]);
    }

    public function updateLogo(UpdateProfileLogoRequest $request): JsonResponse
    {
        $user = $this->accountService->updateLogo($request->user(), $request);

        return response()->json([
            'success' => true,
            'message' => 'Profile image updated successfully.',
            'data' => [
                'user' => AccountProfileResource::make($user),
            ],
        ]);
    }
}
