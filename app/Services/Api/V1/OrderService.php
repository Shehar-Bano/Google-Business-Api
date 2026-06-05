<?php

namespace App\Services\Api\V1;

use App\Http\Requests\Api\V1\Order\IndexOrdersRequest;
use App\Http\Requests\Api\V1\Order\StorePostRequest;
use App\Models\Order;
use App\Support\ApiErrorCode;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Facades\DB;

class OrderService
{
    public function index(IndexOrdersRequest $request): Collection
    {
        return Order::query()
            ->when($request->filled('status'), fn ($query) => $query->where('status', $request->string('status')->toString()))
            ->latest()
            ->get();
    }

    public function show(string $orderId): Order
    {
        $order = Order::query()
            ->with('detail')
            ->where('id', $orderId)
            ->first();

        if (! $order) {
            $this->throwApiError('Order not found.', ApiErrorCode::RECORD_NOT_FOUND, 404);
        }

        return $order;
    }

    public function store(StorePostRequest $request): Order
    {
        return DB::transaction(function () use ($request) {
            $order = Order::create([
                'order_id' => $this->nextOrderId(),
                'status' => Order::STATUS_IN_REVIEW,
            ]);

            $imagePaths = [];
            foreach ($request->file('images', []) as $image) {
                $imagePaths[] = $image->store('orders', 'public');
            }

            $order->detail()->create([
                'description' => $request->string('description')->toString(),
                'price' => 0,
                'phone' => $request->string('phone')->toString(),
                'address' => $request->string('address')->toString(),
                'date' => $request->date('date'),
                'time' => $request->string('time')->toString(),
                'images' => $imagePaths,
            ]);

            return $order->load('detail');
        });
    }

    private function nextOrderId(): string
    {
        $latestOrderId = Order::query()
            ->whereNotNull('order_id')
            ->lockForUpdate()
            ->latest('id')
            ->value('order_id');

        $latestNumber = 1000;
        if (is_string($latestOrderId) && preg_match('/^ORD-(\d+)$/', $latestOrderId, $matches)) {
            $latestNumber = (int) $matches[1];
        }

        return 'ORD-'.($latestNumber + 1);
    }

    private function throwApiError(string $message, string $errorCode, int $status): never
    {
        throw new HttpResponseException(response()->json([
            'success' => false,
            'message' => $message,
            'error_code' => $errorCode,
            'errors' => new \stdClass,
        ], $status));
    }
}
