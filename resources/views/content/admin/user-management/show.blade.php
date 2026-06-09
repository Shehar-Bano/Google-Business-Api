@extends('layouts/contentNavbarLayout')

@section('title', 'User Details')

@section('content')
<div class="admin-page">

    <div class="admin-page-header mb-4">
        <div class="admin-page-header__left">
            <h4 class="admin-page-header__title">User Details</h4>
            <p class="admin-page-header__subtitle">Viewing profile for {{ $user->name }}</p>
        </div>
        <div>
            <a href="{{ route('admin.user-management.index') }}" class="btn btn-outline-secondary btn-sm">
                <i class="mdi mdi-arrow-left me-1"></i> Back to Users
            </a>
        </div>
    </div>

    <div class="row g-4">
        {{-- Profile Card --}}
        <div class="col-lg-4">
            <div class="card h-100">
                <div class="card-body text-center pt-4">
                    @if($user->profile_image || $user->club_logo)
                        <img src="{{ asset('storage/' . ($user->profile_image ?? $user->club_logo)) }}"
                             alt="{{ $user->name }}"
                             class="um-detail-avatar mb-3">
                    @else
                        <div class="um-detail-avatar-placeholder mx-auto mb-3">
                            {{ strtoupper(substr($user->name, 0, 1)) }}
                        </div>
                    @endif

                    <h5 class="mb-1">{{ $user->name }}</h5>
                    <p class="text-muted mb-2">{{ $user->email }}</p>

                    @php
                        $statusBadge = match($user->status) {
                            'active'             => 'bg-label-success',
                            'pending'            => 'bg-label-warning',
                            'rejected'           => 'bg-label-danger',
                            'suspended'          => 'bg-label-dark',
                            default              => 'bg-label-secondary',
                        };
                    @endphp
                    <span class="badge {{ $statusBadge }} mb-3">
                        {{ \App\Models\User::STATUS_LABELS[$user->status] ?? $user->status }}
                    </span>

                    <div class="text-start mt-3">
                        <div class="d-flex justify-content-between border-bottom py-2">
                            <span class="text-muted small">User Type</span>
                            <strong class="text-capitalize">{{ $user->role ?? '—' }}</strong>
                        </div>
                        <div class="d-flex justify-content-between border-bottom py-2">
                            <span class="text-muted small">Phone</span>
                            <strong>{{ $user->phone ?? '—' }}</strong>
                        </div>
                        <div class="d-flex justify-content-between border-bottom py-2">
                            <span class="text-muted small">City</span>
                            <strong>{{ $user->city ?? '—' }}</strong>
                        </div>
                        <div class="d-flex justify-content-between border-bottom py-2">
                            <span class="text-muted small">Joined</span>
                            <strong>{{ $user->created_at?->format('Y-m-d') ?? '—' }}</strong>
                        </div>
                        <div class="d-flex justify-content-between py-2">
                            <span class="text-muted small">Roles</span>
                            <strong>{{ $user->roles->pluck('name')->join(', ') ?: 'None' }}</strong>
                        </div>
                    </div>

                    {{-- Status Update --}}
                    <div class="mt-4">
                        <form action="{{ route('admin.user-management.update-status', $user) }}" method="POST">
                            @csrf
                            @method('PATCH')
                            <label class="form-label text-start d-block fw-semibold small">Update Status</label>
                            <div class="input-group input-group-sm">
                                <select name="status" class="form-select">
                                    @foreach(\App\Models\User::ADMIN_STATUS_LABELS as $val => $label)
                                        <option value="{{ $val }}" @selected($user->status === $val)>{{ $label }}</option>
                                    @endforeach
                                </select>
                                <button class="btn btn-dark" type="submit">Update</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        {{-- Player / Club Details --}}
        <div class="col-lg-8">
            @if($user->role === 'player')
                <div class="card mb-4">
                    <div class="card-header"><h6 class="mb-0">Player Profile</h6></div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-sm-6">
                                <label class="form-label text-muted small">Date of Birth</label>
                                <p class="mb-0 fw-semibold">{{ $user->dob?->format('Y-m-d') ?? '—' }}</p>
                            </div>
                            <div class="col-sm-6">
                                <label class="form-label text-muted small">Gender</label>
                                <p class="mb-0 fw-semibold text-capitalize">{{ $user->gender ?? '—' }}</p>
                            </div>
                            <div class="col-sm-6">
                                <label class="form-label text-muted small">Playing Level</label>
                                <p class="mb-0 fw-semibold text-capitalize">{{ $user->playing_level ?? '—' }}</p>
                            </div>
                            <div class="col-sm-6">
                                <label class="form-label text-muted small">Primary Hand</label>
                                <p class="mb-0 fw-semibold text-capitalize">{{ $user->primary_hand ?? '—' }}</p>
                            </div>
                            @if($user->bio)
                                <div class="col-12">
                                    <label class="form-label text-muted small">Bio</label>
                                    <p class="mb-0">{{ $user->bio }}</p>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            @elseif($user->role === 'club')
                <div class="card mb-4">
                    <div class="card-header"><h6 class="mb-0">Club Information</h6></div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-sm-6">
                                <label class="form-label text-muted small">Club Name</label>
                                <p class="mb-0 fw-semibold">{{ $user->club_name ?? '—' }}</p>
                            </div>
                            <div class="col-sm-6">
                                <label class="form-label text-muted small">Owner / Manager</label>
                                <p class="mb-0 fw-semibold">{{ $user->owner_manager_name ?? '—' }}</p>
                            </div>
                            <div class="col-sm-6">
                                <label class="form-label text-muted small">Address</label>
                                <p class="mb-0 fw-semibold">{{ $user->address ?? '—' }}</p>
                            </div>
                            <div class="col-sm-6">
                                <label class="form-label text-muted small">Number of Courts</label>
                                <p class="mb-0 fw-semibold">{{ $user->number_of_courts ?? '—' }}</p>
                            </div>
                            <div class="col-sm-6">
                                <label class="form-label text-muted small">Working Hours</label>
                                <p class="mb-0 fw-semibold">{{ $user->working_hours ?? '—' }}</p>
                            </div>
                            @if($user->facilities)
                                <div class="col-12">
                                    <label class="form-label text-muted small">Facilities</label>
                                    <div class="d-flex flex-wrap gap-2 mt-1">
                                        @foreach((array) $user->facilities as $facility)
                                            <span class="badge bg-label-primary">{{ $facility }}</span>
                                        @endforeach
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            @endif

            {{-- Orders --}}
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h6 class="mb-0">Orders</h6>
                    <a href="{{ route('admin.order-management.index', ['search' => $user->name]) }}" class="btn btn-sm btn-outline-primary">View All</a>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th>Order #</th>
                                <th>Status</th>
                                <th>Amount</th>
                                <th>Date</th>
                                <th class="text-end">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($user->orders()->with('detail:order_id,price')->latest()->limit(10)->get() as $order)
                                @php
                                    $orderBadge = match($order->status) {
                                        'active'    => 'bg-label-success',
                                        'in_review' => 'bg-label-warning',
                                        'cancelled' => 'bg-label-danger',
                                        'completed' => 'bg-label-primary',
                                        default     => 'bg-label-secondary',
                                    };
                                @endphp
                                <tr>
                                    <td class="fw-semibold">{{ $order->order_id }}</td>
                                    <td><span class="badge {{ $orderBadge }} text-capitalize">{{ \App\Models\Order::STATUS_LABELS[$order->status] ?? $order->status }}</span></td>
                                    <td>{{ number_format($order->detail?->price ?? 0) }}</td>
                                    <td class="text-muted small">{{ $order->created_at?->format('Y-m-d') }}</td>
                                    <td class="text-end">
                                        <a href="{{ route('admin.order-management.show', $order) }}" class="action-icon-btn action-icon-view" title="View Order">
                                            <i class="mdi mdi-eye-outline"></i>
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="5" class="text-center text-muted py-3">No orders found.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

</div>
@endsection

@push('my-styles')
<style>
    .um-detail-avatar {
        width: 80px;
        height: 80px;
        border-radius: 20px;
        object-fit: cover;
        border: 2px solid #e6ebf2;
    }
    .um-detail-avatar-placeholder {
        width: 80px;
        height: 80px;
        border-radius: 20px;
        background: #eef2ff;
        color: #4f46e5;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 2rem;
        font-weight: 800;
    }
</style>
@endpush
