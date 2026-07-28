<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StorePermissionRequest;
use App\Http\Requests\Admin\UpdatePermissionRequest;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class PermissionController extends Controller
{
    public function index(Request $request): View
    {
        $perPage = (int) $request->integer('per_page', 10);
        $perPage = in_array($perPage, [10, 25, 50, 100], true) ? $perPage : 10;

        $sort = $request->string('sort', 'name')->toString();
        $direction = $request->string('direction', 'asc')->toString();
        $direction = in_array($direction, ['asc', 'desc'], true) ? $direction : 'asc';

        $allowedSorts = ['name', 'guard_name', 'created_at'];
        $sort = in_array($sort, $allowedSorts, true) ? $sort : 'name';

        $search = trim($request->string('search')->toString());

        $permissions = Permission::query()
            ->when($search !== '', function (Builder $query) use ($search): void {
                $query->where(function (Builder $subQuery) use ($search): void {
                    $subQuery
                        ->where('name', 'like', "%{$search}%")
                        ->orWhere('guard_name', 'like', "%{$search}%");
                });
            })
            ->orderBy($sort, $direction)
            ->paginate($perPage)
            ->withQueryString();

        return view('content.admin.permissions.index', compact('permissions', 'search', 'sort', 'direction', 'perPage'));
    }

    public function create(): View
    {
        return view('content.admin.permissions.create');
    }

    public function store(StorePermissionRequest $request): RedirectResponse
    {
        $permission = Permission::create([
            'name' => $request->string('name')->toString(),
            'guard_name' => 'web',
        ]);

        \App\Models\AdminAuditLog::log(
            'permission_create',
            'Permission',
            (string) $permission->id,
            "Created permission '{$permission->name}'.",
            ['permission_name' => $permission->name]
        );

        return redirect()->route('admin.permissions.index')->with('success', 'Permission created successfully.');
    }

    public function edit(Permission $permission): View
    {
        return view('content.admin.permissions.edit', compact('permission'));
    }

    public function update(UpdatePermissionRequest $request, Permission $permission): RedirectResponse
    {
        $oldName = $permission->name;
        $newName = $request->string('name')->toString();

        $permission->update([
            'name' => $newName,
        ]);

        \App\Models\AdminAuditLog::log(
            'permission_update',
            'Permission',
            (string) $permission->id,
            "Updated permission '{$oldName}' to '{$newName}'.",
            ['old_name' => $oldName, 'new_name' => $newName]
        );

        return redirect()->route('admin.permissions.index')->with('success', 'Permission updated successfully.');
    }

    public function destroy(Permission $permission): RedirectResponse
    {
        $superAdminRole = Role::query()->where('name', 'super_admin')->first();

        if ($superAdminRole && $superAdminRole->permissions()->whereKey($permission->id)->exists()) {
            return back()->withErrors(['permission' => 'Cannot delete a permission assigned to super_admin.']);
        }

        $permissionName = $permission->name;
        $permissionId = $permission->id;

        DB::transaction(function () use ($permission): void {
            $permission->roles()->detach();
            $permission->delete();
        });

        \App\Models\AdminAuditLog::log(
            'permission_delete',
            'Permission',
            (string) $permissionId,
            "Deleted permission '{$permissionName}'.",
            ['permission_name' => $permissionName]
        );

        return redirect()->route('admin.permissions.index')->with('success', 'Permission deleted successfully.');
    }
}
