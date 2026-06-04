<?php

namespace App\Services;

use App\Models\Tournament;
use App\Models\TournamentRegistration;
use App\Models\User;
use App\Support\ApiErrorCode;
use Carbon\Carbon;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class PlayerTournamentService
{
    public function tournaments(?string $filter = null, int $page = 1, int $limit = 10): array
    {
        $today = Carbon::today('Asia/Karachi')->toDateString();

        $query = Tournament::query()->with('club');

        match ($filter) {
            'upcoming' => $query
                ->whereDate('start_date', '>', $today)
                ->whereIn('status', ['open', 'full']),
            'ongoing' => $query
                ->whereDate('start_date', '<=', $today)
                ->whereDate('end_date', '>=', $today)
                ->whereNotIn('status', ['completed', 'cancelled']),
            'completed' => $query
                ->where(function ($query) use ($today) {
                    $query->where('status', 'completed')
                        ->orWhereDate('end_date', '<', $today);
                }),
            default => null,
        };

        $tournaments = $query
            ->orderBy('start_date')
            ->paginate($limit, ['*'], 'page', $page);

        return [
            'items' => $tournaments->items(),
            'pagination' => $this->pagination($tournaments, $limit),
        ];
    }

    public function detail(int $tournamentId): Tournament
    {
        $tournament = Tournament::query()
            ->with('club')
            ->whereKey($tournamentId)
            ->first();

        if (! $tournament) {
            $this->apiError('Tournament does not exist.', ApiErrorCode::RECORD_NOT_FOUND, 404);
        }

        return $tournament;
    }

    public function paymentMethods(): array
    {
        return [
            ['id' => 'card', 'name' => 'Debit/Credit Card'],
            ['id' => 'wallet', 'name' => 'App Wallet'],
            ['id' => 'cash', 'name' => 'Cash'],
            ['id' => 'jazzcash', 'name' => 'JazzCash'],
            ['id' => 'easypaisa', 'name' => 'Easypaisa'],
        ];
    }

    public function register(User $player, int $tournamentId, string $paymentMethodId): TournamentRegistration
    {
        return DB::transaction(function () use ($player, $tournamentId, $paymentMethodId) {
            $tournament = Tournament::query()
                ->whereKey($tournamentId)
                ->lockForUpdate()
                ->first();

            if (! $tournament) {
                $this->apiError('Tournament does not exist.', ApiErrorCode::RECORD_NOT_FOUND, 404);
            }

            if (! $this->canAcceptRegistration($tournament)) {
                $this->apiError('Only upcoming open tournaments can accept registrations.', ApiErrorCode::VALIDATION_ERROR);
            }

            $alreadyRegistered = TournamentRegistration::query()
                ->where('tournament_id', $tournament->id)
                ->where('player_id', $player->id)
                ->where('registration_status', 'registered')
                ->exists();

            if ($alreadyRegistered) {
                $this->apiError('Player is already registered in this tournament.', ApiErrorCode::DUPLICATE_RESOURCE);
            }

            if ((int) $tournament->registered_players_count >= (int) $tournament->allowed_player) {
                $tournament->status = 'full';
                $tournament->save();

                $this->apiError('Tournament is full.', ApiErrorCode::VALIDATION_ERROR);
            }

            $registration = TournamentRegistration::create([
                'tournament_id' => $tournament->id,
                'player_id' => $player->id,
                'payment_method_id' => $paymentMethodId,
                'payment_status' => 'paid',
                'registration_status' => 'registered',
                'amount' => $tournament->entry_fees,
                'currency' => 'PKR',
            ]);

            $tournament->registered_players_count = ((int) $tournament->registered_players_count) + 1;
            if ((int) $tournament->registered_players_count >= (int) $tournament->allowed_player) {
                $tournament->status = 'full';
            }
            $tournament->save();

            return $registration->load(['tournament']);
        });
    }

    private function canAcceptRegistration(Tournament $tournament): bool
    {
        return $tournament->status === 'open'
            && $tournament->start_date?->toDateString() > Carbon::today('Asia/Karachi')->toDateString();
    }

    private function pagination(LengthAwarePaginator $paginator, int $limit): array
    {
        return [
            'current_page' => $paginator->currentPage(),
            'limit' => $limit,
            'total_records' => $paginator->total(),
            'total_pages' => $paginator->lastPage(),
        ];
    }

    private function apiError(string $message, string $code, int $status = 422): never
    {
        throw new HttpResponseException(response()->json([
            'status' => false,
            'message' => $message,
            'error' => [
                'code' => $code,
            ],
        ], $status));
    }
}
