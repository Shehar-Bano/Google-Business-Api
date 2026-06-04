@extends('layouts/contentNavbarLayout')

@section('title', 'Tournaments')

@section('content')
<div class="admin-page tournaments-page">
    <div class="admin-page-header">
        <div class="admin-page-header__left">
            <h4 class="admin-page-header__title">Tournaments</h4>
            <p class="admin-page-header__subtitle">Monitor tournament schedules, capacity, registration, and status.</p>
        </div>
    </div>

    <div class="tournament-stats-grid">
        <div class="tournament-stat-card">
            <div class="tournament-stat-card__icon tournament-stat-card__icon--primary"><i class="mdi mdi-trophy-outline"></i></div>
            <div>
                <div class="tournament-stat-card__label">Total</div>
                <div class="tournament-stat-card__value">{{ number_format($stats['total_tournaments']) }}</div>
            </div>
        </div>
        <div class="tournament-stat-card">
            <div class="tournament-stat-card__icon tournament-stat-card__icon--success"><i class="mdi mdi-lock-open-outline"></i></div>
            <div>
                <div class="tournament-stat-card__label">Open</div>
                <div class="tournament-stat-card__value">{{ number_format($stats['open_tournaments']) }}</div>
            </div>
        </div>
        <div class="tournament-stat-card">
            <div class="tournament-stat-card__icon tournament-stat-card__icon--warning"><i class="mdi mdi-account-multiple-plus-outline"></i></div>
            <div>
                <div class="tournament-stat-card__label">Full</div>
                <div class="tournament-stat-card__value">{{ number_format($stats['full_tournaments']) }}</div>
            </div>
        </div>
        <div class="tournament-stat-card">
            <div class="tournament-stat-card__icon tournament-stat-card__icon--accent"><i class="mdi mdi-check-decagram-outline"></i></div>
            <div>
                <div class="tournament-stat-card__label">Completed</div>
                <div class="tournament-stat-card__value">{{ number_format($stats['completed_tournaments']) }}</div>
            </div>
        </div>
    </div>

    <div class="tournament-stats-grid tournament-stats-grid--secondary">
        <div class="tournament-stat-card tournament-stat-card--wide">
            <div class="tournament-stat-card__icon tournament-stat-card__icon--danger"><i class="mdi mdi-close-circle-outline"></i></div>
            <div>
                <div class="tournament-stat-card__label">Cancelled</div>
                <div class="tournament-stat-card__value">{{ number_format($stats['cancelled_tournaments']) }}</div>
            </div>
        </div>
        <div class="tournament-stat-card tournament-stat-card--wide">
            <div class="tournament-stat-card__icon tournament-stat-card__icon--primary"><i class="mdi mdi-account-group-outline"></i></div>
            <div>
                <div class="tournament-stat-card__label">Registered Players</div>
                <div class="tournament-stat-card__value">{{ number_format($stats['total_registered_players']) }}</div>
            </div>
        </div>
        <div class="tournament-stat-card tournament-stat-card--wide">
            <div class="tournament-stat-card__icon tournament-stat-card__icon--success"><i class="mdi mdi-cash-multiple"></i></div>
            <div>
                <div class="tournament-stat-card__label">Prize Pool</div>
                <div class="tournament-stat-card__value"> {{ number_format($stats['total_prize_pool'], 0) }}</div>
            </div>
        </div>
    </div>

    @component('admin.components.datatable', [
        'title' => 'Tournament Registry',
        'subtitle' => 'Search by tournament, club, format, and schedule date.',
        'paginator' => $tournaments,
        'search' => $search,
        'perPage' => $perPage,
        'sort' => $sort,
        'direction' => $direction,
        'filters' => [
            [
                'name' => 'status',
                'label' => 'Status',
                'value' => $status,
                'options' => [
                    '' => 'All Statuses',
                    'open' => 'Open',
                    'full' => 'Full',
                    'closed' => 'Closed',
                    'completed' => 'Completed',
                    'cancelled' => 'Cancelled',
                ],
            ],
            [
                'name' => 'format',
                'label' => 'Format',
                'value' => $format,
                'options' => [
                    '' => 'All Formats',
                    'knockout' => 'Knockout',
                    'league' => 'League',
                    'round_robin' => 'Round Robin',
                    'other' => 'Other',
                ],
            ],
            [
                'name' => 'start_date',
                'label' => 'Start Date',
                'value' => $startDate,
                'type' => 'date',
            ],
            [
                'name' => 'club_id',
                'label' => 'Club',
                'value' => $clubId,
                'options' => ['' => 'All Clubs'] + $clubs->mapWithKeys(fn ($club) => [$club->id => $club->club_name ?? $club->name])->all(),
            ],
        ],
        'columns' => [
            ['label' => 'Tournament', 'field' => 'name', 'sortable' => true],
            ['label' => 'Club', 'sortable' => false],
            ['label' => 'Format', 'field' => 'format', 'sortable' => true],
            ['label' => 'Start', 'field' => 'start_date', 'sortable' => true],
            ['label' => 'Deadline', 'field' => 'registration_deadline', 'sortable' => true],
            ['label' => 'Players', 'sortable' => false],
            ['label' => 'Entry Fees', 'field' => 'entry_fees', 'sortable' => true],
            ['label' => 'Prize Pool', 'field' => 'prize_pool', 'sortable' => true],
            ['label' => 'Status', 'field' => 'status', 'sortable' => true],
            ['label' => 'Actions', 'actions' => true],
        ],
    ])
        @forelse($tournaments as $tournament)
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
            <tr>
                <td>
                    <div class="d-flex align-items-center gap-2">
                        @if($tournament->tournament_image)
                            <img src="{{ str_starts_with($tournament->tournament_image, 'http') ? $tournament->tournament_image : asset('storage/' . $tournament->tournament_image) }}" alt="{{ $tournament->name }}" class="tournament-thumb">
                        @else
                            <div class="tournament-thumb tournament-thumb--fallback"><i class="mdi mdi-trophy-outline"></i></div>
                        @endif
                        <div>
                            <div class="cell-primary">{{ $tournament->name }}</div>
                            <div class="cell-muted">#{{ $tournament->id }}</div>
                        </div>
                    </div>
                </td>
                <td>
                    <div class="cell-primary">{{ $tournament->club?->club_name ?? $tournament->club?->name ?? '—' }}</div>
                    <div class="cell-muted">{{ $tournament->club?->city ?? '—' }}</div>
                </td>
                <td>{{ \Illuminate\Support\Str::headline((string) $tournament->format) }}</td>
                <td class="cell-muted">{{ $tournament->start_date?->format('Y-m-d') }}</td>
                <td class="cell-muted">{{ $tournament->registration_deadline?->format('Y-m-d') }}</td>
                <td>{{ (int) $tournament->registered_players_count }}/{{ (int) $tournament->allowed_player }}</td>
                <td> {{ number_format((float) $tournament->entry_fees, 0) }}</td>
                <td> {{ number_format((float) $tournament->prize_pool, 0) }}</td>
                <td><span class="badge {{ $badgeClass }}">{{ $badgeLabel }}</span></td>
                <td class="text-end">
                    @include('admin.components.action-buttons', [
                        'type' => 'view',
                        'href' => route('admin.tournaments.show', $tournament),
                        'title' => 'View Tournament',
                    ])
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="10" class="admin-empty-state">No tournaments found.</td>
            </tr>
        @endforelse
    @endcomponent
</div>
@endsection

@push('my-styles')
<style>
    .tournament-stats-grid {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 1rem;
    }

    .tournament-stats-grid--secondary {
        grid-template-columns: repeat(3, minmax(0, 1fr));
    }

    .tournament-stat-card {
        background: linear-gradient(180deg, #ffffff 0%, #f9fbfc 100%);
        border: 1px solid #e6ebf2;
        border-radius: 18px;
        padding: 1rem 1.1rem;
        display: flex;
        align-items: center;
        gap: 0.9rem;
        box-shadow: 0 10px 24px rgba(15, 23, 42, 0.05);
    }

    .tournament-stat-card__icon {
        width: 48px;
        height: 48px;
        border-radius: 14px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 24px;
        flex-shrink: 0;
    }

    .tournament-stat-card__icon--primary { background: #eef2ff; color: #4f46e5; }
    .tournament-stat-card__icon--success { background: #ecfdf5; color: #059669; }
    .tournament-stat-card__icon--warning { background: #fff7ed; color: #f97316; }
    .tournament-stat-card__icon--accent { background: #fdf2f8; color: #db2777; }
    .tournament-stat-card__icon--danger { background: #fef2f2; color: #dc2626; }

    .tournament-stat-card__label {
        font-size: 0.75rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.06em;
        color: #64748b;
        margin-bottom: 0.2rem;
    }

    .tournament-stat-card__value {
        font-size: 1.4rem;
        font-weight: 800;
        color: #0f172a;
        line-height: 1.1;
    }

    .tournament-thumb {
        width: 44px;
        height: 44px;
        border-radius: 12px;
        object-fit: cover;
        flex-shrink: 0;
    }

    .tournament-thumb--fallback {
        display: flex;
        align-items: center;
        justify-content: center;
        background: #eef2ff;
        color: #4f46e5;
        font-size: 22px;
    }

    @media (max-width: 1199px) {
        .tournament-stats-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }

        .tournament-stats-grid--secondary {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
    }

    @media (max-width: 767px) {
        .tournament-stats-grid,
        .tournament-stats-grid--secondary {
            grid-template-columns: 1fr;
        }
    }
</style>
@endpush
