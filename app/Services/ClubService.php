<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\Court;
use App\Models\Tournament;
use App\Models\User;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Schema;

class ClubService
{
    public function profile(User $club): User
    {
        return $club->loadCount('courts');
    }

    public function updateClubDetails(User $club, array $data): User
    {
        return DB::transaction(function () use ($club, $data) {
            if (array_key_exists('name', $data)) {
                $club->name = $data['name'];
                $club->club_name = $data['name'];
            }

            if (array_key_exists('address', $data)) {
                $club->address = $data['address'];
            }

            if (array_key_exists('working_hours', $data)) {
                $club->working_hours = $data['working_hours'];
            }

            if (array_key_exists('facilities', $data)) {
                $club->facilities = $this->normalizeFacilities($data['facilities']);
            }

            $club->number_of_courts = $club->courts()->count();
            $club->save();

            return $club->refresh();
        });
    }

    public function updateClubLogo(User $club, UploadedFile $logoFile): User
    {
        return DB::transaction(function () use ($club, $logoFile) {
            $this->deleteStoredClubLogo($club->club_logo);
            $club->club_logo = $this->storeUploadedClubLogo($logoFile);
            $club->save();

            return $club->refresh();
        });
    }

    public function dashboard(User $club): array
    {
        $totalCourts = (int) ($club->number_of_courts ?: $club->courts()->count());
        $availableCourts = (int) $club->courts()->where('status', 'active')->count();
        $maintenanceCourts = (int) $club->courts()->whereIn('status', ['maintenance', 'inactive'])->count();
        $courtBooking = $this->countClubBookings($club->id);
        $todayBooking = $this->countClubBookings($club->id, true);
        $pendingBookings = $this->countClubBookings($club->id, false, 'pending');
        $activeTournaments = $this->countClubTournaments($club->id);

        return [
            'court_utilization' => $availableCourts.'/'.$totalCourts,
            'court_booking' => $courtBooking,
            'today_booking' => $todayBooking,
            'pending_bookings' => $pendingBookings,
            'total_courts' => $totalCourts,
            'available_courts' => $availableCourts,
            'maintenance_courts' => $maintenanceCourts,
            'active_tournaments' => $activeTournaments,
        ];
    }

    public function courts(User $club, ?string $status = null, ?int $limit = null, int $page = 1): array
    {
        $allCourts = $club->courts()->orderBy('id')->get();

        $query = $club->courts()->orderBy('id');
        if ($status === 'available') {
            $query->where('status', 'active');
        } elseif ($status === 'maintenance') {
            $query->where('status', 'maintenance');
        }

        $courts = $limit
            ? collect($query->paginate($limit, ['*'], 'page', $page)->items())
            : $query->get();

        return [
            'counts' => [
                'total_courts' => $allCourts->count(),
                'available_courts' => $allCourts->where('status', 'active')->count(),
                'maintenance_courts' => $allCourts->whereIn('status', ['maintenance', 'inactive'])->count(),
            ],
            'courts' => $courts,
        ];
    }

    public function courtDetail(User $club, string $courtId): Court
    {
        return $this->findClubCourt($club, $courtId);
    }

    public function storeCourt(User $club, array $data): Court
    {
        return DB::transaction(function () use ($club, $data) {
            $court = $club->courts()->create([
                'name' => $data['name'],
                'type' => $data['type'],
                'price_per_hour' => $data['price_per_hour'],
                'status' => $this->mapInputStatusToStorage($data['status']),
                'maintenance_note' => $data['maintenance_note'] ?? null,
            ]);

            return $court->refresh();
        });
    }

    public function updateCourt(User $club, string $courtId, array $data): Court
    {
        return DB::transaction(function () use ($club, $courtId, $data) {
            $court = $this->findClubCourt($club, $courtId);

            if (array_key_exists('name', $data)) {
                $court->name = $data['name'];
            }

            if (array_key_exists('type', $data)) {
                $court->type = $data['type'];
            }

            if (array_key_exists('price_per_hour', $data)) {
                $court->price_per_hour = $data['price_per_hour'];
            }

            if (array_key_exists('status', $data)) {
                $court->status = $this->mapInputStatusToStorage($data['status']);
            }

            if (array_key_exists('maintenance_note', $data)) {
                $court->maintenance_note = $data['maintenance_note'];
            }

            $court->save();

            return $court->refresh();
        });
    }

    public function setCourtMaintenance(User $club, string $courtId, ?string $reason = null): Court
    {
        return DB::transaction(function () use ($club, $courtId, $reason) {
            $court = $this->findClubCourt($club, $courtId);
            $court->status = 'maintenance';
            $court->maintenance_note = $reason;
            $court->save();

            return $court->refresh();
        });
    }

    public function tournaments(User $club, ?string $status = null, ?int $limit = null, int $page = 1): array
    {
        $allTournaments = $club->tournaments()->orderBy('id')->get();

        $query = $club->tournaments()->orderBy('id');
        if ($status && in_array($status, ['open', 'full', 'closed', 'completed', 'cancelled'], true)) {
            $query->where('status', $status);
        }

        $tournaments = $limit
            ? collect($query->paginate($limit, ['*'], 'page', $page)->items())
            : $query->get();

        return [
            'counts' => [
                'open_tournament_count' => $allTournaments->where('status', 'open')->count(),
                'full_tournament_count' => $allTournaments->where('status', 'full')->count(),
                'total_registered_players' => (int) $allTournaments->sum('registered_players_count'),
            ],
            'tournaments' => $tournaments,
        ];
    }

    public function bookings(User $club, ?string $status = null, ?string $date = null, ?int $limit = null, int $page = 1): array
    {
        $allBookings = $club->bookingsAsClub()
            ->with(['player:id,name', 'court:id,name'])
            ->orderByDesc('id')
            ->get();

        $query = $club->bookingsAsClub()
            ->with(['player:id,name', 'court:id,name'])
            ->orderByDesc('id');

        if ($status && in_array($status, ['pending', 'confirmed', 'cancelled'], true)) {
            $query->where('booking_status', $status);
        }

        if ($date) {
            $query->whereDate('booking_date', $date);
        }

        $bookings = $limit
            ? collect($query->paginate($limit, ['*'], 'page', $page)->items())
            : $query->get();

        return [
            'counts' => [
                'pending_bookings' => $allBookings->where('booking_status', 'pending')->count(),
                'confirmed_bookings' => $allBookings->where('booking_status', 'confirmed')->count(),
                'cancelled_bookings' => $allBookings->where('booking_status', 'cancelled')->count(),
            ],
            'bookings' => $bookings,
        ];
    }

    public function bookingDetail(User $club, string $bookingId): Booking
    {
        return $this->findClubBooking($club, $bookingId);
    }

    public function updateBookingStatus(User $club, string $bookingId, string $status): Booking
    {
        return DB::transaction(function () use ($club, $bookingId, $status) {
            $booking = $this->findClubBooking($club, $bookingId);
            $booking->booking_status = $status;
            $booking->save();

            return $booking->refresh()->load(['player', 'court']);
        });
    }

    public function tournamentDetail(User $club, string $tournamentId): Tournament
    {
        return $this->findClubTournament($club, $tournamentId);
    }

    public function storeTournament(User $club, array $data, ?UploadedFile $imageFile = null): Tournament
    {
        return DB::transaction(function () use ($club, $data, $imageFile) {
            $tournament = $club->tournaments()->create([
                'name' => $data['name'],
                'format' => $data['format'],
                'tournament_image' => $this->storeTournamentImage($imageFile, $data['tournament_image'] ?? null),
                'start_date' => $data['start_date'],
                'end_date' => $data['end_date'],
                'registration_deadline' => $data['registration_deadline'],
                'entry_fees' => $data['entry_fees'],
                'prize_pool' => $data['prize_pool'],
                'allowed_player' => $data['allowed_player'],
                'registered_players_count' => 0,
                'status' => 'open',
                'rules' => $data['rules'] ?? null,
            ]);

            return $tournament->refresh();
        });
    }

    public function updateTournament(User $club, string $tournamentId, array $data, ?UploadedFile $imageFile = null): Tournament
    {
        return DB::transaction(function () use ($club, $tournamentId, $data, $imageFile) {
            $tournament = $this->findClubTournament($club, $tournamentId);

            if (array_key_exists('name', $data)) {
                $tournament->name = $data['name'];
            }

            if (array_key_exists('format', $data)) {
                $tournament->format = $data['format'];
            }

            if (array_key_exists('start_date', $data)) {
                $tournament->start_date = $data['start_date'];
            }

            if (array_key_exists('registration_deadline', $data)) {
                $tournament->registration_deadline = $data['registration_deadline'];
            }

            if (array_key_exists('end_date', $data)) {
                $tournament->end_date = $data['end_date'];
            }

            if (array_key_exists('entry_fees', $data)) {
                $tournament->entry_fees = $data['entry_fees'];
            }

            if (array_key_exists('prize_pool', $data)) {
                $tournament->prize_pool = $data['prize_pool'];
            }

            if (array_key_exists('allowed_player', $data)) {
                $tournament->allowed_player = $data['allowed_player'];
            }

            if (array_key_exists('rules', $data)) {
                $tournament->rules = $data['rules'];
            }

            if ($imageFile) {
                $this->deleteStoredTournamentImage($tournament->tournament_image);
                $tournament->tournament_image = $this->storeUploadedTournamentImage($imageFile);
            } elseif (array_key_exists('tournament_image', $data) && is_string($data['tournament_image'])) {
                $tournament->tournament_image = $data['tournament_image'];
            }

            $tournament->save();

            return $tournament->refresh();
        });
    }

    private function findClubCourt(User $club, string $courtId): Court
    {
        if (! ctype_digit($courtId)) {
            throw (new ModelNotFoundException())->setModel(Court::class, [$courtId]);
        }

        $court = $club->courts()->whereKey((int) $courtId)->first();

        if (! $court) {
            throw (new ModelNotFoundException())->setModel(Court::class, [$courtId]);
        }

        return $court;
    }

    private function findClubTournament(User $club, string $tournamentId): Tournament
    {
        if (! ctype_digit($tournamentId)) {
            throw (new ModelNotFoundException())->setModel(Tournament::class, [$tournamentId]);
        }

        $tournament = $club->tournaments()->whereKey((int) $tournamentId)->first();

        if (! $tournament) {
            throw (new ModelNotFoundException())->setModel(Tournament::class, [$tournamentId]);
        }

        return $tournament;
    }

    private function findClubBooking(User $club, string $bookingId): Booking
    {
        if (! ctype_digit($bookingId)) {
            throw (new ModelNotFoundException())->setModel(Booking::class, [$bookingId]);
        }

        $booking = $club->bookingsAsClub()
            ->with(['player', 'court'])
            ->whereKey((int) $bookingId)
            ->first();

        if (! $booking) {
            throw (new ModelNotFoundException())->setModel(Booking::class, [$bookingId]);
        }

        return $booking;
    }

    private function storeTournamentImage(?UploadedFile $file, mixed $existingValue): ?string
    {
        if ($file) {
            return $this->storeUploadedTournamentImage($file);
        }

        if (is_string($existingValue) && $existingValue !== '') {
            return $existingValue;
        }

        return null;
    }

    private function storeUploadedTournamentImage(UploadedFile $file): string
    {
        return $file->store('tournament-images', 'public');
    }

    private function storeUploadedClubLogo(UploadedFile $file): string
    {
        return $file->store('club-logos', 'public');
    }

    private function deleteStoredClubLogo(?string $value): void
    {
        if (! $value) {
            return;
        }

        if (str_starts_with($value, 'http://') || str_starts_with($value, 'https://')) {
            return;
        }

        Storage::disk('public')->delete($value);
    }

    private function deleteStoredTournamentImage(?string $value): void
    {
        if (! $value) {
            return;
        }

        if (str_starts_with($value, 'http://') || str_starts_with($value, 'https://')) {
            return;
        }

        Storage::disk('public')->delete($value);
    }

    private function mapInputStatusToStorage(string $status): string
    {
        return $status === 'maintenance' ? 'maintenance' : 'active';
    }

    private function normalizeFacilities(mixed $facilities): array
    {
        if (is_array($facilities)) {
            return array_values(array_filter($facilities, fn ($item) => is_string($item) && trim($item) !== ''));
        }

        if (is_string($facilities)) {
            $decoded = json_decode($facilities, true);

            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                return $this->normalizeFacilities($decoded);
            }

            return array_values(array_filter(array_map('trim', explode(',', $facilities))));
        }

        return [];
    }

    private function countClubBookings(int $clubId, bool $todayOnly = false, ?string $status = null): int
    {
        $query = DB::table('bookings')->where('club_id', $clubId);

        if ($todayOnly && Schema::hasColumn('bookings', 'booking_date')) {
            $query->whereDate('booking_date', now()->toDateString());
        }

        if ($status !== null && Schema::hasColumn('bookings', 'booking_status')) {
            $query->where('booking_status', $status);
        }

        return (int) $query->count();
    }

    private function countClubTournaments(int $clubId): int
    {
        if (! Schema::hasTable('tournaments')) {
            return 0;
        }

        $query = DB::table('tournaments')->where('club_id', $clubId);

        if (Schema::hasColumn('tournaments', 'status')) {
            $query->where('status', 'open');
        }

        return (int) $query->count();
    }
}
