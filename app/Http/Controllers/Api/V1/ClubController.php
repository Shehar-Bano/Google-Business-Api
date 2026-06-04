<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\ClubDashboardResource;
use App\Http\Resources\Api\V1\ClubDetailResource;
use App\Http\Resources\Api\V1\ClubCourtsResource;
use App\Http\Resources\Api\V1\ClubBookingsResource;
use App\Http\Resources\Api\V1\ClubProfileCollection;
use App\Http\Resources\Api\V1\ClubTournamentsResource;
use App\Http\Resources\Api\V1\CourtDetailResource;
use App\Http\Resources\Api\V1\CourtResource;
use App\Http\Resources\Api\V1\BookingDetailResource;
use App\Http\Requests\Api\V1\Club\IndexCourtsRequest;
use App\Http\Requests\Api\V1\Club\IndexBookingsRequest;
use App\Http\Requests\Api\V1\Club\IndexTournamentsRequest;
use App\Http\Requests\Api\V1\Club\SetCourtMaintenanceRequest;
use App\Http\Requests\Api\V1\Club\UpdateClubLogoRequest;
use App\Http\Requests\Api\V1\Club\UpdateClubDetailsRequest;
use App\Http\Requests\Api\V1\Club\UpdateBookingStatusRequest;
use App\Http\Requests\Api\V1\Club\StoreTournamentRequest;
use App\Http\Requests\Api\V1\Club\StoreCourtRequest;
use App\Http\Requests\Api\V1\Club\UpdateTournamentRequest;
use App\Http\Requests\Api\V1\Club\UpdateCourtRequest;
use App\Http\Resources\Api\V1\TournamentDetailResource;
use App\Models\TournamentRegistration;
use App\Services\ClubService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ClubController extends Controller
{
    public function __construct(private readonly ClubService $clubService)
    {
    }

    public function profile(Request $request): JsonResponse
    {
        $club = $this->clubService->profile($request->user());

        return response()->json([
            'success' => true,
            'message' => 'Club profile fetched successfully.',
            'data' => new ClubProfileCollection(collect([$club])),
        ]);
    }

    public function updateClubDetails(UpdateClubDetailsRequest $request): JsonResponse
    {
        $club = $this->clubService->updateClubDetails($request->user(), $request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Club details updated successfully.',
            'data' => new ClubDetailResource($club),
        ]);
    }

    public function updateClubLogo(UpdateClubLogoRequest $request): JsonResponse
    {
        $club = $this->clubService->updateClubLogo($request->user(), $request->file('club_logo'));

        return response()->json([
            'success' => true,
            'message' => 'Club logo updated successfully.',
            'data' => new ClubDetailResource($club),
        ]);
    }

    public function dashboard(Request $request): JsonResponse
    {
        $metrics = $this->clubService->dashboard($request->user());

        return response()->json([
            'success' => true,
            'message' => 'Dashboard data fetched successfully.',
            'data' => new ClubDashboardResource($metrics),
        ]);
    }

    public function courts(IndexCourtsRequest $request): JsonResponse
    {
        $result = $this->clubService->courts(
            $request->user(),
            $request->input('status'),
            $request->filled('limit') ? (int) $request->input('limit') : null,
            (int) $request->input('page', 1)
        );

        return response()->json([
            'success' => true,
            'message' => 'Courts fetched successfully.',
            'data' => new ClubCourtsResource($result),
        ]);
    }

    public function storeCourt(StoreCourtRequest $request): JsonResponse
    {
        $court = $this->clubService->storeCourt($request->user(), $request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Court added successfully.',
            'data' => new CourtResource($court),
        ], 201);
    }

    public function courtDetail(Request $request, string $court_id): JsonResponse
    {
        $court = $this->clubService->courtDetail($request->user(), $court_id);

        return response()->json([
            'success' => true,
            'message' => 'Court detail fetched successfully.',
            'data' => new CourtDetailResource($court),
        ]);
    }

    public function editCourt(UpdateCourtRequest $request, string $court_id): JsonResponse
    {
        $court = $this->clubService->updateCourt($request->user(), $court_id, $request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Court updated successfully.',
            'data' => new CourtResource($court),
        ]);
    }

    public function setCourtMaintenance(SetCourtMaintenanceRequest $request, string $court_id): JsonResponse
    {
        $court = $this->clubService->setCourtMaintenance(
            $request->user(),
            $court_id,
            $request->input('reason')
        );

        return response()->json([
            'success' => true,
            'message' => 'Court moved to maintenance successfully.',
            'data' => [
                'id' => (string) $court->id,
                'status' => 'maintenance',
                'maintenance_note' => $court->maintenance_note,
            ],
        ]);
    }

    public function tournaments(IndexTournamentsRequest $request): JsonResponse
    {
        $result = $this->clubService->tournaments(
            $request->user(),
            $request->input('status'),
            $request->filled('limit') ? (int) $request->input('limit') : null,
            (int) $request->input('page', 1)
        );

        return response()->json([
            'success' => true,
            'message' => 'Tournaments fetched successfully.',
            'data' => new ClubTournamentsResource($result),
        ]);
    }

    public function tournamentDetail(Request $request, string $tournament_id): JsonResponse
    {
        $tournament = $this->clubService->tournamentDetail($request->user(), $tournament_id);

        return response()->json([
            'success' => true,
            'message' => 'Tournament detail fetched successfully.',
            'data' => new TournamentDetailResource($tournament),
        ]);
    }

    public function tournamentEnrolledUsers(Request $request, string $tournament_id): JsonResponse
    {
        $tournament = $this->clubService->tournamentDetail($request->user(), $tournament_id);

        $perPage = in_array((int) $request->integer('limit', 10), [10, 25, 50, 100], true)
            ? (int) $request->integer('limit', 10) : 10;
        $page = max(1, (int) $request->integer('page', 1));

        $registrations = TournamentRegistration::query()
            ->with('player')
            ->where('tournament_id', $tournament->id)
            ->where('registration_status', 'registered')
            ->latest()
            ->paginate($perPage, ['*'], 'page', $page);

        $enrolledUsers = collect($registrations->items())
            ->map(fn (TournamentRegistration $registration) => [
                'id' => $registration->player_id,
                'name' => $registration->player?->name,
                'email' => $registration->player?->email,
                'phone' => $registration->player?->phone,
                'profile_image' => $this->imageUrl($registration->player?->profile_image),
                'enrollment_status' => $registration->registration_status,
                'enrolled_at' => $registration->created_at?->format('Y-m-d H:i:s'),
            ])
            ->values();

        return response()->json([
            'success' => true,
            'message' => 'Tournament enrolled users fetched successfully.',
            'data' => [
                'tournament_id' => $tournament->id,
                'tournament_title' => $tournament->name,
                'total_enrolled_users' => $registrations->total(),
                'enrolled_users' => $enrolledUsers,
                'pagination' => [
                    'current_page' => $registrations->currentPage(),
                    'per_page' => $registrations->perPage(),
                    'total_records' => $registrations->total(),
                    'total_pages' => $registrations->lastPage(),
                ],
            ],
        ]);
    }

    public function storeTournament(StoreTournamentRequest $request): JsonResponse
    {
        $tournament = $this->clubService->storeTournament(
            $request->user(),
            $request->validated(),
            $request->file('tournament_image')
        );

        return response()->json([
            'success' => true,
            'message' => 'Tournament created successfully.',
            'data' => [
                'id' => $tournament->id,
                'status' => $tournament->status,
            ],
        ], 201);
    }

    public function updateTournament(UpdateTournamentRequest $request, string $tournament_id): JsonResponse
    {
        $tournament = $this->clubService->updateTournament(
            $request->user(),
            $tournament_id,
            $request->validated(),
            $request->file('tournament_image')
        );

        return response()->json([
            'success' => true,
            'message' => 'Tournament updated successfully.',
            'data' => [
                'id' => $tournament->id,
                'status' => $tournament->status,
            ],
        ]);
    }

    public function bookings(IndexBookingsRequest $request): JsonResponse
    {
        $result = $this->clubService->bookings(
            $request->user(),
            $request->input('status'),
            $request->input('date'),
            $request->filled('limit') ? (int) $request->input('limit') : null,
            (int) $request->input('page', 1)
        );

        return response()->json([
            'success' => true,
            'message' => 'Bookings fetched successfully.',
            'data' => new ClubBookingsResource($result),
        ]);
    }

    public function bookingDetail(Request $request, string $booking_id): JsonResponse
    {
        $booking = $this->clubService->bookingDetail($request->user(), $booking_id);

        return response()->json([
            'success' => true,
            'message' => 'Booking detail fetched successfully.',
            'data' => new BookingDetailResource($booking),
        ]);
    }

    public function updateBookingStatus(UpdateBookingStatusRequest $request, string $booking_id): JsonResponse
    {
        $booking = $this->clubService->updateBookingStatus(
            $request->user(),
            $booking_id,
            $request->input('status')
        );

        return response()->json([
            'success' => true,
            'message' => 'Booking status updated successfully.',
            'data' => [
                'booking_id' => $booking->id,
                'status' => $booking->booking_status,
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

        return asset('storage/' . $path);
    }
}
