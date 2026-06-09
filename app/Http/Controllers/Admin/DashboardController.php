<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
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

        // Order statistics — read all statuses from Order model constants
        $orderStats = ['total' => Order::count()];
        foreach (Order::STATUSES as $status) {
            $orderStats[$status] = Order::where('status', $status)->count();
        }

        // Latest 10 orders with user and detail
        $latestOrders = Order::query()
            ->with(['user:id,name,email', 'detail:order_id,price'])
            ->latest()
            ->limit(10)
            ->get();

        return view('content.admin.dashboard.index', compact('userStats', 'orderStats', 'latestOrders'));
    }
}
