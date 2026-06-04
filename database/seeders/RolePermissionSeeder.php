<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RolePermissionSeeder extends Seeder
{
    private const ROLES = [
        'super_admin',
        'admin',
        'club',
        'player',
    ];

    private const PERMISSIONS = [
        'view_dashboard',
        'view_clubs',
        'create_club',
        'edit_club',
        'delete_club',
        'approve_club',
        'verify_club',
        'suspend_club',
        'feature_club',
        'view_players',
        'edit_player',
        'suspend_player',
        'delete_player',
        'view_bookings',
        'edit_booking',
        'cancel_booking',
        'refund_booking',
        'view_courts',
        'create_court',
        'edit_court',
        'delete_court',
        'view_tournaments',
        'create_tournament',
        'edit_tournament',
        'approve_tournament',
        'suspend_tournament',
        'delete_tournament',
        'manage_tournament_results',
        'view_payments',
        'view_revenue',
        'export_revenue',
        'manage_refunds',
        'view_notifications',
        'send_notifications',
        'schedule_notifications',
        'view_settings',
        'manage_settings',
        'view_reports',
        'export_reports',
        'view_roles',
        'create_role',
        'edit_role',
        'delete_role',
        'assign_permissions',
        'view_permissions',
        'create_permission',
        'edit_permission',
        'delete_permission',
        'view_users',
        'create_user',
        'edit_user',
        'delete_user',
        'assign_roles',
    ];

    private const CLUB_PERMISSIONS = [
        'view_dashboard',
        'view_bookings',
        'edit_booking',
        'cancel_booking',
        'view_courts',
        'create_court',
        'edit_court',
        'delete_court',
        'view_tournaments',
        'create_tournament',
        'edit_tournament',
        'manage_tournament_results',
        'view_payments',
        'view_revenue',
        'view_notifications',
        'view_reports',
    ];

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        foreach (self::PERMISSIONS as $permissionName) {
            Permission::firstOrCreate([
                'name' => $permissionName,
                'guard_name' => 'web',
            ]);
        }

        foreach (self::ROLES as $roleName) {
            Role::firstOrCreate([
                'name' => $roleName,
                'guard_name' => 'web',
            ]);
        }

        // Remove obsolete legacy roles if they still exist in old environments.
        Role::query()
            ->whereIn('name', ['admin_vet', 'client', 'client_vet'])
            ->get()
            ->each
            ->delete();

        $superAdminRole = Role::where('name', 'super_admin')->firstOrFail();
        $adminRole = Role::where('name', 'admin')->firstOrFail();
        $clubRole = Role::where('name', 'club')->firstOrFail();
        $playerRole = Role::where('name', 'player')->firstOrFail();

        $superAdminRole->syncPermissions(self::PERMISSIONS);
        $adminRole->syncPermissions(self::PERMISSIONS);
        $clubRole->syncPermissions(self::CLUB_PERMISSIONS);
        $playerRole->syncPermissions([]);

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
}
