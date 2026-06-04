@extends('layouts/contentNavbarLayout')

@section('title', 'Tournament Detail')

@section('content')
<div class="admin-page">
    <div class="admin-page-header">
        <div class="admin-page-header__left">
            <a href="{{ route('admin.tournaments.index') }}" class="btn btn-sm btn-outline-secondary mb-3">
                <i class="mdi mdi-arrow-left me-1"></i> Back
            </a>
            <h4 class="admin-page-header__title">{{ $tournament->name }}</h4>
            <p class="admin-page-header__subtitle">{{ $tournament->club?->club_name ?? '—' }} · {{ \Illuminate\Support\Str::headline((string) $tournament->format) }}</p>
        </div>
        <div class="admin-page-header__actions">
            @php
                $statusMap = [
                    'open' => ['bg-label-success', 'Open'],
                    'full' => ['bg-label-warning', 'Full'],
                    'closed' => ['bg-label-secondary', 'Closed'],
                    'completed' => ['bg-label-primary', 'Completed'],
                    'cancelled' => ['bg-label-danger', 'Cancelled'],
                ];
                [$badgeClass, $badgeLabel] = $statusMap[$tournament->status] ?? ['bg-label-secondary', ucfirst($tournament->status)];
            @endphp
            <span class="badge {{ $badgeClass }}">{{ $badgeLabel }}</span>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-12 col-lg-4">
            <div class="card h-100">
                <div class="card-header"><h6 class="mb-0">Tournament Snapshot</h6></div>
                <div class="card-body text-center">
                    @if($tournament->tournament_image)
                        <img src="{{ str_starts_with($tournament->tournament_image, 'http') ? $tournament->tournament_image : asset('storage/' . $tournament->tournament_image) }}" alt="{{ $tournament->name }}" class="tournament-cover mb-3">
                    @else
                        <div class="tournament-cover tournament-cover--fallback mb-3"><i class="mdi mdi-trophy-outline"></i></div>
                    @endif
                    <h5 class="mb-1">{{ $tournament->name }}</h5>
                    <p class="text-muted small mb-3">Tournament #{{ $tournament->id }}</p>
                    <div class="detail-stack detail-stack--single text-start">
                        <div><span class="text-muted small d-block">Start Date</span><strong>{{ $tournament->start_date?->format('Y-m-d') }}</strong></div>
                        <div><span class="text-muted small d-block">End Date</span><strong>{{ $tournament->end_date?->format('Y-m-d') }}</strong></div>
                        <div><span class="text-muted small d-block">Registration Deadline</span><strong>{{ $tournament->registration_deadline?->format('Y-m-d') }}</strong></div>
                        <div><span class="text-muted small d-block">Players</span><strong>{{ (int) $tournament->registered_players_count }}/{{ (int) $tournament->allowed_player }}</strong></div>
                        <div><span class="text-muted small d-block">Entry Fees</span><strong>PKR {{ number_format((float) $tournament->entry_fees, 0) }}</strong></div>
                        <div><span class="text-muted small d-block">Prize Pool</span><strong>PKR {{ number_format((float) $tournament->prize_pool, 0) }}</strong></div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12 col-lg-8">
            <div class="card mb-4">
                <div class="card-header"><h6 class="mb-0">Club Information</h6></div>
                <div class="card-body">
                    <div class="d-flex align-items-center gap-3 mb-4">
                        @if($tournament->club?->club_logo)
                            <img src="{{ asset('storage/' . $tournament->club->club_logo) }}" alt="{{ $tournament->club?->club_name }}" class="tournament-club-logo">
                        @else
                            <div class="tournament-club-logo tournament-club-logo--fallback"><i class="mdi mdi-domain"></i></div>
                        @endif
                        <div>
                            <h6 class="mb-1">{{ $tournament->club?->club_name ?? '—' }}</h6>
                            <p class="mb-0 text-muted small">{{ $tournament->club?->city ?? '—' }}</p>
                        </div>
                    </div>

                    <div class="row g-3">
                        <div class="col-md-4"><p class="text-muted small mb-1">Manager</p><p class="fw-semibold mb-0">{{ $tournament->club?->name ?? '—' }}</p></div>
                        <div class="col-md-4"><p class="text-muted small mb-1">Email</p><p class="fw-semibold mb-0">{{ $tournament->club?->email ?? '—' }}</p></div>
                        <div class="col-md-4"><p class="text-muted small mb-1">Phone</p><p class="fw-semibold mb-0">{{ $tournament->club?->phone ?? '—' }}</p></div>
                        <div class="col-12"><p class="text-muted small mb-1">Address</p><p class="fw-semibold mb-0">{{ $tournament->club?->address ?? '—' }}</p></div>
                    </div>
                </div>
            </div>

            <div class="card mb-4">
                <div class="card-header"><h6 class="mb-0">Rules</h6></div>
                <div class="card-body">
                    <p class="mb-0">{{ $tournament->rules ?? 'No rules added.' }}</p>
                </div>
            </div>

            <div class="card">
                <div class="card-header"><h6 class="mb-0">Status</h6></div>
                <div class="card-body">
                    <form action="{{ route('admin.tournaments.status', $tournament) }}" method="POST" class="row g-3 align-items-end">
                        @csrf
                        <div class="col-md-8">
                            <label class="form-label">Update Tournament Status</label>
                            <select name="status" class="form-select">
                                @foreach(['open' => 'Open', 'full' => 'Full', 'closed' => 'Closed', 'completed' => 'Completed', 'cancelled' => 'Cancelled'] as $val => $label)
                                    <option value="{{ $val }}" @selected($tournament->status === $val)>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4">
                            <button class="btn btn-dark w-100" type="submit">
                                <i class="mdi mdi-content-save-outline me-1"></i> Save Status
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('my-styles')
<style>
    .detail-stack { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 1rem; }
    .detail-stack--single { grid-template-columns: 1fr; }
    .tournament-cover {
        width: 100%;
        max-width: 320px;
        height: 180px;
        border-radius: 16px;
        object-fit: cover;
        border: 1px solid #e6ebf2;
    }
    .tournament-cover--fallback {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        background: #eef2ff;
        color: #4f46e5;
        font-size: 42px;
    }
    .tournament-club-logo,
    .tournament-club-logo--fallback {
        width: 60px;
        height: 60px;
        border-radius: 16px;
        object-fit: cover;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        background: #ecfdf5;
        color: #059669;
        font-size: 28px;
    }
    @media (max-width: 767px) { .detail-stack { grid-template-columns: 1fr; } }
</style>
@endpush
