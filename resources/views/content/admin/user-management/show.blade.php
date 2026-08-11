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
                    <div class="d-flex justify-content-center align-items-center gap-2 mb-3">
                        <span class="badge {{ $statusBadge }} text-capitalize">
                            {{ \App\Models\User::STATUS_LABELS[$user->status] ?? $user->status }}
                        </span>
                        @if($user->otp_verified)
                            <span class="badge bg-label-success"><i class="mdi mdi-check-decagram-outline me-1"></i> OTP Verified</span>
                        @else
                            <span class="badge bg-label-warning"><i class="mdi mdi-alert-circle-outline me-1"></i> OTP Unverified</span>
                        @endif
                    </div>

                    <div class="text-start mt-3">
                        <div class="d-flex justify-content-between border-bottom py-2">
                            <span class="text-muted small">Connected Business</span>
                            @php $uBus = $user->businesses->first(); @endphp
                            @if($uBus)
                                <a href="{{ route('admin.business-management.show', $uBus) }}" class="fw-semibold text-primary text-decoration-none">
                                    {{ $uBus->name }}
                                </a>
                            @else
                                <span class="text-muted small fst-italic">— None —</span>
                            @endif
                        </div>
                        <div class="d-flex justify-content-between border-bottom py-2">
                            <span class="text-muted small">Phone</span>
                            <strong>{{ $user->phone ?? '—' }}</strong>
                        </div>
                        <div class="d-flex justify-content-between border-bottom py-2">
                            <span class="text-muted small">User Type</span>
                            <strong class="text-capitalize">{{ $user->role ?? '—' }}</strong>
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

                    {{-- Status & OTP Update --}}
                    <div class="mt-4 p-3 bg-light rounded text-start border">
                        <form action="{{ route('admin.user-management.update-status', $user) }}" method="POST">
                            @csrf
                            @method('PATCH')
                            <input type="hidden" name="otp_verified_present" value="1">
                            <h6 class="fw-bold mb-3 small"><i class="mdi mdi-account-cog-outline me-1 text-primary"></i> Update User Status</h6>
                            <div class="mb-3">
                                <label class="form-label fw-semibold small text-muted">Account Status</label>
                                <select name="status" class="form-select form-select-sm">
                                    <option value="active" @selected($user->status === 'active')>Active</option>
                                    <option value="suspended" @selected($user->status === 'suspended')>Suspended</option>
                                </select>
                            </div>
                            <div class="mb-3 form-check form-switch">
                                <input class="form-check-input" type="checkbox" name="otp_verified" value="1" id="otpVerifiedSwitch" @checked($user->otp_verified)>
                                <label class="form-check-label fw-semibold small" for="otpVerifiedSwitch">OTP Verified</label>
                            </div>
                            <button class="btn btn-dark btn-sm w-100" type="submit">Save Changes</button>
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

            {{-- Registered Businesses --}}
            <h5 class="mb-3 fw-bold mt-4">Registered Businesses ({{ $user->businesses->count() }})</h5>
            @forelse($user->businesses as $business)
                <div class="card mb-3">
                    <div class="card-body">
                        <div class="d-flex align-items-center justify-content-between border-bottom pb-3 mb-3">
                            <div class="d-flex align-items-center gap-3">
                                @if($business->brand_logo)
                                    <img src="{{ asset($business->brand_logo) }}" alt="Logo" class="rounded" style="width: 60px; height: 60px; object-fit: cover; border: 1px solid #eef2ff;">
                                @else
                                    <div class="bg-label-primary rounded d-flex align-items-center justify-content-center fw-bold" style="width: 60px; height: 60px; font-size: 1.5rem;">
                                        {{ strtoupper(substr($business->name, 0, 1)) }}
                                    </div>
                                @endif
                                <div>
                                    <h5 class="mb-1 fw-bold">
                                        {{ $business->name }}
                                        @if($business->isVerified)
                                            <i class="mdi mdi-decagram text-primary" title="Verified Business"></i>
                                        @endif
                                    </h5>
                                    <p class="text-muted mb-0">
                                        <span class="badge bg-label-info text-capitalize me-2">{{ $business->category ?? 'General' }}</span>
                                        <i class="mdi mdi-map-marker me-1 text-danger"></i>{{ $business->location }}
                                    </p>
                                </div>
                            </div>
                            <div>
                                <a href="{{ route('admin.business-management.show', $business) }}" class="btn btn-sm btn-outline-primary">
                                    <i class="mdi mdi-eye-outline me-1"></i> View Detail Page
                                </a>
                            </div>
                        </div>

                        <div class="row g-3">
                            <div class="col-sm-4">
                                <span class="text-muted small d-block">Verification Status</span>
                                @if($business->isVerified)
                                    <span class="badge bg-label-primary mt-1">Verified</span>
                                @else
                                    <span class="badge bg-label-secondary mt-1">Not Verified</span>
                                @endif
                            </div>

                            <div class="col-sm-4">
                                <span class="text-muted small d-block">Rating & Reviews</span>
                                <strong class="cell-primary d-block mt-1">
                                    <i class="mdi mdi-star text-warning"></i> {{ number_format($business->rating ?? 0.0, 1) }} ({{ $business->reviews ?? 0 }} reviews)
                                </strong>
                            </div>

                            <div class="col-sm-4">
                                <span class="text-muted small d-block">Phone Number</span>
                                <strong class="cell-primary d-block mt-1">{{ $business->phone_number ?? '—' }}</strong>
                            </div>

                            <div class="col-sm-4">
                                <span class="text-muted small d-block">Address</span>
                                <strong class="cell-primary d-block mt-1">{{ $business->address ?? '—' }}</strong>
                            </div>

                            <div class="col-sm-4">
                                <span class="text-muted small d-block">Registered Date</span>
                                <strong class="cell-primary d-block mt-1">{{ $business->created_at?->format('Y-m-d H:i') ?? '—' }}</strong>
                            </div>

                            <div class="col-12 mt-2">
                                <span class="text-muted small d-block mb-1">Top Selling Items</span>
                                @if(is_array($business->top_selling_items) && count($business->top_selling_items) > 0)
                                    <div class="d-flex flex-wrap gap-1 mt-1">
                                        @foreach($business->top_selling_items as $item)
                                            <span class="badge bg-label-success">{{ $item }}</span>
                                        @endforeach
                                    </div>
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            @empty
                <div class="card">
                    <div class="card-body text-center py-4 text-muted">
                        No businesses registered for this user.
                    </div>
                </div>
            @endforelse
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
