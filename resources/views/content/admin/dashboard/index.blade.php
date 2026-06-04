@extends('layouts/contentNavbarLayout')

@section('title', 'Dashboard')

@section('content')
<div class="row g-4 mb-4">
    @foreach($stats as $stat)
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card h-100">
                <div class="card-body d-flex align-items-start justify-content-between">
                    <div>
                        <p class="text-muted mb-1">{{ $stat['title'] }}</p>
                        <h4 class="mb-0">{{ $stat['value'] }}</h4>
                    </div>
                    <i class="{{ $stat['icon'] }} text-primary fs-2"></i>
                </div>
            </div>
        </div>
    @endforeach
</div>

<div class="row g-4">
    <div class="col-lg-8">
        <div class="card h-100">
            <div class="card-header">
                <h5 class="mb-0">Recent Users</h5>
            </div>
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Created</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($recentUsers as $user)
                            <tr>
                                <td>{{ $user->name }}</td>
                                <td>{{ $user->email }}</td>
                                <td>{{ optional($user->created_at)->format('Y-m-d') ?? '-' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="text-center text-muted py-4">No users yet.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card h-100">
            <div class="card-header">
                <h5 class="mb-0">Role Summary</h5>
            </div>
            <div class="card-body">
                @forelse($roleCounts as $roleCount)
                    <div class="d-flex justify-content-between align-items-center border-bottom py-2">
                        <span class="text-capitalize">{{ $roleCount->name }}</span>
                        <strong>{{ $roleCount->total }}</strong>
                    </div>
                @empty
                    <p class="text-muted mb-0">No roles assigned yet.</p>
                @endforelse

                <div class="mt-4 d-grid gap-2">
                    <a href="{{ route('admin.roles.index') }}" class="btn btn-primary">Manage Roles</a>
                    <a href="{{ route('admin.permissions.index') }}" class="btn btn-outline-secondary">Manage Permissions</a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
