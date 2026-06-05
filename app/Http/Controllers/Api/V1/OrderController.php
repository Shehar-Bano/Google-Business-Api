<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Order\IndexOrdersRequest;
use App\Http\Requests\Api\V1\Order\StorePostRequest;
use App\Http\Resources\Api\V1\OrderDetailResource;
use App\Http\Resources\Api\V1\OrderSummaryResource;
use App\Services\Api\V1\OrderService;
use Illuminate\Http\JsonResponse;

class OrderController extends Controller
{
    public function __construct(private readonly OrderService $orderService)
    {
    }

    public function index(IndexOrdersRequest $request): JsonResponse
    {
        return response()->json([
            'success' => true,
            'orders' => OrderSummaryResource::collection($this->orderService->index($request)),
        ]);
    }

    public function show(string $order_id): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => OrderDetailResource::make($this->orderService->show($order_id)),
        ]);
    }

    public function store(StorePostRequest $request): JsonResponse
    {
        $this->orderService->store($request);

        return response()->json([
            'success' => true,
            'message' => 'Post created successfully',
        ], 201);
    }
}
