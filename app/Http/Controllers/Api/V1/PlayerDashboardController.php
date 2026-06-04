<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Tournament;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class PlayerDashboardController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $today = Carbon::today('Asia/Karachi')->toDateString();
        $now = Carbon::now('Asia/Karachi')->format('H:i:s');
        $user = $request->user();

        $clubs = User::query()
            ->where('role', 'club')
            ->where('status', 'active')
            ->withMin(['courts as lowest_court_price' => fn ($query) => $query->where('status', 'active')], 'price_per_hour')
            ->withAvg('bookingReviewsAsClub as rating', 'rating')
            ->orderBy('id')
            ->limit(4)
            ->get()
            ->map(fn (User $club) => [
                'id' => $club->id,
                'name' => $club->club_name ?? $club->name,
                'image' => $this->imageUrl($club->club_logo),
                'rating' => round((float) ($club->rating ?? 0), 1),
                'lowest_court_price' => $this->normalizeNumber($club->lowest_court_price ?? 0),
            ])
            ->values();

        $upcomingBooking = Booking::query()
            ->with(['club', 'court'])
            ->when($user?->role === 'player', function ($query) use ($user, $today, $now) {
                $query->where('player_id', $user->id)
                    ->whereIn('booking_status', ['pending', 'confirmed'])
                    ->where(function ($query) use ($today, $now) {
                        $query->whereDate('booking_date', '>', $today)
                            ->orWhere(function ($query) use ($today, $now) {
                                $query->whereDate('booking_date', $today)
                                    ->where('start_time', '>', $now);
                            });
                    });
            })
            ->orderBy('booking_date')
            ->orderBy('start_time')
            ->first();

        $activeTournaments = Tournament::query()
            ->where('status', 'open')
            ->whereDate('registration_deadline', '>=', $today)
            ->whereDate('start_date', '>=', $today)
            ->orderBy('start_date')
            ->limit(2)
            ->get()
            ->map(fn (Tournament $tournament) => [
                'id' => $tournament->id,
                'title' => $tournament->name,
                'image' => $this->imageUrl($tournament->tournament_image),
                'start_date' => $tournament->start_date?->toDateString(),
                'registration_status' => $tournament->status,
            ])
            ->values();

        return response()->json([
            'success' => true,
            'message' => 'Dashboard data fetched successfully.',
            'data' => [
                'clubs' => $clubs,
                'upcoming_booking' => $upcomingBooking ? [
                    'id' => $upcomingBooking->id,
                    'club_name' => $upcomingBooking->club?->club_name ?? $upcomingBooking->club?->name,
                    'court_name' => $upcomingBooking->court?->name,
                    'booking_date' => $upcomingBooking->booking_date?->toDateString(),
                    'start_time' => substr((string) $upcomingBooking->start_time, 0, 5),
                    'end_time' => substr((string) $upcomingBooking->end_time, 0, 5),
                    'status' => $upcomingBooking->booking_status,
                ] : null,
                'active_tournaments' => $activeTournaments,
            ],
        ]);
    }

    private function imageUrl(?string $path): ?string
    {
        if (! $path) {
            return null;
        }

        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            return $path;
        }

        return Storage::disk('public')->url($path);
    }

    private function normalizeNumber(mixed $value): int|float
    {
        $numeric = (float) ($value ?? 0);

        return $numeric == (int) $numeric ? (int) $numeric : $numeric;
    }
}
