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
        'user',
    ];

    private const PERMISSIONS = [
        'view_dashboard',
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
        'edit_user',
        'assign_roles',
        'view_support_options',
        'create_support_option',
        'edit_support_option',
        'delete_support_option',
        'view_privacy_policy',
        'edit_privacy_policy',
        'view_notifications',
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

        $superAdminRole = Role::where('name', 'super_admin')->firstOrFail();
        $userRole = Role::where('name', 'user')->firstOrFail();
        // $adminRole = Role::where('name', 'admin')->firstOrFail();

        $superAdminRole->syncPermissions(self::PERMISSIONS);
        $userRole->syncPermissions([
            'view_dashboard',
            'view_users',
            'edit_user',
            'assign_roles',
            'view_roles',
            'view_permissions',
            'view_support_options',
            'view_privacy_policy',
            'view_notifications',
        ]);
        // $adminRole->syncPermissions([
        //     'view_dashboard',
        //     'view_users',
        //     'edit_user',
        //     'assign_roles',
        //     'view_roles',
        //     'view_permissions',
        //     'view_support_options',
        //     'view_privacy_policy',
        //     'view_notifications',
        // ]);

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
}
