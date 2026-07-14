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

        return view('content.admin.dashboard.index', compact('userStats', 'businessStats', 'latestBusinesses'));
    }
}
