<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdateUserRolesRequest;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Spatie\Permission\Models\Role;

class UserRoleController extends Controller
{
    public function index(Request $request): View
    {
        $perPage = (int) $request->integer('per_page', 10);
        $perPage = in_array($perPage, [10, 15, 20, 25, 50, 100], true) ? $perPage : 10;

        $sort = $request->string('sort', 'name')->toString();
        $direction = $request->string('direction', 'asc')->toString();
        $direction = in_array($direction, ['asc', 'desc'], true) ? $direction : 'asc';

        $allowedSorts = ['name', 'email', 'created_at'];
        $sort = in_array($sort, $allowedSorts, true) ? $sort : 'name';

        $search = trim($request->string('search')->toString());
        $roleFilter = trim($request->string('role')->toString());
        $status = trim($request->string('status')->toString());
        $dateFrom = trim($request->string('date_from')->toString());
        $dateTo = trim($request->string('date_to')->toString());

        $users = User::query()
            ->with('roles')
            ->when($search !== '', function (Builder $query) use ($search): void {
                $query->where(function (Builder $subQuery) use ($search): void {
                    $subQuery
                        ->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                });
            })
            ->when($roleFilter !== '', function (Builder $query) use ($roleFilter): void {
                $query->role($roleFilter);
            })
            ->when($status !== '', function (Builder $query) use ($status): void {
                if ($status === 'active') {
                    $query->whereNotNull('email_verified_at');
                } elseif ($status === 'pending') {
                    $query->whereNull('email_verified_at');
                }
            })
            ->when($dateFrom !== '', fn (Builder $q) => $q->whereDate('created_at', '>=', $dateFrom))
            ->when($dateTo !== '', fn (Builder $q) => $q->whereDate('created_at', '<=', $dateTo))
            ->orderBy($sort, $direction)
            ->paginate($perPage)
            ->withQueryString();

        $roles = Role::query()->orderBy('name')->get();

        return view('content.admin.users.index', compact(
            'users', 'roles', 'search', 'roleFilter', 'status', 'dateFrom', 'dateTo', 'sort', 'direction', 'perPage'
        ));
    }

    public function edit(User $user): View
    {
        $roles = Role::query()->orderBy('name')->get();

        return view('content.admin.users.edit-roles', [
            'user' => $user,
            'roles' => $roles,
            'selectedRoles' => $user->roles->pluck('name')->all(),
        ]);
    }

    public function update(UpdateUserRolesRequest $request, User $user): RedirectResponse
    {
        $roles = $request->input('roles', []);
        $user->syncRoles($roles);

        \App\Models\AdminAuditLog::log(
            'role_assign',
            'User',
            (string) $user->id,
            "Assigned roles to user {$user->name}: " . implode(', ', $roles),
            ['user_id' => $user->id, 'roles' => $roles]
        );

        return redirect()->route('admin.users.index')->with('success', 'User roles updated successfully.');
    }
}
