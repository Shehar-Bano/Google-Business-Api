<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Business;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\View\View;

use Illuminate\Support\Facades\Cache;

class DashboardController extends Controller
{
    public function index(Request $request): View
    {
        $range = in_array(
            $request->string('range', 'all')->toString(),
            ['today', 'week', 'month', 'year', 'all'],
            true
        ) ? $request->string('range', 'all')->toString() : 'all';

        // Cache stats for 5 minutes per range value to ensure high speed loading
        $cacheKey = 'admin_dashboard_stats_data_' . $range;
        $dashboardData = Cache::remember($cacheKey, 300, function () use ($range) {
            $start = null;
            $end = null;

            if ($range === 'today') {
                $start = now()->startOfDay();
                $end = now()->endOfDay();
            } elseif ($range === 'week') {
                $start = now()->startOfWeek();
                $end = now()->endOfWeek();
            } elseif ($range === 'month') {
                $start = now()->startOfMonth();
                $end = now()->endOfMonth();
            } elseif ($range === 'year') {
                $start = now()->startOfYear();
                $end = now()->endOfYear();
            }

            // User statistics
            $userQuery = User::query();
            if ($start && $end) {
                $userQuery->whereBetween('created_at', [$start, $end]);
            }
            $userStats = ['total' => (clone $userQuery)->count()];
            foreach (User::STATUSES as $status) {
                $userStats[$status] = (clone $userQuery)->where('status', $status)->count();
            }

            // Business statistics
            $businessQuery = Business::query();
            if ($start && $end) {
                $businessQuery->whereBetween('created_at', [$start, $end]);
            }
            $businessStats = [
                'total' => (clone $businessQuery)->count(),
                'suspended' => (clone $businessQuery)->where('status', 'suspended')->count(),
                'approved' => (clone $businessQuery)->where(fn($q) => $q->where('status', 'approved')->orWhereNull('status'))->count(),
            ];

            // AI Statistics
            $posterQuery = \App\Models\AiGeneratedPoster::query();
            $keywordQuery = \App\Models\BusinessKeywordIdea::query();
            if ($start && $end) {
                $posterQuery->whereBetween('created_at', [$start, $end]);
                $keywordQuery->whereBetween('created_at', [$start, $end]);
            }
            $aiStats = [
                'total_generated_posters' => $posterQuery->count(),
                'total_keywords' => $keywordQuery->count(),
            ];

            // Chart Data
            // 1. Daily: Last 7 days
            $dailyData = [];
            for ($i = 6; $i >= 0; $i--) {
                $date = now()->subDays($i);
                $dStart = $date->copy()->startOfDay();
                $dEnd = $date->copy()->endOfDay();
                $dailyData[$date->format('M d')] = User::whereBetween('created_at', [$dStart, $dEnd])->count();
            }

            // 2. Weekly: Last 4 weeks
            $weeklyData = [];
            for ($i = 3; $i >= 0; $i--) {
                $wStart = now()->subWeeks($i)->startOfWeek();
                $wEnd = now()->subWeeks($i)->endOfWeek();
                $weeklyData[$wStart->format('M d') . ' - ' . $wEnd->format('M d')] = User::whereBetween('created_at', [$wStart, $wEnd])->count();
            }

            // 3. Monthly: Last 6 months
            $monthlyData = [];
            for ($i = 5; $i >= 0; $i--) {
                $monthDate = now()->subMonths($i);
                $mStart = $monthDate->copy()->startOfMonth();
                $mEnd = $monthDate->copy()->endOfMonth();
                $monthlyData[$monthDate->format('M Y')] = User::whereBetween('created_at', [$mStart, $mEnd])->count();
            }

            // 4. Yearly: Last 5 years
            $yearlyData = [];
            for ($i = 4; $i >= 0; $i--) {
                $yearDate = now()->subYears($i);
                $yStart = $yearDate->copy()->startOfYear();
                $yEnd = $yearDate->copy()->endOfYear();
                $yearlyData[$yearDate->format('Y')] = User::whereBetween('created_at', [$yStart, $yEnd])->count();
            }

            $chartData = [
                'daily' => $dailyData,
                'weekly' => $weeklyData,
                'monthly' => $monthlyData,
                'yearly' => $yearlyData,
            ];

            return [
                'userStats' => $userStats,
                'businessStats' => $businessStats,
                'aiStats' => $aiStats,
                'chartData' => $chartData,
            ];
        });

        // Latest 10 businesses (Not cached to keep new arrivals instantly visible, very fast query)
        $latestBusinesses = Business::query()
            ->withCount('offerings')
            ->latest()
            ->limit(10)
            ->get();

        $userStats = $dashboardData['userStats'];
        $businessStats = $dashboardData['businessStats'];
        $aiStats = $dashboardData['aiStats'];
        $chartData = $dashboardData['chartData'];

        return view('content.admin.dashboard.index', compact('userStats', 'businessStats', 'aiStats', 'latestBusinesses', 'chartData', 'range'));
    }
}
