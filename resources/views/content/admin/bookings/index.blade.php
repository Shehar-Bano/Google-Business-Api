@extends('layouts/contentNavbarLayout')

@section('title', 'Bookings')

@section('content')
<div class="admin-page bookings-page">

    <div class="admin-page-header">
        <div class="admin-page-header__left">
            <h4 class="admin-page-header__title">Bookings</h4>
            <p class="admin-page-header__subtitle">Track court bookings, payments, and booking state across the platform.</p>
        </div>
    </div>

    <div class="booking-stats-grid">
        <div class="booking-stat-card">
            <div class="booking-stat-card__icon booking-stat-card__icon--primary"><i class="mdi mdi-calendar-multiple"></i></div>
            <div>
                <div class="booking-stat-card__label">Total Bookings</div>
                <div class="booking-stat-card__value">{{ number_format($stats['total_bookings']) }}</div>
            </div>
        </div>
        <div class="booking-stat-card">
            <div class="booking-stat-card__icon booking-stat-card__icon--warning"><i class="mdi mdi-clock-outline"></i></div>
            <div>
                <div class="booking-stat-card__label">Pending</div>
                <div class="booking-stat-card__value">{{ number_format($stats['pending_bookings']) }}</div>
            </div>
        </div>
        <div class="booking-stat-card">
            <div class="booking-stat-card__icon booking-stat-card__icon--success"><i class="mdi mdi-check-circle-outline"></i></div>
            <div>
                <div class="booking-stat-card__label">Confirmed</div>
                <div class="booking-stat-card__value">{{ number_format($stats['confirmed_bookings']) }}</div>
            </div>
        </div>
        <div class="booking-stat-card">
            <div class="booking-stat-card__icon booking-stat-card__icon--danger"><i class="mdi mdi-close-circle-outline"></i></div>
            <div>
                <div class="booking-stat-card__label">Cancelled</div>
                <div class="booking-stat-card__value">{{ number_format($stats['cancelled_bookings']) }}</div>
            </div>
        </div>
    </div>

    <div class="booking-stats-grid booking-stats-grid--single">
        <div class="booking-stat-card booking-stat-card--wide">
            <div class="booking-stat-card__icon booking-stat-card__icon--accent"><i class="mdi mdi-cash-multiple"></i></div>
            <div>
                <div class="booking-stat-card__label">Today Bookings</div>
                <div class="booking-stat-card__value">{{ number_format($stats['today_bookings']) }}</div>
            </div>
        </div>
        <div class="booking-stat-card booking-stat-card--wide">
            <div class="booking-stat-card__icon booking-stat-card__icon--primary"><i class="mdi mdi-currency-usd"></i></div>
            <div>
                <div class="booking-stat-card__label">Revenue (Paid)</div>
                <div class="booking-stat-card__value">PKR {{ number_format((float) $stats['total_revenue'], 0) }}</div>
            </div>
        </div>
    </div>

    @component('admin.components.datatable', [
        'title' => 'Booking Ledger',
        'subtitle' => 'Search by player, club, court, booking status, or date.',
        'paginator' => $bookings,
        'search' => $search,
        'perPage' => $perPage,
        'sort' => $sort,
        'direction' => $direction,
        'filters' => [
            [
                'name' => 'status',
                'label' => 'Booking Status',
                'value' => $status,
                'options' => [
                    '' => 'All Statuses',
                    'pending' => 'Pending',
                    'confirmed' => 'Confirmed',
                    'cancelled' => 'Cancelled',
                    'completed' => 'Completed',
                    'failed' => 'Failed',
                ],
            ],
            [
                'name' => 'payment_status',
                'label' => 'Payment',
                'value' => $payment,
                'options' => [
                    '' => 'All Payments',
                    'pending' => 'Pending',
                    'paid' => 'Paid',
                    'failed' => 'Failed',
                    'refunded' => 'Refunded',
                ],
            ],
            [
                'name' => 'date',
                'label' => 'Date',
                'value' => $date,
                'type' => 'date',
            ],
            [
                'name' => 'club_id',
                'label' => 'Club',
                'value' => $clubId,
                'options' => ['' => 'All Clubs'] + $clubs->mapWithKeys(fn ($club) => [$club->id => $club->club_name ?? $club->name])->all(),
            ],
            [
                'name' => 'court_id',
                'label' => 'Court',
                'value' => $courtId,
                'options' => ['' => 'All Courts'] + $courts->mapWithKeys(fn ($court) => [$court->id => $court->name])->all(),
            ],
        ],
        'columns' => [
            ['label' => 'Booking', 'field' => 'id', 'sortable' => true],
            ['label' => 'Player', 'sortable' => false],
            ['label' => 'Club', 'sortable' => false],
            ['label' => 'Court', 'sortable' => false],
            ['label' => 'Date', 'field' => 'booking_date', 'sortable' => true],
            ['label' => 'Time', 'field' => 'start_time', 'sortable' => true],
            ['label' => 'Price', 'field' => 'total_amount', 'sortable' => true],
            ['label' => 'Status', 'field' => 'booking_status', 'sortable' => true],
            ['label' => 'Payment', 'field' => 'payment_status', 'sortable' => true],
            ['label' => 'Actions', 'actions' => true],
        ],
    ])
        @forelse($bookings as $booking)
            @php
                $statusMap = [
                    'pending' => ['bg-label-warning', 'Pending'],
                    'confirmed' => ['bg-label-success', 'Confirmed'],
                    'cancelled' => ['bg-label-danger', 'Cancelled'],
                    'completed' => ['bg-label-primary', 'Completed'],
                    'failed' => ['bg-label-secondary', 'Failed'],
                ];
                [$statusBadgeClass, $statusBadgeLabel] = $statusMap[$booking->booking_status] ?? ['bg-label-secondary', ucfirst($booking->booking_status)];

                $paymentMap = [
                    'pending' => ['bg-label-warning', 'Pending'],
                    'paid' => ['bg-label-success', 'Paid'],
                    'failed' => ['bg-label-danger', 'Failed'],
                    'refunded' => ['bg-label-secondary', 'Refunded'],
                ];
                [$paymentBadgeClass, $paymentBadgeLabel] = $paymentMap[$booking->payment_status] ?? ['bg-label-secondary', ucfirst($booking->payment_status)];
            @endphp
            <tr>
                <td class="cell-primary">#{{ $booking->id }}</td>
                <td>
                    <div class="cell-primary">{{ $booking->player?->name ?? '—' }}</div>
                    <div class="cell-muted">{{ $booking->player?->phone ?? $booking->player?->email ?? '—' }}</div>
                </td>
                <td>
                    <div class="cell-primary">{{ $booking->club?->club_name ?? $booking->club?->name ?? '—' }}</div>
                    <div class="cell-muted">{{ $booking->club?->city ?? '—' }}</div>
                </td>
                <td>
                    <div class="cell-primary">{{ $booking->court?->name ?? '—' }}</div>
                    <div class="cell-muted">{{ \Illuminate\Support\Str::headline((string) $booking->court?->type) }}</div>
                </td>
                <td class="cell-muted">{{ $booking->booking_date?->format('Y-m-d') }}</td>
                <td>{{ substr((string) $booking->start_time, 0, 5) }} - {{ substr((string) $booking->end_time, 0, 5) }}</td>
                <td>PKR {{ number_format((float) $booking->total_amount, 0) }}</td>
                <td><span class="badge {{ $statusBadgeClass }}">{{ $statusBadgeLabel }}</span></td>
                <td><span class="badge {{ $paymentBadgeClass }}">{{ $paymentBadgeLabel }}</span></td>
                <td class="text-end">
                    @include('admin.components.action-buttons', [
                        'type' => 'view',
                        'href' => route('admin.bookings.show', $booking),
                        'title' => 'View Booking',
                    ])
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="10" class="admin-empty-state">No bookings found.</td>
            </tr>
        @endforelse
    @endcomponent

</div>
@endsection

@push('my-styles')
<style>
    .booking-stats-grid {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 1rem;
    }

    .booking-stats-grid--single {
        grid-template-columns: repeat(2, minmax(0, 1fr));
    }

    .booking-stat-card {
        background: linear-gradient(180deg, #ffffff 0%, #f9fbfc 100%);
        border: 1px solid #e6ebf2;
        border-radius: 18px;
        padding: 1rem 1.1rem;
        display: flex;
        align-items: center;
        gap: 0.9rem;
        box-shadow: 0 10px 24px rgba(15, 23, 42, 0.05);
    }

    .booking-stat-card__icon {
        width: 48px;
        height: 48px;
        border-radius: 14px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 24px;
        flex-shrink: 0;
    }

    .booking-stat-card__icon--primary { background: #eef2ff; color: #4f46e5; }
    .booking-stat-card__icon--success { background: #ecfdf5; color: #059669; }
    .booking-stat-card__icon--warning { background: #fff7ed; color: #f97316; }
    .booking-stat-card__icon--danger { background: #fef2f2; color: #dc2626; }
    .booking-stat-card__icon--accent { background: #fdf2f8; color: #db2777; }

    .booking-stat-card__label {
        font-size: 0.75rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.06em;
        color: #64748b;
        margin-bottom: 0.2rem;
    }

    .booking-stat-card__value {
        font-size: 1.5rem;
        font-weight: 800;
        color: #0f172a;
        line-height: 1.1;
    }

    @media (max-width: 1199px) {
        .booking-stats-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
    }

    @media (max-width: 767px) {
        .booking-stats-grid,
        .booking-stats-grid--single {
            grid-template-columns: 1fr;
        }
    }
</style>
@endpush
