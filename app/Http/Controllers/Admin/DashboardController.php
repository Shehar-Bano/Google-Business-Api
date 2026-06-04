<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class DashboardController extends Controller
{
    public function index(Request $request): View
    {
        $stats = [
            [
                'title' => 'Total Users',
                'value' => number_format(User::count()),
                'icon' => 'mdi mdi-account-multiple',
            ],
            [
                'title' => 'Verified Users',
                'value' => number_format(User::whereNotNull('email_verified_at')->count()),
                'icon' => 'mdi mdi-account-check',
            ],
            [
                'title' => 'Roles',
                'value' => number_format(Role::count()),
                'icon' => 'mdi mdi-account-key',
            ],
            [
                'title' => 'Permissions',
                'value' => number_format(Permission::count()),
                'icon' => 'mdi mdi-shield-key',
            ],
        ];

        $recentUsers = User::query()
            ->select(['id', 'name', 'email', 'created_at'])
            ->latest()
            ->limit(5)
            ->get();

        $roleCounts = DB::table('model_has_roles')
            ->join('roles', 'roles.id', '=', 'model_has_roles.role_id')
            ->select('roles.name', DB::raw('COUNT(*) as total'))
            ->groupBy('roles.name')
            ->orderByDesc('total')
            ->get();

        return view('content.admin.dashboard.index', compact('stats', 'recentUsers', 'roleCounts'));
    }
}
