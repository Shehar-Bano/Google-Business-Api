<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreRoleRequest;
use App\Http\Requests\Admin\UpdateRoleRequest;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RoleController extends Controller
{
    private const PROTECTED_ROLES = ['super_admin', 'admin'];

    public function index(Request $request): View
    {
        $perPage = (int) $request->integer('per_page', 10);
        $perPage = in_array($perPage, [10, 15, 20, 25, 50, 100], true) ? $perPage : 10;

        $sort = $request->string('sort', 'name')->toString();
        $direction = $request->string('direction', 'asc')->toString();
        $direction = in_array($direction, ['asc', 'desc'], true) ? $direction : 'asc';

        $allowedSorts = ['name', 'permissions_count', 'created_at'];
        $sort = in_array($sort, $allowedSorts, true) ? $sort : 'name';

        $search = trim($request->string('search')->toString());
        $dateFrom = trim($request->string('date_from')->toString());
        $dateTo = trim($request->string('date_to')->toString());

        $roles = Role::query()
            ->withCount('permissions')
            ->when($search !== '', function (Builder $query) use ($search): void {
                $query->where('name', 'like', "%{$search}%");
            })
            ->when($dateFrom !== '', fn (Builder $q) => $q->whereDate('created_at', '>=', $dateFrom))
            ->when($dateTo !== '', fn (Builder $q) => $q->whereDate('created_at', '<=', $dateTo))
            ->orderBy($sort, $direction)
            ->paginate($perPage)
            ->withQueryString();

        return view('content.admin.roles.index', compact('roles', 'search', 'dateFrom', 'dateTo', 'sort', 'direction', 'perPage'));
    }

    public function create(): View
    {
        $permissions = Permission::query()->orderBy('name')->get();

        return view('content.admin.roles.create', [
            'permissions' => $permissions,
            'groupedPermissions' => $this->groupPermissions($permissions->pluck('name')->all()),
        ]);
    }

    public function store(StoreRoleRequest $request): RedirectResponse
    {
        $role = Role::create([
            'name' => $request->string('name')->toString(),
            'guard_name' => 'web',
        ]);

        $permissions = $request->input('permissions', []);
        $role->syncPermissions($permissions);

        \App\Models\AdminAuditLog::log(
            'role_create',
            'Role',
            (string) $role->id,
            "Created role '{$role->name}' with permissions: " . implode(', ', $permissions),
            ['role_name' => $role->name, 'permissions' => $permissions]
        );

        return redirect()->route('admin.roles.index')->with('success', 'Role created successfully.');
    }

    public function edit(Role $role): View
    {
        $permissions = Permission::query()->orderBy('name')->get();

        return view('content.admin.roles.edit', [
            'role' => $role,
            'permissions' => $permissions,
            'selectedPermissions' => $role->permissions->pluck('name')->all(),
            'groupedPermissions' => $this->groupPermissions($permissions->pluck('name')->all()),
        ]);
    }

    public function update(UpdateRoleRequest $request, Role $role): RedirectResponse
    {
        if (in_array($role->name, self::PROTECTED_ROLES, true) && $role->name !== $request->string('name')->toString()) {
            return back()->withErrors(['name' => 'Protected roles cannot be renamed.'])->withInput();
        }

        $oldName = $role->name;
        $newName = $request->string('name')->toString();

        $role->update([
            'name' => $newName,
        ]);

        $permissions = $request->input('permissions', []);
        $role->syncPermissions($permissions);

        \App\Models\AdminAuditLog::log(
            'role_update',
            'Role',
            (string) $role->id,
            "Updated role '{$oldName}' to '{$newName}' with permissions: " . implode(', ', $permissions),
            ['old_name' => $oldName, 'new_name' => $newName, 'permissions' => $permissions]
        );

        return redirect()->route('admin.roles.index')->with('success', 'Role updated successfully.');
    }

    public function destroy(Role $role): RedirectResponse
    {
        if (in_array($role->name, self::PROTECTED_ROLES, true)) {
            return back()->withErrors(['role' => 'This role is protected and cannot be deleted.']);
        }

        $roleName = $role->name;
        $role->delete();

        \App\Models\AdminAuditLog::log(
            'role_delete',
            'Role',
            (string) $role->id,
            "Deleted role '{$roleName}'.",
            ['role_name' => $roleName]
        );

        return redirect()->route('admin.roles.index')->with('success', 'Role deleted successfully.');
    }

    private function groupPermissions(array $permissionNames): array
    {
        $groups = [];

        foreach ($permissionNames as $permissionName) {
            $module = str_contains($permissionName, '_')
                ? explode('_', $permissionName, 2)[1]
                : $permissionName;

            $groups[$module][] = $permissionName;
        }

        ksort($groups);

        return $groups;
    }
}
