@extends('layouts/contentNavbarLayout')

@section('title', 'Courts')

@section('content')
<div class="admin-page courts-page">

    <div class="admin-page-header">
        <div class="admin-page-header__left">
            <h4 class="admin-page-header__title">Courts</h4>
            <p class="admin-page-header__subtitle">Manage every court across clubs, with live status and maintenance visibility.</p>
        </div>
    </div>

    <div class="court-stats-grid">
        <div class="court-stat-card">
            <div class="court-stat-card__icon court-stat-card__icon--primary"><i class="mdi mdi-tennis"></i></div>
            <div>
                <div class="court-stat-card__label">Total Courts</div>
                <div class="court-stat-card__value">{{ number_format($stats['total_courts']) }}</div>
            </div>
        </div>
        <div class="court-stat-card">
            <div class="court-stat-card__icon court-stat-card__icon--success"><i class="mdi mdi-check-circle-outline"></i></div>
            <div>
                <div class="court-stat-card__label">Active Courts</div>
                <div class="court-stat-card__value">{{ number_format($stats['active_courts']) }}</div>
            </div>
        </div>
        <div class="court-stat-card">
            <div class="court-stat-card__icon court-stat-card__icon--warning"><i class="mdi mdi-wrench-outline"></i></div>
            <div>
                <div class="court-stat-card__label">Maintenance</div>
                <div class="court-stat-card__value">{{ number_format($stats['maintenance_courts']) }}</div>
            </div>
        </div>
        <div class="court-stat-card">
            <div class="court-stat-card__icon court-stat-card__icon--accent"><i class="mdi mdi-domain"></i></div>
            <div>
                <div class="court-stat-card__label">Clubs with Courts</div>
                <div class="court-stat-card__value">{{ number_format($stats['clubs_with_courts']) }}</div>
            </div>
        </div>
    </div>

    @component('admin.components.datatable', [
        'title' => 'Court Inventory',
        'subtitle' => 'Search by court, club, type, or status.',
        'paginator' => $courts,
        'createUrl' => route('admin.courts.create'),
        'createLabel' => 'Add Court',
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
                    'active' => 'Active',
                    'inactive' => 'Inactive',
                    'maintenance' => 'Maintenance',
                ],
            ],
            [
                'name' => 'type',
                'label' => 'Type',
                'value' => $type,
                'options' => [
                    '' => 'All Types',
                    'glass' => 'Glass',
                    'wooden' => 'Wooden',
                    'synthetic' => 'Synthetic',
                    'other' => 'Other',
                ],
            ],
            [
                'name' => 'club_id',
                'label' => 'Club',
                'value' => $clubId,
                'options' => ['' => 'All Clubs'] + $clubs->mapWithKeys(fn ($club) => [$club->id => $club->club_name ?? $club->name])->all(),
            ],
        ],
        'columns' => [
            ['label' => 'Court', 'field' => 'name', 'sortable' => true],
            ['label' => 'Club', 'sortable' => false],
            ['label' => 'Type', 'field' => 'type', 'sortable' => true],
            ['label' => 'Price/Hour', 'field' => 'price_per_hour', 'sortable' => true],
            ['label' => 'Capacity', 'sortable' => false],
            ['label' => 'Status', 'field' => 'status', 'sortable' => true],
            ['label' => 'Created', 'field' => 'created_at', 'sortable' => true],
            ['label' => 'Actions', 'actions' => true],
        ],
    ])
        @forelse($courts as $court)
            @php
                $statusMap = [
                    'active' => ['bg-label-success', 'Active'],
                    'maintenance' => ['bg-label-warning', 'Maintenance'],
                    'inactive' => ['bg-label-secondary', 'Inactive'],
                ];
                [$badgeClass, $badgeLabel] = $statusMap[$court->status] ?? ['bg-label-secondary', ucfirst($court->status)];
            @endphp
            <tr>
                <td>
                    <div class="d-flex align-items-center gap-2">
                        <div class="court-row-avatar">
                            <i class="mdi mdi-tennis"></i>
                        </div>
                        <div>
                            <div class="cell-primary">{{ $court->name }}</div>
                            <div class="cell-muted">Court #{{ $court->id }}</div>
                        </div>
                    </div>
                </td>
                <td>
                    <div class="d-flex align-items-center gap-2">
                        @if($court->club?->club_logo)
                            <img src="{{ asset('storage/' . $court->club->club_logo) }}" alt="{{ $court->club?->club_name }}"
                                class="court-club-logo">
                        @else
                            <div class="court-row-avatar court-row-avatar--club">
                                <i class="mdi mdi-domain"></i>
                            </div>
                        @endif
                        <div>
                            <div class="cell-primary">{{ $court->club?->club_name ?? $court->club?->name ?? '—' }}</div>
                            <div class="cell-muted">{{ $court->club?->city ?? '—' }}</div>
                        </div>
                    </div>
                </td>
                <td>{{ \Illuminate\Support\Str::headline((string) $court->type) }}</td>
                <td>PKR {{ number_format((float) $court->price_per_hour, 0) }}</td>
                <td>{{ $court->capacity ?? '—' }}</td>
                <td><span class="badge {{ $badgeClass }}">{{ $badgeLabel }}</span></td>
                <td class="cell-muted">{{ $court->created_at?->format('Y-m-d') }}</td>
                <td class="text-end">
                    @include('admin.components.action-buttons', [
                        'type' => 'view',
                        'href' => route('admin.courts.show', $court),
                        'title' => 'View Court',
                    ])
                    @include('admin.components.action-buttons', [
                        'type' => 'edit',
                        'href' => route('admin.courts.edit', $court),
                        'title' => 'Edit Court',
                    ])
                    @include('admin.components.action-buttons', [
                        'type' => 'delete',
                        'formAction' => route('admin.courts.destroy', $court),
                        'confirm' => "Delete court \"{$court->name}\"?",
                    ])
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="8" class="admin-empty-state">No courts found.</td>
            </tr>
        @endforelse
    @endcomponent

</div>
@endsection

@push('my-styles')
<style>
    .court-stats-grid {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 1rem;
    }

    .court-stat-card {
        background: linear-gradient(180deg, #ffffff 0%, #f9fbfc 100%);
        border: 1px solid #e6ebf2;
        border-radius: 18px;
        padding: 1rem 1.1rem;
        display: flex;
        align-items: center;
        gap: 0.9rem;
        box-shadow: 0 10px 24px rgba(15, 23, 42, 0.05);
    }

    .court-stat-card__icon {
        width: 48px;
        height: 48px;
        border-radius: 14px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 24px;
        flex-shrink: 0;
    }

    .court-stat-card__icon--primary { background: #eef2ff; color: #4f46e5; }
    .court-stat-card__icon--success { background: #ecfdf5; color: #059669; }
    .court-stat-card__icon--warning { background: #fff7ed; color: #f97316; }
    .court-stat-card__icon--accent { background: #fdf2f8; color: #db2777; }

    .court-stat-card__label {
        font-size: 0.75rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.06em;
        color: #64748b;
        margin-bottom: 0.2rem;
    }

    .court-stat-card__value {
        font-size: 1.5rem;
        font-weight: 800;
        color: #0f172a;
        line-height: 1.1;
    }

    .court-row-avatar,
    .court-row-avatar--club {
        width: 42px;
        height: 42px;
        border-radius: 14px;
        display: flex;
        align-items: center;
        justify-content: center;
        background: #eef2ff;
        color: #4f46e5;
        flex-shrink: 0;
    }

    .court-row-avatar--club {
        background: #ecfdf5;
        color: #059669;
    }

    .court-club-logo {
        width: 42px;
        height: 42px;
        border-radius: 14px;
        object-fit: cover;
        flex-shrink: 0;
    }

    @media (max-width: 1199px) {
        .court-stats-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
    }

    @media (max-width: 767px) {
        .court-stats-grid {
            grid-template-columns: 1fr;
        }
    }
</style>
@endpush
