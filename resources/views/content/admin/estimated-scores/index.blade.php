@extends('layouts/contentNavbarLayout')

@section('title', 'Businesses Estimated Scores')

@section('content')
<div class="admin-page estimated-scores-page">

    <div class="admin-page-header d-flex justify-content-between align-items-center mb-4">
        <div class="admin-page-header__left">
            <h4 class="admin-page-header__title">Estimated Scores</h4>
            <p class="admin-page-header__subtitle">View calculated scores and points for all registered businesses.</p>
        </div>
    </div>

    {{-- Stats Grid --}}
    <div class="um-stats-grid mb-4">
        <div class="um-stat-card">
            <div class="um-stat-card__icon um-stat-card__icon--primary"><i class="mdi mdi-star-box"></i></div>
            <div>
                <div class="um-stat-card__label">Total Score Records</div>
                <div class="um-stat-card__value">{{ number_format($stats['total_records']) }}</div>
            </div>
        </div>
        <div class="um-stat-card">
            <div class="um-stat-card__icon um-stat-card__icon--success"><i class="mdi mdi-trophy-outline"></i></div>
            <div>
                <div class="um-stat-card__label">Total Points Awarded</div>
                <div class="um-stat-card__value">{{ number_format($stats['total_points']) }}</div>
            </div>
        </div>
    </div>

    @component('admin.components.datatable', [
        'title'     => 'Estimated Scores List',
        'subtitle'  => 'Search by business name or score field criterion name.',
        'paginator' => $scores,
        'createUrl' => null,
        'search'    => $search,
        'perPage'   => $perPage,
        'sort'      => $sort,
        'direction' => $direction,
        'columns'   => [
            ['label' => '#',               'field' => 'id',         'sortable' => false],
            ['label' => 'Business Name',   'sortable' => false],
            ['label' => 'Score Field Name','field' => 'name',       'sortable' => true],
            ['label' => 'Points Awarded',  'field' => 'points',     'sortable' => true],
            ['label' => 'Calculated At',   'field' => 'updated_at', 'sortable' => true],
        ],
    ])
        @forelse($scores as $score)
            <tr>
                <td class="cell-muted">{{ $score->id }}</td>
                <td>
                    @if($score->business)
                        <div class="d-flex align-items-center gap-2">
                            <div class="um-user-avatar bg-label-primary" style="width: 32px; height: 32px; font-size: 0.9rem;">
                                {{ strtoupper(substr($score->business->name, 0, 1)) }}
                            </div>
                            <div>
                                <div class="cell-primary fw-semibold">{{ $score->business->name }}</div>
                            </div>
                        </div>
                    @else
                        <span class="text-muted">— Deleted Business —</span>
                    @endif
                </td>
                <td class="cell-primary font-monospace text-capitalize">
                    {{ str_replace('_', ' ', $score->name) }}
                </td>
                <td>
                    <span class="badge {{ $score->points > 0 ? 'bg-label-success' : 'bg-label-secondary' }} px-3 py-2">
                        {{ $score->points }} Points
                    </span>
                </td>
                <td class="cell-muted">{{ $score->updated_at?->format('Y-m-d H:i') }}</td>
            </tr>
        @empty
            <tr>
                <td colspan="5" class="admin-empty-state">No score records found.</td>
            </tr>
        @endforelse
    @endcomponent

</div>
@endsection

@push('my-styles')
<style>
    .font-monospace {
        font-family: SFMono-Regular, Menlo, Monaco, Consolas, "Liberation Mono", "Courier New", monospace;
    }
    .um-stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
        gap: 1rem;
    }

    .um-stat-card {
        background: linear-gradient(180deg, #ffffff 0%, #f9fbfc 100%);
        border: 1px solid #e6ebf2;
        border-radius: 18px;
        padding: 1rem 1.1rem;
        display: flex;
        align-items: center;
        gap: 1rem;
    }
    .um-stat-card__icon {
        width: 48px;
        height: 48px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.5rem;
    }
    .um-stat-card__icon--primary {
        background: #eef2ff;
        color: #4f46e5;
    }
    .um-stat-card__icon--success {
        background: #ecfdf5;
        color: #10b981;
    }
    .um-stat-card__label {
        font-size: 0.8rem;
        color: #64748b;
        font-weight: 500;
    }
    .um-stat-card__value {
        font-size: 1.25rem;
        font-weight: 700;
        color: #1e293b;
    }
</style>
@endpush
