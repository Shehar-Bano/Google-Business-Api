@extends('layouts/contentNavbarLayout')

@section('title', $player->name . ' — Player Detail')

@section('content')
<div class="d-flex align-items-center gap-2 mb-4">
    <a href="{{ route('admin.players.index') }}" class="btn btn-sm btn-outline-secondary">
        <i class="mdi mdi-arrow-left"></i> Back
    </a>
    <h5 class="mb-0">Player Detail</h5>
</div>

<div class="row g-4">

    {{-- Profile Card --}}
    <div class="col-12 col-lg-4">
        <div class="card h-100">
            <div class="card-body text-center pt-4">
                @if($player->profile_image)
                    <img src="{{ asset('storage/' . $player->profile_image) }}" alt="{{ $player->name }}"
                         class="rounded-circle mb-3" style="width:90px;height:90px;object-fit:cover;">
                @else
                    <div class="rounded-circle d-flex align-items-center justify-content-center bg-label-success mx-auto mb-3"
                         style="width:90px;height:90px;">
                        <i class="mdi mdi-account" style="font-size:40px;"></i>
                    </div>
                @endif

                <h5 class="mb-1">{{ $player->name }}</h5>
                <p class="text-muted small mb-1">{{ $player->email }}</p>
                <p class="text-muted small mb-3">{{ $player->phone ?? '—' }}</p>

                @php
                    $statusMap = [
                        'active'             => ['bg-label-success',   'Active'],
                        'profile_incomplete' => ['bg-label-warning',   'Profile Incomplete'],
                        'otp_pending'        => ['bg-label-secondary', 'OTP Pending'],
                        'suspended'          => ['bg-label-dark',      'Suspended'],
                    ];
                    [$badgeClass, $badgeLabel] = $statusMap[$player->status] ?? ['bg-label-secondary', ucfirst($player->status)];
                @endphp
                <span class="badge {{ $badgeClass }} mb-3">{{ $badgeLabel }}</span>

                <div class="d-flex justify-content-center gap-2 flex-wrap">
                    <a href="{{ route('admin.players.edit', $player) }}" class="btn btn-sm btn-dark">
                        <i class="mdi mdi-pencil-outline"></i> Edit
                    </a>
                    @include('admin.components.action-buttons', [
                        'type'       => 'delete',
                        'formAction' => route('admin.players.destroy', $player),
                        'confirm'    => "Delete player \"{$player->name}\"? This cannot be undone.",
                    ])
                </div>
            </div>
        </div>
    </div>

    {{-- Details --}}
    <div class="col-12 col-lg-8">
        <div class="card mb-4">
            <div class="card-header"><h6 class="mb-0">Player Information</h6></div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-6"><p class="text-muted small mb-1">Date of Birth</p><p class="fw-semibold mb-0">{{ $player->dob?->format('d M Y') ?? '—' }}</p></div>
                    <div class="col-6"><p class="text-muted small mb-1">Gender</p><p class="fw-semibold mb-0">{{ $player->gender ? ucfirst($player->gender) : '—' }}</p></div>
                    <div class="col-6"><p class="text-muted small mb-1">City</p><p class="fw-semibold mb-0">{{ $player->city ?? '—' }}</p></div>
                    <div class="col-6"><p class="text-muted small mb-1">Playing Level</p><p class="fw-semibold mb-0">{{ $player->playing_level ? ucfirst($player->playing_level) : '—' }}</p></div>
                    <div class="col-6"><p class="text-muted small mb-1">Primary Hand</p><p class="fw-semibold mb-0">{{ $player->primary_hand ? ucfirst($player->primary_hand) : '—' }}</p></div>
                    <div class="col-6"><p class="text-muted small mb-1">OTP Verified</p><p class="fw-semibold mb-0">{{ $player->otp_verified ? 'Yes' : 'No' }}</p></div>
                    <div class="col-6"><p class="text-muted small mb-1">Registered</p><p class="fw-semibold mb-0">{{ $player->created_at->format('d M Y') }}</p></div>
                    @if($player->bio)
                        <div class="col-12"><p class="text-muted small mb-1">Bio</p><p class="mb-0">{{ $player->bio }}</p></div>
                    @endif
                </div>
            </div>
        </div>

        {{-- Update Status --}}
        <div class="card">
            <div class="card-header"><h6 class="mb-0">Update Status</h6></div>
            <div class="card-body">
                <form action="{{ route('admin.players.status', $player) }}" method="POST" class="d-flex align-items-center gap-3 flex-wrap">
                    @csrf @method('PATCH')
                    <select name="status" class="form-select form-select-sm" style="max-width:220px;">
                        @foreach(['otp_pending' => 'OTP Pending', 'profile_incomplete' => 'Profile Incomplete', 'active' => 'Active', 'suspended' => 'Suspended'] as $val => $lbl)
                            <option value="{{ $val }}" @selected($player->status === $val)>{{ $lbl }}</option>
                        @endforeach
                    </select>
                    <button type="submit" class="btn btn-sm btn-dark">Update Status</button>
                </form>
            </div>
        </div>
    </div>

</div>
@endsection
