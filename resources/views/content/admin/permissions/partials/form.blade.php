<div class="mb-3">
    <label class="form-label">Permission Name</label>
    <input type="text" name="name" class="form-control" value="{{ old('name', $permission?->name) }}" required>
    <small class="text-muted">Use snake_case format (example: view_clubs).</small>
    @error('name')<div class="text-danger small">{{ $message }}</div>@enderror
</div>

<button type="submit" class="btn btn-primary">Save</button>
<a href="{{ route('admin.permissions.index') }}" class="btn btn-outline-secondary">Cancel</a>
