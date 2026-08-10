<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Content\ShowHelpSupportRequest;
use App\Http\Requests\Api\V1\Content\ShowPrivacyPolicyRequest;
use App\Http\Resources\Api\V1\PrivacyPolicyResource;
use App\Http\Resources\Api\V1\SupportOptionResource;
use App\Services\Api\V1\ContentService;
use Illuminate\Http\JsonResponse;

class ContentController extends Controller
{
    public function __construct(private readonly ContentService $contentService)
    {
    }

    public function helpSupport(ShowHelpSupportRequest $request): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => 'Help and support options fetched successfully.',
            'data' => SupportOptionResource::collection($this->contentService->helpSupport()),
        ]);
    }

    public function privacyPolicy(ShowPrivacyPolicyRequest $request): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => 'Privacy policy fetched successfully.',
            'data' => PrivacyPolicyResource::make($this->contentService->privacyPolicy()),
        ]);
    }

    public function videos(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => 'Videos fetched successfully.',
            'data' => $this->contentService->videos(),
        ]);
    }
}
