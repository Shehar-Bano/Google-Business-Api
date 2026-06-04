<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Player\ClubCourtsRequest;
use App\Http\Requests\Api\V1\Player\IndexClubsRequest;
use App\Http\Requests\Api\V1\Player\TimeSlotsRequest;
use App\Http\Resources\Api\V1\PlayerClubDetailResource;
use App\Http\Resources\Api\V1\PlayerClubListResource;
use App\Http\Resources\Api\V1\PlayerCourtResource;
use App\Http\Resources\Api\V1\PlayerTimeSlotResource;
use App\Services\PlayerBookingService;
use Illuminate\Http\JsonResponse;

class PlayerClubController extends Controller
{
    public function __construct(private readonly PlayerBookingService $playerBookingService)
    {
    }

    public function index(IndexClubsRequest $request): JsonResponse
    {
        $result = $this->playerBookingService->clubs(
            $request->boolean('lowest_price'),
            $request->boolean('open_now'),
            $request->integer('page', 1),
            $request->integer('limit', 10)
        );

        return response()->json([
            'success' => true,
            'message' => 'Clubs fetched successfully',
            'data' => PlayerClubListResource::collection(collect($result['items'])),
            'pagination' => $result['pagination'],
        ]);
    }

    public function show(string $club_id): JsonResponse
    {
        $club = $this->playerBookingService->clubDetails((int) $club_id);

        return response()->json([
            'success' => true,
            'message' => 'Club details fetched successfully',
            'data' => new PlayerClubDetailResource($club),
        ]);
    }

    public function courts(ClubCourtsRequest $request, string $club_id): JsonResponse
    {
        $courts = $this->playerBookingService->clubCourts((int) $club_id, $request->string('date')->toString());

        return response()->json([
            'success' => true,
            'message' => 'Courts fetched successfully',
            'data' => PlayerCourtResource::collection(collect($courts)),
        ]);
    }

    public function timeSlots(TimeSlotsRequest $request, string $court_id): JsonResponse
    {
        $payload = $this->playerBookingService->timeSlots(
            (int) $request->integer('club_id'),
            (int) $court_id,
            $request->string('date')->toString()
        );

        return response()->json([
            'success' => true,
            'message' => 'Time slots fetched successfully',
            'data' => [
                'club_id' => $payload['club_id'],
                'court_id' => $payload['court_id'],
                'date' => $payload['date'],
                'slots' => PlayerTimeSlotResource::collection(collect($payload['slots'])),
            ],
        ]);
    }
}
