@extends('layouts/contentNavbarLayout')

@section('title', 'Booking Detail')

@section('content')
<div class="admin-page">
    <div class="admin-page-header">
        <div class="admin-page-header__left">
            <a href="{{ route('admin.bookings.index') }}" class="btn btn-sm btn-outline-secondary mb-3">
                <i class="mdi mdi-arrow-left me-1"></i> Back
            </a>
            <h4 class="admin-page-header__title">Booking #{{ $booking->id }}</h4>
            <p class="admin-page-header__subtitle">{{ $booking->club?->club_name ?? '—' }} · {{ $booking->court?->name ?? '—' }}</p>
        </div>
        <div class="admin-page-header__actions">
            @php
                $statusMap = [
                    'pending' => ['bg-label-warning', 'Pending'],
                    'confirmed' => ['bg-label-success', 'Confirmed'],
                    'cancelled' => ['bg-label-danger', 'Cancelled'],
                    'completed' => ['bg-label-primary', 'Completed'],
                    'failed' => ['bg-label-secondary', 'Failed'],
                ];
                [$badgeClass, $badgeLabel] = $statusMap[$booking->booking_status] ?? ['bg-label-secondary', ucfirst($booking->booking_status)];
            @endphp
            <span class="badge {{ $badgeClass }}">{{ $badgeLabel }}</span>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-12 col-lg-4">
            <div class="card h-100">
                <div class="card-header">
                    <h6 class="mb-0">Booking Summary</h6>
                </div>
                <div class="card-body">
                    <div class="detail-stack detail-stack--single">
                        <div><span class="text-muted small d-block">Booking Date</span><strong>{{ $booking->booking_date?->format('Y-m-d') }}</strong></div>
                        <div><span class="text-muted small d-block">Time</span><strong>{{ substr((string) $booking->start_time, 0, 5) }} - {{ substr((string) $booking->end_time, 0, 5) }}</strong></div>
                        <div><span class="text-muted small d-block">Court Price</span><strong>PKR {{ number_format((float) $booking->court_price, 0) }}</strong></div>
                        <div><span class="text-muted small d-block">Service Fee</span><strong>PKR {{ number_format((float) $booking->service_fee, 0) }}</strong></div>
                        <div><span class="text-muted small d-block">Total Amount</span><strong>PKR {{ number_format((float) $booking->total_amount, 0) }}</strong></div>
                        <div><span class="text-muted small d-block">Payment Status</span><strong>{{ \Illuminate\Support\Str::headline($booking->payment_status) }}</strong></div>
                        <div><span class="text-muted small d-block">Created At</span><strong>{{ $booking->created_at?->format('Y-m-d H:i') }}</strong></div>
                        <div><span class="text-muted small d-block">Updated At</span><strong>{{ $booking->updated_at?->format('Y-m-d H:i') }}</strong></div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12 col-lg-8">
            <div class="card mb-4">
                <div class="card-header">
                    <h6 class="mb-0">Club / Court / Player</h6>
                </div>
                <div class="card-body">
                    <div class="row g-4">
                        <div class="col-md-4">
                            <div class="booking-person-card">
                                @if($booking->club?->club_logo)
                                    <img src="{{ asset('storage/' . $booking->club->club_logo) }}" alt="{{ $booking->club?->club_name }}" class="booking-person-card__avatar">
                                @else
                                    <div class="booking-person-card__avatar booking-person-card__avatar--fallback"><i class="mdi mdi-domain"></i></div>
                                @endif
                                <div class="mt-3">
                                    <div class="text-muted small">Club</div>
                                    <div class="fw-semibold">{{ $booking->club?->club_name ?? '—' }}</div>
                                    <div class="text-muted small">{{ $booking->club?->city ?? '—' }}</div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="booking-person-card">
                                <div class="booking-person-card__avatar booking-person-card__avatar--court">
                                    <i class="mdi mdi-tennis"></i>
                                </div>
                                <div class="mt-3">
                                    <div class="text-muted small">Court</div>
                                    <div class="fw-semibold">{{ $booking->court?->name ?? '—' }}</div>
                                    <div class="text-muted small">{{ \Illuminate\Support\Str::headline((string) $booking->court?->type) }}</div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="booking-person-card">
                                @if($booking->player?->profile_image)
                                    <img src="{{ asset('storage/' . $booking->player->profile_image) }}" alt="{{ $booking->player?->name }}" class="booking-person-card__avatar">
                                @else
                                    <div class="booking-person-card__avatar booking-person-card__avatar--player"><i class="mdi mdi-account"></i></div>
                                @endif
                                <div class="mt-3">
                                    <div class="text-muted small">Player</div>
                                    <div class="fw-semibold">{{ $booking->player?->name ?? '—' }}</div>
                                    <div class="text-muted small">{{ $booking->player?->phone ?? $booking->player?->email ?? '—' }}</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <h6 class="mb-0">Status</h6>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.bookings.status', $booking) }}" method="POST" class="row g-3 align-items-end">
                        @csrf
                        <div class="col-md-8">
                            <label class="form-label">Update Booking Status</label>
                            <select name="booking_status" class="form-select">
                                @foreach(['pending' => 'Pending', 'confirmed' => 'Confirmed', 'cancelled' => 'Cancelled', 'completed' => 'Completed', 'failed' => 'Failed'] as $val => $label)
                                    <option value="{{ $val }}" @selected($booking->booking_status === $val)>{{ $label }}</option>
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
    .detail-stack {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 1rem;
    }

    .detail-stack--single {
        grid-template-columns: 1fr;
    }

    .booking-person-card {
        background: linear-gradient(180deg, #ffffff 0%, #f9fbfc 100%);
        border: 1px solid #e6ebf2;
        border-radius: 18px;
        padding: 1rem;
        text-align: center;
        height: 100%;
    }

    .booking-person-card__avatar {
        width: 72px;
        height: 72px;
        border-radius: 18px;
        object-fit: cover;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 34px;
        background: #eef2ff;
        color: #4f46e5;
    }

    .booking-person-card__avatar--fallback {
        background: #ecfdf5;
        color: #059669;
    }

    .booking-person-card__avatar--court {
        background: #fff7ed;
        color: #f97316;
    }

    .booking-person-card__avatar--player {
        background: #fdf2f8;
        color: #db2777;
    }

    @media (max-width: 767px) {
        .detail-stack {
            grid-template-columns: 1fr;
        }
    }
</style>
@endpush
