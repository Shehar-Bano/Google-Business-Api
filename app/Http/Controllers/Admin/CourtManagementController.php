<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Court;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CourtManagementController extends Controller
{
    public function index(Request $request): View
    {
        $perPage = in_array((int) $request->integer('per_page', 10), [10, 25, 50, 100], true)
            ? (int) $request->integer('per_page', 10) : 10;

        $sort = in_array($request->string('sort', 'created_at')->toString(), ['name', 'type', 'price_per_hour', 'status', 'created_at'], true)
            ? $request->string('sort', 'created_at')->toString() : 'created_at';

        $direction = in_array($request->string('direction', 'desc')->toString(), ['asc', 'desc'], true)
            ? $request->string('direction', 'desc')->toString() : 'desc';

        $search = trim($request->string('search')->toString());
        $status = trim($request->string('status')->toString());
        $type = trim($request->string('type')->toString());
        $clubId = (int) $request->integer('club_id');

        $clubs = User::query()
            ->where('role', 'club')
            ->whereIn('status', ['active', 'pending', 'otp_pending'])
            ->orderBy('club_name')
            ->get(['id', 'club_name']);

        $courtsQuery = Court::query()
            ->with(['club:id,club_name,name,club_logo'])
            ->when($search !== '', function (Builder $query) use ($search) {
                $query->where(function (Builder $sub) use ($search) {
                    $sub->where('name', 'like', "%{$search}%")
                        ->orWhere('type', 'like', "%{$search}%")
                        ->orWhereHas('club', function (Builder $clubQuery) use ($search) {
                            $clubQuery->where('club_name', 'like', "%{$search}%")
                                ->orWhere('name', 'like', "%{$search}%");
                        });
                });
            })
            ->when($status !== '', fn (Builder $query) => $query->where('status', $status))
            ->when($type !== '', fn (Builder $query) => $query->where('type', $type))
            ->when($clubId > 0, fn (Builder $query) => $query->where('club_id', $clubId))
            ->orderBy($sort, $direction);

        $courts = $courtsQuery->paginate($perPage)->withQueryString();

        $stats = [
            'total_courts' => Court::count(),
            'active_courts' => Court::where('status', 'active')->count(),
            'maintenance_courts' => Court::where('status', 'maintenance')->count(),
            'clubs_with_courts' => Court::distinct('club_id')->count('club_id'),
        ];

        return view('content.admin.courts.index', compact(
            'courts',
            'clubs',
            'search',
            'status',
            'type',
            'clubId',
            'sort',
            'direction',
            'perPage',
            'stats'
        ));
    }

    public function create(): View
    {
        $clubs = User::query()
            ->where('role', 'club')
            ->whereIn('status', ['active', 'pending', 'otp_pending'])
            ->orderBy('club_name')
            ->get(['id', 'club_name']);

        return view('content.admin.courts.create', compact('clubs'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'club_id' => ['required', 'exists:users,id'],
            'name' => ['required', 'string', 'max:255'],
            'type' => ['required', 'in:glass,wooden,synthetic,other'],
            'price_per_hour' => ['required', 'numeric', 'min:0'],
            'capacity' => ['nullable', 'integer', 'min:1'],
            'status' => ['required', 'in:active,inactive,maintenance'],
            'description' => ['nullable', 'string', 'max:1000'],
            'maintenance_note' => ['nullable', 'string', 'max:1000'],
        ]);

        Court::create($validated);

        return redirect()->route('admin.courts.index')->with('success', 'Court created successfully.');
    }

    public function show(Court $court): View
    {
        $court->load(['club:id,club_name,name,email,phone,city,club_logo']);

        $today = now()->toDateString();

        $metrics = [
            'today_bookings' => $court->bookings()->whereDate('booking_date', $today)->count(),
            'upcoming_bookings' => $court->bookings()->whereDate('booking_date', '>', $today)->count(),
            'total_bookings' => $court->bookings()->count(),
        ];

        $latestBookings = $court->bookings()
            ->with(['player:id,name,phone'])
            ->latest()
            ->take(5)
            ->get();

        return view('content.admin.courts.show', compact('court', 'metrics', 'latestBookings'));
    }

    public function edit(Court $court): View
    {
        $court->load('club:id,club_name');

        $clubs = User::query()
            ->where('role', 'club')
            ->whereIn('status', ['active', 'pending', 'otp_pending'])
            ->orderBy('club_name')
            ->get(['id', 'club_name']);

        return view('content.admin.courts.edit', compact('court', 'clubs'));
    }

    public function update(Request $request, Court $court): RedirectResponse
    {
        $validated = $request->validate([
            'club_id' => ['required', 'exists:users,id'],
            'name' => ['required', 'string', 'max:255'],
            'type' => ['required', 'in:glass,wooden,synthetic,other'],
            'price_per_hour' => ['required', 'numeric', 'min:0'],
            'capacity' => ['nullable', 'integer', 'min:1'],
            'status' => ['required', 'in:active,inactive,maintenance'],
            'description' => ['nullable', 'string', 'max:1000'],
            'maintenance_note' => ['nullable', 'string', 'max:1000'],
        ]);

        $court->update($validated);

        return redirect()->route('admin.courts.index')->with('success', 'Court updated successfully.');
    }

    public function destroy(Court $court): RedirectResponse
    {
        $court->delete();

        return redirect()->route('admin.courts.index')->with('success', 'Court deleted successfully.');
    }
}
