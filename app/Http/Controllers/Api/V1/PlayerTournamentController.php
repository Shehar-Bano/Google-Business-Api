<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Player\IndexTournamentsRequest;
use App\Http\Requests\Api\V1\Player\RegisterTournamentRequest;
use App\Http\Resources\Api\V1\PlayerTournamentDetailResource;
use App\Http\Resources\Api\V1\PlayerTournamentListResource;
use App\Services\PlayerTournamentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PlayerTournamentController extends Controller
{
    public function __construct(private readonly PlayerTournamentService $playerTournamentService)
    {
    }

    public function index(IndexTournamentsRequest $request): JsonResponse
    {
        $filter = $request->string('filter')->toString() ?: $request->string('status')->toString();

        $payload = $this->playerTournamentService->tournaments(
            $filter ?: null,
            (int) $request->integer('page', 1),
            (int) $request->integer('limit', 10)
        );

        return response()->json([
            'success' => true,
            'message' => 'Tournaments fetched successfully',
            'data' => PlayerTournamentListResource::collection(collect($payload['items'])),
            'pagination' => $payload['pagination'],
        ]);
    }

    public function show(Request $request, string $tournament_id): JsonResponse
    {
        $tournament = $this->playerTournamentService->detail((int) $tournament_id);

        return response()->json([
            'success' => true,
            'message' => 'Tournament detail fetched successfully',
            'data' => new PlayerTournamentDetailResource($tournament),
        ]);
    }

    public function paymentMethods(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => 'Payment methods fetched successfully',
            'data' => $this->playerTournamentService->paymentMethods(),
        ]);
    }

    public function register(RegisterTournamentRequest $request): JsonResponse
    {
        $registration = $this->playerTournamentService->register(
            $request->user(),
            (int) $request->integer('tournament_id'),
            $request->string('payment_method_id')->toString()
        );

        return response()->json([
            'success' => true,
            'message' => 'Tournament registered successfully',
            'data' => [
                'registration_id' => $registration->id,
                'registration_status' => $registration->registration_status,
                'payment_status' => $registration->payment_status,
                'tournament_id' => $registration->tournament_id,
            ],
        ], 201);
    }
}
