<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Player\CancelBookingRequest;
use App\Http\Requests\Api\V1\Player\IndexPlayerBookingsRequest;
use App\Http\Requests\Api\V1\Player\StoreBookingRequest;
use App\Http\Resources\Api\V1\PlayerBookingDetailResource;
use App\Http\Resources\Api\V1\PlayerBookingListResource;
use App\Http\Resources\Api\V1\PlayerBookingResource;
use App\Services\PlayerBookingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PlayerBookingController extends Controller
{
    public function __construct(private readonly PlayerBookingService $playerBookingService)
    {
    }

    public function store(StoreBookingRequest $request): JsonResponse
    {
        $booking = $this->playerBookingService->storeBooking(
            $request->user(),
            $request->validated()
        );

        return response()->json([
            'success' => true,
            'message' => 'Booking confirmed successfully',
            'data' => new PlayerBookingResource($booking),
        ], 201);
    }

    public function index(IndexPlayerBookingsRequest $request): JsonResponse
    {
        $filter = $request->string('filter')->toString() ?: $request->string('status')->toString();

        $payload = $this->playerBookingService->playerBookings(
            $request->user(),
            $filter ?: null,
            (int) $request->integer('page', 1),
            (int) $request->integer('limit', 10)
        );

        return response()->json([
            'success' => true,
            'message' => 'Bookings fetched successfully',
            'data' => PlayerBookingListResource::collection(collect($payload['items'])),
            'pagination' => $payload['pagination'],
        ]);
    }

    public function show(Request $request, string $booking_id): JsonResponse
    {
        $booking = $this->playerBookingService->playerBookingDetail($request->user(), (int) $booking_id);

        return response()->json([
            'success' => true,
            'message' => 'Booking detail fetched successfully',
            'data' => new PlayerBookingDetailResource($booking),
        ]);
    }

    public function cancel(CancelBookingRequest $request): JsonResponse
    {
        $booking = $this->playerBookingService->cancelBooking(
            $request->user(),
            (int) $request->integer('booking_id')
        );

        return response()->json([
            'success' => true,
            'message' => 'Booking cancelled successfully',
            'data' => [
                'booking_id' => $booking->id,
                'booking_status' => $booking->booking_status,
            ],
        ]);
    }
}
