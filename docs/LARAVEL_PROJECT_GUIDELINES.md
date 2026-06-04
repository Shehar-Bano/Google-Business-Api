# Laravel Project Guidelines

## 1) Project Overview

### Project Name
**Squash Pro**

### Tech Stack
- Laravel (PHP framework)
- Blade templating engine
- MySQL/MariaDB (relational database)
- Spatie Laravel Permission (roles and permissions)
- Bootstrap/Metronic admin UI
- JavaScript (module-specific enhancements)

### Admin Panel Purpose
The Squash Pro admin panel is the operational control center for managing platform data and workflows, including clubs, bookings, tournaments, users, permissions, and related administrative modules.

### Role-Based Architecture Summary
- Access control follows a **role + permission** model.
- Roles group permissions (e.g., Super Admin, Manager, Staff).
- Permissions control module/action access (view/create/edit/delete).
- Backend authorization is enforced at route/controller/policy level.
- Frontend visibility (sidebar, actions, buttons) is permission-aware.

---

## 2) Laravel Folder Structure

Use the following structure and responsibility boundaries:

- `app/Models`
  - Eloquent models only.
  - Keep relationships, casts, scopes, and lightweight model logic here.

- `app/Http/Controllers/Admin`
  - Admin module controllers.
  - Should orchestrate requests, authorization, and service calls.

- `app/Http/Controllers/Auth`
  - Authentication-specific controllers (login/logout/reset/etc.).

- `app/Http/Requests`
  - Form Request classes for validation and authorization per endpoint.

- `app/Services`
  - Business logic and application workflows.
  - Controllers should delegate use-case logic here.

- `app/Repositories`
  - Data access abstractions and query encapsulation when logic is complex/reused.

- `app/Policies`
  - Authorization policies per model/resource.

- `app/Enums`
  - PHP enums for constrained domain values (statuses, types, flags).

- `app/Helpers`
  - Project-specific helper utilities only when a class-based service is not more suitable.

- `database/migrations`
  - Every schema change must be migration-driven.

- `database/seeders`
  - Seeders for roles, permissions, defaults, and testable bootstrap data.

- `routes/web.php`
  - Public/non-admin routes.

- `routes/admin.php`
  - All admin routes grouped and protected.

- `resources/views/admin`
  - Admin Blade pages by module.

- `resources/views/layouts`
  - Shared layouts, wrappers, and reusable shell templates.

- `public/assets/admin`
  - Admin static assets (compiled/custom JS/CSS/images if needed).

---

## 3) Naming Conventions

Use consistent, intention-revealing names:

- Controllers: `ClubController`, `BookingController`
- Models: `Club`, `Booking`, `Tournament`
- Requests: `StoreClubRequest`, `UpdateClubRequest`
- Services: `ClubService`, `BookingService`
- Repositories: `ClubRepository`
- Policies: `ClubPolicy`
- Migrations: `create_clubs_table`
- Blade files: `kebab-case` (e.g., `index.blade.php`, `create-form.blade.php`)
- Routes: `admin.clubs.index`
- Permissions: `view_clubs`, `create_club`, `edit_club`

Additional standards:
- Table names: plural snake_case (`clubs`, `booking_slots`).
- Model properties/methods: camelCase.
- Constants: UPPER_SNAKE_CASE.

---

## 4) Admin Panel Rules

- All admin routes must be protected by `auth` middleware.
- Every admin module must enforce permissions.
- Sidebar items must render conditionally by permission.
- Do not hardcode role checks in Blade unless absolutely necessary.
- Prefer permission checks (`@can`, policies, middleware) over direct role checks.

---

## 5) Controller Rules

- Keep controllers thin and focused on HTTP orchestration.
- Validation must be handled via Form Request classes.
- Business logic belongs in Service classes.
- Complex/reusable queries should be placed in Repositories or Model scopes.
- Always run authorization checks before sensitive actions.

Controller responsibilities should typically include:
- Accept validated request.
- Authorize action.
- Call service method.
- Return response/redirect with status message.

---

## 6) Service Layer Pattern

Use this standard flow:

`Controller -> Request -> Service -> Repository/Model`

Example structure:

```text
app/
  Http/
    Controllers/
      Admin/
        CourtController.php
    Requests/
      StoreCourtRequest.php
      UpdateCourtRequest.php
  Services/
    CourtService.php
  Repositories/
    CourtRepository.php
  Models/
    Court.php
```

Example execution flow:
1. `CourtController@store` receives `StoreCourtRequest`.
2. Request handles validation and authorization gate.
3. Controller calls `CourtService::create($data)`.
4. Service applies business rules and calls repository/model.
5. Repository writes to DB and returns model/result.
6. Controller returns redirect/JSON response.

---

## 7) Blade/UI Rules

- Use reusable layouts and partials across admin pages.
- Sidebar must be dynamic and permission-driven.
- Create shared components/partials for:
  - Buttons
  - Cards
  - Badges
  - Tables
  - Alerts
- Do not duplicate repeated HTML structures.
- Keep Metronic/Bootstrap markup clean, readable, and consistent.
- Keep business logic out of Blade templates.

---

## 8) Routes Rules

- Public routes must stay in `routes/web.php`.
- Admin routes must stay in `routes/admin.php`.
- Always use named routes.
- Group routes by module (clubs, bookings, tournaments, courts, etc.).
- Apply middleware at group level where possible.

Recommended admin grouping pattern:

```php
Route::middleware(['auth'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {
        Route::resource('clubs', ClubController::class);
    });
```

---

## 9) Database Rules

- Every schema change requires a migration.
- Use seeders for roles, permissions, and default records.
- Add foreign keys wherever relational integrity applies.
- Use soft deletes where business requirements need recoverability.
- Add proper indexes on searchable/filterable columns.
- Keep migration files atomic and reversible.

---

## 10) Role & Permission Rules

- Use **Spatie Laravel Permission** package consistently.
- Create permissions in seeders only.
- Do not create permissions ad hoc in controllers/views/random scripts.
- Super Admin must receive all permissions.
- Every admin module must define and use explicit permissions.

Suggested module permission set pattern:
- `view_{module}`
- `create_{module}`
- `edit_{module}`
- `delete_{module}`

Example: `view_courts`, `create_court`, `edit_court`, `delete_court`

---

## 11) Code Quality Rules

- Follow PSR-12 coding standards.
- Use strict, readable, domain-meaningful names.
- Avoid duplicate logic; refactor reusable code.
- Never place business logic inside Blade templates.
- Avoid raw SQL unless absolutely necessary.
- Use DB transactions for multi-table create/update/delete workflows.
- Log errors with meaningful context.
- Never keep `dd()`, `dump()`, or `var_dump()` in production code.

---

## 12) Git/Commit Rules

- Use clear, descriptive commit messages.
- Prefer one feature/fix per commit.
- Do not commit `.env`, runtime storage artifacts, or `vendor`.
- Keep pull requests focused and review-friendly.

---

## 13) Before Writing Any Code

**Mandatory Instruction:**
Before creating any new feature, controller, route, Blade file, seeder, or permission, first check this documentation and follow the defined structure and naming conventions.

---

## 14) Example Feature Flow

### Example: Courts Module

1. Migration
- Create migration: `create_courts_table`
- Define fields, foreign keys, indexes, and soft deletes (if needed).

2. Model
- Create `Court` model in `app/Models/Court.php`
- Add fillable/casts/relationships/scopes.

3. Controller
- Create `CourtController` in `app/Http/Controllers/Admin`
- Keep actions thin and service-driven.

4. Request
- Create:
  - `StoreCourtRequest`
  - `UpdateCourtRequest`
- Place both in `app/Http/Requests`.

5. Service
- Create `CourtService` in `app/Services`
- Implement create/update/delete domain logic.

6. Routes
- Add module routes in `routes/admin.php`
- Use named routes such as `admin.courts.index`.

7. Sidebar Permission
- Show Courts menu only to users with `view_courts`.

8. Seeder Permission
- Add permissions in seeder:
  - `view_courts`
  - `create_court`
  - `edit_court`
  - `delete_court`
- Ensure Super Admin is granted all.

9. Blade Pages
- Create module views in `resources/views/admin/courts`:
  - `index.blade.php`
  - `create.blade.php`
  - `edit.blade.php`
  - `_form.blade.php` (shared partial)

---

## Enforcement Note

This document is the project’s Laravel implementation standard for Squash Pro. All contributors are expected to follow it before and during development to keep architecture maintainable, secure, and consistent.
