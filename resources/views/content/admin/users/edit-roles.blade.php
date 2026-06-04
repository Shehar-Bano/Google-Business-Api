@extends('layouts/contentNavbarLayout')

@section('title', 'Assign Roles')

@section('content')
<div class="card">
    <div class="card-header"><h5 class="mb-0">Assign Roles - {{ $user->name }}</h5></div>
    <div class="card-body">
        <form action="{{ route('admin.users.roles.update', $user) }}" method="POST">
            @csrf
            @method('PUT')
            <div class="mb-3">
                <label class="form-label">Roles</label>
                @foreach($roles as $role)
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="roles[]" value="{{ $role->name }}"
                            @checked(in_array($role->name, old('roles', $selectedRoles), true))>
                        <label class="form-check-label">{{ $role->name }}</label>
                    </div>
                @endforeach
                @error('roles')<div class="text-danger small">{{ $message }}</div>@enderror
            </div>
            <button type="submit" class="btn btn-primary">Update Roles</button>
            <a href="{{ route('admin.users.index') }}" class="btn btn-outline-secondary">Cancel</a>
        </form>
    </div>
</div>
@endsection
