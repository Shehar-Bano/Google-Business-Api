@extends('layouts/contentNavbarLayout')

@section('title', 'Court Detail')

@section('content')
<div class="admin-page">
    <div class="admin-page-header">
        <div class="admin-page-header__left">
            <a href="{{ route('admin.courts.index') }}" class="btn btn-sm btn-outline-secondary mb-3">
                <i class="mdi mdi-arrow-left me-1"></i> Back
            </a>
            <h4 class="admin-page-header__title">{{ $court->name }}</h4>
            <p class="admin-page-header__subtitle">{{ $court->club?->club_name ?? '—' }} · {{ \Illuminate\Support\Str::headline((string) $court->type) }}</p>
        </div>
        <div class="admin-page-header__actions">
            <a href="{{ route('admin.courts.edit', $court) }}" class="btn btn-dark">
                <i class="mdi mdi-pencil-outline me-1"></i> Edit Court
            </a>
            @include('admin.components.action-buttons', [
                'type' => 'delete',
                'formAction' => route('admin.courts.destroy', $court),
                'confirm' => "Delete court \"{$court->name}\"?",
            ])
        </div>
    </div>

    @php
        $statusMap = [
            'active' => ['bg-label-success', 'Active'],
            'maintenance' => ['bg-label-warning', 'Maintenance'],
            'inactive' => ['bg-label-secondary', 'Inactive'],
        ];
        [$badgeClass, $badgeLabel] = $statusMap[$court->status] ?? ['bg-label-secondary', ucfirst($court->status)];
    @endphp

    <div class="row g-4">
        <div class="col-12 col-xl-4">
            <div class="card h-100">
                <div class="card-header">
                    <h6 class="mb-0">Court Snapshot</h6>
                </div>
                <div class="card-body text-center">
                    <div class="court-snapshot-icon mx-auto mb-3">
                        <i class="mdi mdi-tennis"></i>
                    </div>
                    <h5 class="mb-1">{{ $court->name }}</h5>
                    <p class="text-muted small mb-3">Court #{{ $court->id }}</p>
                    <span class="badge {{ $badgeClass }} mb-4">{{ $badgeLabel }}</span>

                    <div class="court-mini-stats">
                        <div>
                            <div class="text-muted small">Today Bookings</div>
                            <div class="fw-bold fs-5">{{ number_format($metrics['today_bookings']) }}</div>
                        </div>
                        <div>
                            <div class="text-muted small">Upcoming</div>
                            <div class="fw-bold fs-5">{{ number_format($metrics['upcoming_bookings']) }}</div>
                        </div>
                        <div>
                            <div class="text-muted small">Total</div>
                            <div class="fw-bold fs-5">{{ number_format($metrics['total_bookings']) }}</div>
                        </div>
                        <div>
                            <div class="text-muted small">Price / Hour</div>
                            <div class="fw-bold fs-5">PKR {{ number_format((float) $court->price_per_hour, 0) }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12 col-xl-8">
            <div class="card mb-4">
                <div class="card-header">
                    <h6 class="mb-0">Court Details</h6>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-6">
                            <p class="text-muted small mb-1">Club</p>
                            <p class="fw-semibold mb-0">{{ $court->club?->club_name ?? '—' }}</p>
                        </div>
                        <div class="col-6">
                            <p class="text-muted small mb-1">Type</p>
                            <p class="fw-semibold mb-0">{{ \Illuminate\Support\Str::headline((string) $court->type) }}</p>
                        </div>
                        <div class="col-6">
                            <p class="text-muted small mb-1">Capacity</p>
                            <p class="fw-semibold mb-0">{{ $court->capacity ?? '—' }}</p>
                        </div>
                        <div class="col-6">
                            <p class="text-muted small mb-1">Status</p>
                            <p class="fw-semibold mb-0">{{ \Illuminate\Support\Str::headline($court->status) }}</p>
                        </div>
                        <div class="col-12">
                            <p class="text-muted small mb-1">Description</p>
                            <p class="fw-semibold mb-0">{{ $court->description ?? '—' }}</p>
                        </div>
                        <div class="col-12">
                            <p class="text-muted small mb-1">Maintenance Note</p>
                            <p class="fw-semibold mb-0">{{ $court->maintenance_note ?? '—' }}</p>
                        </div>
                        <div class="col-6">
                            <p class="text-muted small mb-1">Created At</p>
                            <p class="fw-semibold mb-0">{{ $court->created_at?->format('Y-m-d H:i') }}</p>
                        </div>
                        <div class="col-6">
                            <p class="text-muted small mb-1">Updated At</p>
                            <p class="fw-semibold mb-0">{{ $court->updated_at?->format('Y-m-d H:i') }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card mb-4">
                <div class="card-header">
                    <h6 class="mb-0">Club Information</h6>
                </div>
                <div class="card-body">
                    <div class="d-flex align-items-center gap-3 mb-4">
                        @if($court->club?->club_logo)
                            <img src="{{ asset('storage/' . $court->club->club_logo) }}" alt="{{ $court->club?->club_name }}" class="court-club-logo">
                        @else
                            <div class="court-club-logo court-club-logo--fallback">
                                <i class="mdi mdi-domain"></i>
                            </div>
                        @endif
                        <div>
                            <h6 class="mb-1">{{ $court->club?->club_name ?? '—' }}</h6>
                            <p class="mb-0 text-muted small">{{ $court->club?->city ?? '—' }}</p>
                        </div>
                    </div>

                    <div class="row g-3">
                        <div class="col-md-4">
                            <p class="text-muted small mb-1">Email</p>
                            <p class="fw-semibold mb-0">{{ $court->club?->email ?? '—' }}</p>
                        </div>
                        <div class="col-md-4">
                            <p class="text-muted small mb-1">Phone</p>
                            <p class="fw-semibold mb-0">{{ $court->club?->phone ?? '—' }}</p>
                        </div>
                        <div class="col-md-4">
                            <p class="text-muted small mb-1">Address</p>
                            <p class="fw-semibold mb-0">{{ $court->club?->address ?? '—' }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <h6 class="mb-0">Latest Bookings</h6>
                </div>
                <div class="card-body p-0">
                    @if($latestBookings->isEmpty())
                        <p class="text-center text-muted py-4 mb-0">No bookings found for this court.</p>
                    @else
                        <div class="table-responsive">
                            <table class="table admin-datatable mb-0">
                                <thead>
                                    <tr>
                                        <th>Booking ID</th>
                                        <th>Player</th>
                                        <th>Date</th>
                                        <th>Time</th>
                                        <th>Status</th>
                                        <th>Payment</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($latestBookings as $booking)
                                        <tr>
                                            <td class="cell-primary">#{{ $booking->id }}</td>
                                            <td>{{ $booking->player?->name ?? '—' }}</td>
                                            <td class="cell-muted">{{ $booking->booking_date?->format('Y-m-d') }}</td>
                                            <td>{{ substr((string) $booking->start_time, 0, 5) }} - {{ substr((string) $booking->end_time, 0, 5) }}</td>
                                            <td>
                                                <span class="badge {{ $booking->booking_status === 'confirmed' ? 'bg-label-success' : ($booking->booking_status === 'cancelled' ? 'bg-label-danger' : 'bg-label-warning') }}">
                                                    {{ \Illuminate\Support\Str::headline($booking->booking_status) }}
                                                </span>
                                            </td>
                                            <td>
                                                <span class="badge {{ $booking->payment_status === 'paid' ? 'bg-label-success' : 'bg-label-secondary' }}">
                                                    {{ \Illuminate\Support\Str::headline($booking->payment_status) }}
                                                </span>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('my-styles')
<style>
    .court-snapshot-icon,
    .court-club-logo,
    .court-club-logo--fallback {
        width: 64px;
        height: 64px;
        border-radius: 18px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 32px;
        background: #eef2ff;
        color: #4f46e5;
        object-fit: cover;
    }

    .court-club-logo {
        object-fit: cover;
    }

    .court-club-logo--fallback {
        background: #ecfdf5;
        color: #059669;
    }

    .court-mini-stats {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 1rem;
        text-align: left;
        margin-top: 1rem;
    }

    @media (max-width: 767px) {
        .court-mini-stats {
            grid-template-columns: 1fr;
        }
    }
</style>
@endpush
