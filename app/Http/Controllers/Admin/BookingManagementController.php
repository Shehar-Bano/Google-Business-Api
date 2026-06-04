<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Court;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class BookingManagementController extends Controller
{
    public function index(Request $request): View
    {
        $perPage = in_array((int) $request->integer('per_page', 10), [10, 25, 50, 100], true)
            ? (int) $request->integer('per_page', 10) : 10;

        $sort = in_array($request->string('sort', 'booking_date')->toString(), ['id', 'booking_date', 'start_time', 'end_time', 'booking_status', 'payment_status', 'total_amount', 'created_at'], true)
            ? $request->string('sort', 'booking_date')->toString() : 'booking_date';

        $direction = in_array($request->string('direction', 'desc')->toString(), ['asc', 'desc'], true)
            ? $request->string('direction', 'desc')->toString() : 'desc';

        $search = trim($request->string('search')->toString());
        $status = trim($request->string('status')->toString());
        $payment = trim($request->string('payment_status')->toString());
        $date = trim($request->string('date')->toString());
        $clubId = (int) $request->integer('club_id');
        $courtId = (int) $request->integer('court_id');

        $clubs = User::query()
            ->where('role', 'club')
            ->orderBy('club_name')
            ->get(['id', 'club_name', 'name']);

        $courts = Court::query()
            ->orderBy('name')
            ->get(['id', 'name']);

        $query = Booking::query()
            ->with([
                'club:id,club_name,name,city,club_logo',
                'court:id,name,type,club_id',
                'player:id,name,email,phone',
            ])
            ->when($search !== '', function (Builder $builder) use ($search) {
                $builder->where(function (Builder $sub) use ($search) {
                    $sub->where('id', 'like', "%{$search}%")
                        ->orWhereHas('player', function (Builder $playerQuery) use ($search) {
                            $playerQuery->where('name', 'like', "%{$search}%")
                                ->orWhere('phone', 'like', "%{$search}%")
                                ->orWhere('email', 'like', "%{$search}%");
                        })
                        ->orWhereHas('club', function (Builder $clubQuery) use ($search) {
                            $clubQuery->where('club_name', 'like', "%{$search}%")
                                ->orWhere('name', 'like', "%{$search}%");
                        })
                        ->orWhereHas('court', function (Builder $courtQuery) use ($search) {
                            $courtQuery->where('name', 'like', "%{$search}%");
                        });
                });
            })
            ->when($status !== '', fn (Builder $builder) => $builder->where('booking_status', $status))
            ->when($payment !== '', fn (Builder $builder) => $builder->where('payment_status', $payment))
            ->when($date !== '', fn (Builder $builder) => $builder->whereDate('booking_date', $date))
            ->when($clubId > 0, fn (Builder $builder) => $builder->where('club_id', $clubId))
            ->when($courtId > 0, fn (Builder $builder) => $builder->where('court_id', $courtId))
            ->orderBy($sort, $direction);

        $bookings = $query->paginate($perPage)->withQueryString();

        $stats = [
            'total_bookings' => Booking::count(),
            'pending_bookings' => Booking::where('booking_status', 'pending')->count(),
            'confirmed_bookings' => Booking::where('booking_status', 'confirmed')->count(),
            'cancelled_bookings' => Booking::where('booking_status', 'cancelled')->count(),
            'today_bookings' => Booking::whereDate('booking_date', now()->toDateString())->count(),
            'total_revenue' => Booking::where('payment_status', 'paid')->sum('total_amount'),
        ];

        return view('content.admin.bookings.index', compact(
            'bookings',
            'clubs',
            'courts',
            'search',
            'status',
            'payment',
            'date',
            'clubId',
            'courtId',
            'sort',
            'direction',
            'perPage',
            'stats'
        ));
    }

    public function show(Booking $booking): View
    {
        $booking->load([
            'club:id,club_name,name,email,phone,address,city,club_logo,working_hours',
            'court:id,name,type,price_per_hour,club_id,maintenance_note',
            'player:id,name,email,phone,city,profile_image',
            'slot:id,booking_date,start_time,end_time,status,price',
        ]);

        return view('content.admin.bookings.show', compact('booking'));
    }

    public function updateStatus(Request $request, Booking $booking): RedirectResponse
    {
        $validated = $request->validate([
            'booking_status' => ['required', 'in:pending,confirmed,cancelled,completed,failed'],
        ]);

        $booking->update(['booking_status' => $validated['booking_status']]);

        return redirect()->back()->with('success', 'Booking status updated successfully.');
    }
}
