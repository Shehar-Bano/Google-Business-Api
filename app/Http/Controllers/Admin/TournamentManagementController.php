<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Tournament;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TournamentManagementController extends Controller
{
    public function index(Request $request): View
    {
        $perPage = in_array((int) $request->integer('per_page', 10), [10, 25, 50, 100], true)
            ? (int) $request->integer('per_page', 10) : 10;

        $sort = in_array($request->string('sort', 'start_date')->toString(), ['id', 'name', 'start_date', 'registration_deadline', 'entry_fees', 'prize_pool', 'status', 'created_at'], true)
            ? $request->string('sort', 'start_date')->toString() : 'start_date';

        $direction = in_array($request->string('direction', 'desc')->toString(), ['asc', 'desc'], true)
            ? $request->string('direction', 'desc')->toString() : 'desc';

        $search = trim($request->string('search')->toString());
        $status = trim($request->string('status')->toString());
        $format = trim($request->string('format')->toString());
        $startDate = trim($request->string('start_date')->toString());
        $clubId = (int) $request->integer('club_id');

        $clubs = User::query()
            ->where('role', 'club')
            ->orderBy('club_name')
            ->get(['id', 'club_name', 'name']);

        $query = Tournament::query()
            ->with(['club:id,club_name,name,city,club_logo'])
            ->when($search !== '', function (Builder $builder) use ($search) {
                $builder->where(function (Builder $sub) use ($search) {
                    $sub->where('id', 'like', "%{$search}%")
                        ->orWhere('name', 'like', "%{$search}%")
                        ->orWhere('format', 'like', "%{$search}%")
                        ->orWhereHas('club', function (Builder $clubQuery) use ($search) {
                            $clubQuery->where('club_name', 'like', "%{$search}%")
                                ->orWhere('name', 'like', "%{$search}%");
                        });
                });
            })
            ->when($status !== '', fn (Builder $builder) => $builder->where('status', $status))
            ->when($format !== '', fn (Builder $builder) => $builder->where('format', $format))
            ->when($startDate !== '', fn (Builder $builder) => $builder->whereDate('start_date', $startDate))
            ->when($clubId > 0, fn (Builder $builder) => $builder->where('club_id', $clubId))
            ->orderBy($sort, $direction);

        $tournaments = $query->paginate($perPage)->withQueryString();

        $stats = [
            'total_tournaments' => Tournament::count(),
            'open_tournaments' => Tournament::where('status', 'open')->count(),
            'full_tournaments' => Tournament::where('status', 'full')->count(),
            'completed_tournaments' => Tournament::where('status', 'completed')->count(),
            'cancelled_tournaments' => Tournament::where('status', 'cancelled')->count(),
            'total_registered_players' => (int) Tournament::sum('registered_players_count'),
            'total_prize_pool' => (float) Tournament::sum('prize_pool'),
        ];

        return view('content.admin.tournaments.index', compact(
            'tournaments',
            'clubs',
            'search',
            'status',
            'format',
            'startDate',
            'clubId',
            'sort',
            'direction',
            'perPage',
            'stats'
        ));
    }

    public function show(Tournament $tournament): View
    {
        $tournament->load(['club:id,club_name,name,email,phone,address,city,club_logo,working_hours']);

        return view('content.admin.tournaments.show', compact('tournament'));
    }

    public function updateStatus(Request $request, Tournament $tournament): RedirectResponse
    {
        $validated = $request->validate([
            'status' => ['required', 'in:open,full,closed,completed,cancelled'],
        ]);

        $tournament->update(['status' => $validated['status']]);

        return redirect()->back()->with('success', 'Tournament status updated successfully.');
    }
}
