<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Business;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(Request $request): View
    {
        // User statistics — read all statuses from User model constants
        $userStats = ['total' => User::count()];
        foreach (User::STATUSES as $status) {
            $userStats[$status] = User::where('status', $status)->count();
        }

        // Business statistics instead of orders
        $businessStats = [
            'total' => Business::count(),
            'with_offerings' => Business::has('offerings')->count(),
            'without_offerings' => Business::doesntHave('offerings')->count(),
            'unique_locations' => Business::distinct('location')->count(),
        ];

        // Latest 10 businesses
        $latestBusinesses = Business::query()
            ->withCount('offerings')
            ->latest()
            ->limit(10)
            ->get();

        // 1. Daily: Last 7 days
        $dailyData = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = now()->subDays($i);
            $start = $date->copy()->startOfDay();
            $end = $date->copy()->endOfDay();
            $dailyData[$date->format('M d')] = User::whereBetween('created_at', [$start, $end])->count();
        }

        // 2. Weekly: Last 4 weeks
        $weeklyData = [];
        for ($i = 3; $i >= 0; $i--) {
            $start = now()->subWeeks($i)->startOfWeek();
            $end = now()->subWeeks($i)->endOfWeek();
            $weeklyData[$start->format('M d') . ' - ' . $end->format('M d')] = User::whereBetween('created_at', [$start, $end])->count();
        }

        // 3. Monthly: Last 6 months
        $monthlyData = [];
        for ($i = 5; $i >= 0; $i--) {
            $monthDate = now()->subMonths($i);
            $start = $monthDate->copy()->startOfMonth();
            $end = $monthDate->copy()->endOfMonth();
            $monthlyData[$monthDate->format('M Y')] = User::whereBetween('created_at', [$start, $end])->count();
        }

        // 4. Yearly: Last 5 years
        $yearlyData = [];
        for ($i = 4; $i >= 0; $i--) {
            $yearDate = now()->subYears($i);
            $start = $yearDate->copy()->startOfYear();
            $end = $yearDate->copy()->endOfYear();
            $yearlyData[$yearDate->format('Y')] = User::whereBetween('created_at', [$start, $end])->count();
        }

        $chartData = [
            'daily' => $dailyData,
            'weekly' => $weeklyData,
            'monthly' => $monthlyData,
            'yearly' => $yearlyData,
        ];

        return view('content.admin.dashboard.index', compact('userStats', 'businessStats', 'latestBusinesses', 'chartData'));
    }
}
