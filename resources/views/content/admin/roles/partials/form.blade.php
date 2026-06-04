<div class="mb-3">
    <label class="form-label">Role Name</label>
    <input type="text" name="name" class="form-control" value="{{ old('name', $role?->name) }}" required>
    @error('name')<div class="text-danger small">{{ $message }}</div>@enderror
</div>

<div class="mb-3">
    <label class="form-label">Permissions</label>
    <div class="row">
        @foreach($groupedPermissions as $group => $permissionNames)
            <div class="col-md-4 mb-3">
                <div class="border rounded p-2 h-100">
                    <div class="fw-semibold text-capitalize mb-2">{{ str_replace('_', ' ', $group) }}</div>
                    @foreach($permissionNames as $permissionName)
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="permissions[]" value="{{ $permissionName }}"
                                @checked(in_array($permissionName, old('permissions', $selectedPermissions ?? []), true))>
                            <label class="form-check-label">{{ $permissionName }}</label>
                        </div>
                    @endforeach
                </div>
            </div>
        @endforeach
    </div>
    @error('permissions')<div class="text-danger small">{{ $message }}</div>@enderror
</div>

<button type="submit" class="btn btn-primary">Save</button>
<a href="{{ route('admin.roles.index') }}" class="btn btn-outline-secondary">Cancel</a>
