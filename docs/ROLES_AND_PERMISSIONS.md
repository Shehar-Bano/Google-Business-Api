# Roles and Permissions - SquashPro

## Roles
- `super_admin`: full platform access, all permissions.
- `admin`: full platform access, all permissions.
- `club`: club-level access for operational modules only.
- `player`: no admin panel access by default.

Legacy compatibility roles kept in project:
- `admin_vet`
- `client`
- `client_vet`

## Permissions By Module

### Dashboard
- `view_dashboard`

### Clubs
- `view_clubs`
- `create_club`
- `edit_club`
- `delete_club`
- `approve_club`
- `verify_club`
- `suspend_club`
- `feature_club`

### Players
- `view_players`
- `edit_player`
- `suspend_player`
- `delete_player`

### Bookings
- `view_bookings`
- `edit_booking`
- `cancel_booking`
- `refund_booking`

### Courts
- `view_courts`
- `create_court`
- `edit_court`
- `delete_court`

### Tournaments
- `view_tournaments`
- `create_tournament`
- `edit_tournament`
- `approve_tournament`
- `suspend_tournament`
- `delete_tournament`
- `manage_tournament_results`

### Payments and Revenue
- `view_payments`
- `view_revenue`
- `export_revenue`
- `manage_refunds`

### Notifications
- `view_notifications`
- `send_notifications`
- `schedule_notifications`

### Settings
- `view_settings`
- `manage_settings`

### Reports
- `view_reports`
- `export_reports`

### Roles and Permissions
- `view_roles`
- `create_role`
- `edit_role`
- `delete_role`
- `assign_permissions`
- `view_permissions`
- `create_permission`
- `edit_permission`
- `delete_permission`

### Users
- `view_users`
- `create_user`
- `edit_user`
- `delete_user`
- `assign_roles`

## Role Mapping

### super_admin
- All permissions.

### admin
- All permissions.

### club
- `view_dashboard`
- `view_bookings`
- `edit_booking`
- `cancel_booking`
- `view_courts`
- `create_court`
- `edit_court`
- `delete_court`
- `view_tournaments`
- `create_tournament`
- `edit_tournament`
- `manage_tournament_results`
- `view_payments`
- `view_revenue`
- `view_notifications`
- `view_reports`

### player
- No admin permissions.

## Add New Permission
1. Add the permission name in `database/seeders/RolePermissionSeeder.php` inside `PERMISSIONS`.
2. Assign it to relevant roles in seeder mapping.
3. Run seeder again.
4. Protect routes/controllers using `permission:` middleware and `@can` checks.

## Add New Sidebar Item
1. Add menu item in `resources/views/layouts/sections/menu/verticalMenu.blade.php`.
2. Wrap with `@can(...)` or `@canany(...)`.
3. Ensure route exists and is permission-protected.

## Protect Route and Controller

### Route
```php
Route::get('admin/example', ExampleController::class)
    ->middleware('permission:view_reports');
```

### Controller
```php
abort_if(auth()->user()->cannot('view_reports'), 403);
```

## Seeder Command
```bash
php artisan db:seed --class=RolePermissionSeeder
```

## Full Setup Commands
```bash
php artisan migrate
php artisan db:seed
php artisan permission:cache-reset
```
