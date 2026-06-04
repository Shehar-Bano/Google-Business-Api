<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Tournament;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(Request $request): View
    {
        $period = $this->normalizePeriod((int) $request->integer('period', 30));

        $dashboardStats = $this->dashboardStats();
        $chartData = $this->chartData($period);

        return view('content.admin.dashboard.index', compact('dashboardStats', 'chartData', 'period'));
    }

    private function dashboardStats(): array
    {
        $totalUsers = User::count();
        $activeUsers = User::where('status', 'active')->count();
        $inactiveUsers = User::where('status', '!=', 'active')->count();

        $totalClubs = User::where('role', 'club')->count();
        $activeClubs = User::where('role', 'club')->where('status', 'active')->count();
        $pendingApprovals = User::where('role', 'club')->whereIn('status', ['otp_pending', 'pending'])->count();

        $totalPlayers = User::where('role', 'player')->count();
        $activePlayers = User::where('role', 'player')->where('status', 'active')->count();

        $bookingsToday = Booking::whereDate('booking_date', Carbon::today()->toDateString())->count();
        $activeBookings = Booking::whereIn('booking_status', ['pending', 'confirmed'])->count();
        $cancelledBookings = Booking::where('booking_status', 'cancelled')->count();

        $totalTournaments = Tournament::count();
        $activeTournaments = Tournament::where('status', 'open')->count();

        $totalRevenue = Booking::where('payment_status', 'paid')->sum('total_amount');
        $pendingPayments = Booking::where('payment_status', 'pending')->sum('total_amount');
        $refundRequests = Booking::where('payment_status', 'refunded')->count();

        return [
            [
                'title' => 'Total Users',
                'value' => $this->formatCount($totalUsers),
                'icon' => 'mdi mdi-account-multiple',
                'iconClass' => 'icon-primary',
            ],
            [
                'title' => 'Active Users',
                'value' => $this->formatCount($activeUsers),
                'icon' => 'mdi mdi-account-check',
                'iconClass' => 'icon-success',
            ],
            [
                'title' => 'Inactive Users',
                'value' => $this->formatCount($inactiveUsers),
                'icon' => 'mdi mdi-account-off',
                'iconClass' => 'icon-danger',
            ],
            [
                'title' => 'Total Clubs',
                'value' => $this->formatCount($totalClubs),
                'icon' => 'mdi mdi-domain',
                'iconClass' => 'icon-info',
                'link' => route('admin.clubs.index'),
            ],
            [
                'title' => 'Active Clubs',
                'value' => $this->formatCount($activeClubs),
                'icon' => 'mdi mdi-check-circle',
                'iconClass' => 'icon-success',
                'link' => route('admin.clubs.index'),
            ],
            [
                'title' => 'Pending Approvals',
                'value' => $this->formatCount($pendingApprovals),
                'icon' => 'mdi mdi-clock-outline',
                'iconClass' => 'icon-warning',
                'link' => route('admin.clubs.index'),
            ],
            [
                'title' => 'Total Players',
                'value' => $this->formatCount($totalPlayers),
                'icon' => 'mdi mdi-account-group',
                'iconClass' => 'icon-purple',
                'link' => route('admin.players.index'),
            ],
            [
                'title' => 'Active Players',
                'value' => $this->formatCount($activePlayers),
                'icon' => 'mdi mdi-account-check',
                'iconClass' => 'icon-success',
                'link' => route('admin.players.index'),
            ],
            [
                'title' => 'Bookings Today',
                'value' => $this->formatCount($bookingsToday),
                'icon' => 'mdi mdi-calendar-today',
                'iconClass' => 'icon-accent',
                'link' => route('admin.bookings.index'),
            ],
            [
                'title' => 'Active Bookings',
                'value' => $this->formatCount($activeBookings),
                'icon' => 'mdi mdi-calendar-check',
                'iconClass' => 'icon-success',
                'link' => route('admin.bookings.index'),
            ],
            [
                'title' => 'Cancelled Bookings',
                'value' => $this->formatCount($cancelledBookings),
                'icon' => 'mdi mdi-calendar-remove',
                'iconClass' => 'icon-danger',
                'link' => route('admin.bookings.index'),
            ],
            [
                'title' => 'Total Tournaments',
                'value' => $this->formatCount($totalTournaments),
                'icon' => 'mdi mdi-trophy',
                'iconClass' => 'icon-warning',
                'link' => route('admin.tournaments.index'),
            ],
            [
                'title' => 'Active Tournaments',
                'value' => $this->formatCount($activeTournaments),
                'icon' => 'mdi mdi-trophy-variant',
                'iconClass' => 'icon-accent',
                'link' => route('admin.tournaments.index'),
            ],
            [
                'title' => 'Total Revenue',
                'value' => $this->formatMoney($totalRevenue),
                'icon' => 'mdi mdi-currency-usd',
                'iconClass' => 'icon-success',
            ],
            [
                'title' => 'Pending Payments',
                'value' => $this->formatMoney($pendingPayments),
                'icon' => 'mdi mdi-clock-alert-outline',
                'iconClass' => 'icon-warning',
            ],
            [
                'title' => 'Refund Requests',
                'value' => $this->formatCount($refundRequests),
                'icon' => 'mdi mdi-cash-refund',
                'iconClass' => 'icon-danger',
            ],
        ];
    }

    private function chartData(int $period): array
    {
        return $period === 365
            ? $this->buildMonthlyChartData()
            : $this->buildDailyChartData($period);
    }

    private function buildDailyChartData(int $days): array
    {
        $start = Carbon::today()->subDays($days - 1);
        $end = Carbon::now();

        $dates = collect(range(0, $days - 1))->map(fn (int $offset) => Carbon::today()->subDays($days - 1 - $offset));
        $keys = $dates->map(fn (Carbon $date) => $date->toDateString());

        $bookingRows = Booking::query()
            ->selectRaw('DATE(created_at) as date_key, COUNT(*) as total, COALESCE(SUM(total_amount), 0) as revenue')
            ->whereBetween('created_at', [$start->copy()->startOfDay(), $end])
            ->groupBy('date_key')
            ->get()
            ->keyBy('date_key');

        $playerRows = User::query()
            ->selectRaw('DATE(created_at) as date_key, COUNT(*) as total')
            ->where('role', 'player')
            ->whereBetween('created_at', [$start->copy()->startOfDay(), $end])
            ->groupBy('date_key')
            ->get()
            ->keyBy('date_key');

        $clubRows = User::query()
            ->selectRaw('DATE(created_at) as date_key, COUNT(*) as total')
            ->where('role', 'club')
            ->whereBetween('created_at', [$start->copy()->startOfDay(), $end])
            ->groupBy('date_key')
            ->get()
            ->keyBy('date_key');

        return [
            'labels' => $dates->map(fn (Carbon $date) => $date->format('d M'))->all(),
            'bookings' => $this->buildSeries($keys, $bookingRows, 'total'),
            'revenue' => $this->buildSeries($keys, $bookingRows, 'revenue', true),
            'players' => $this->buildSeries($keys, $playerRows, 'total'),
            'clubs' => $this->buildSeries($keys, $clubRows, 'total'),
        ];
    }

    private function buildMonthlyChartData(): array
    {
        $months = collect(range(11, 0))->map(fn (int $offset) => Carbon::now()->startOfMonth()->subMonths($offset));
        $start = $months->first()->copy()->startOfMonth();
        $end = Carbon::now();
        $keys = $months->map(fn (Carbon $date) => $date->format('Y-m'));

        $bookingRows = Booking::query()
            ->selectRaw("DATE_FORMAT(created_at, '%Y-%m') as date_key, COUNT(*) as total, COALESCE(SUM(total_amount), 0) as revenue")
            ->whereBetween('created_at', [$start, $end])
            ->groupBy('date_key')
            ->get()
            ->keyBy('date_key');

        $playerRows = User::query()
            ->selectRaw("DATE_FORMAT(created_at, '%Y-%m') as date_key, COUNT(*) as total")
            ->where('role', 'player')
            ->whereBetween('created_at', [$start, $end])
            ->groupBy('date_key')
            ->get()
            ->keyBy('date_key');

        $clubRows = User::query()
            ->selectRaw("DATE_FORMAT(created_at, '%Y-%m') as date_key, COUNT(*) as total")
            ->where('role', 'club')
            ->whereBetween('created_at', [$start, $end])
            ->groupBy('date_key')
            ->get()
            ->keyBy('date_key');

        return [
            'labels' => $months->map(fn (Carbon $date) => $date->format('M Y'))->all(),
            'bookings' => $this->buildSeries($keys, $bookingRows, 'total'),
            'revenue' => $this->buildSeries($keys, $bookingRows, 'revenue', true),
            'players' => $this->buildSeries($keys, $playerRows, 'total'),
            'clubs' => $this->buildSeries($keys, $clubRows, 'total'),
        ];
    }

    private function buildSeries(Collection $keys, Collection $rows, string $field, bool $thousands = false): array
    {
        return $keys->map(function (string $key) use ($rows, $field, $thousands) {
            $value = (float) ($rows->get($key)?->$field ?? 0);

            if ($thousands) {
                $value = round($value / 1000);
            }

            return $value == (int) $value ? (int) $value : $value;
        })->all();
    }

    private function formatCount(int $value): string
    {
        return number_format($value);
    }

    private function formatMoney(mixed $value): string
    {
        $numeric = (float) $value;
        $formatted = $numeric == (int) $numeric
            ? number_format((int) $numeric)
            : number_format($numeric, 2);

        return 'PKR '.$formatted;
    }

    private function normalizePeriod(int $period): int
    {
        return in_array($period, [7, 30, 90, 365], true) ? $period : 30;
    }
}
