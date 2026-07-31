@extends('layouts/contentNavbarLayout')

@section('title', 'Businesses Estimated Scores')

@section('content')
<div class="admin-page estimated-scores-page">

    <div class="admin-page-header d-flex justify-content-between align-items-center mb-4">
        <div class="admin-page-header__left">
            <h4 class="admin-page-header__title">Estimated Scores</h4>
            <p class="admin-page-header__subtitle">View calculated scores and points breakdown for all registered businesses.</p>
        </div>
    </div>

    {{-- Stats Grid --}}
    <div class="um-stats-grid mb-4">
        <div class="um-stat-card">
            <div class="um-stat-card__icon um-stat-card__icon--primary"><i class="mdi mdi-briefcase-outline"></i></div>
            <div>
                <div class="um-stat-card__label">Total Businesses</div>
                <div class="um-stat-card__value">{{ number_format($stats['total_businesses']) }}</div>
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

    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <div>
                <h5 class="mb-0 fw-bold">Businesses Scores</h5>
                <small class="text-muted">Click on a business row to view its exact scores breakdown.</small>
            </div>
            <div>
                <span class="badge bg-label-primary"><strong>{{ number_format($businesses->total()) }}</strong> records</span>
            </div>
        </div>
        <div class="card-body">
            <!-- Filter & Search Form -->
            <form method="GET" id="filter-form" action="{{ route('admin.estimated-scores.index') }}" class="mb-4">
                <input type="hidden" name="sort" value="{{ $sort }}">
                <input type="hidden" name="direction" value="{{ $direction }}">
                <input type="hidden" name="per_page" value="{{ $perPage }}">

                <!-- Global Search Box (No Button, Auto Filters on Keystroke) -->
                <div class="mb-4">
                    <label class="form-label fw-semibold">Global Search</label>
                    <div class="input-group input-group-merge border-primary">
                        <span class="input-group-text"><i class="mdi mdi-magnify text-primary"></i></span>
                        <input type="text" name="search" id="global-search-input" value="{{ $search }}" class="form-control" placeholder="Type anything to search globally (Name, Location)..." autocomplete="off">
                    </div>
                    <small class="form-text text-muted">Type to search. Filtering happens automatically as you type.</small>
                </div>

                <!-- Separate Filters Panel (With Apply Button) -->
                <div class="border p-3 rounded mb-4 bg-light">
                    <h6 class="mb-3 fw-bold"><i class="mdi mdi-filter-variant me-1"></i> Filter Options</h6>
                    <div class="row g-3 align-items-end">
                        <div class="col-12 col-md-3">
                            <label class="form-label fw-medium">Business Name</label>
                            <input type="text" name="business_name" value="{{ $businessName ?? '' }}" class="form-control" placeholder="Filter by name...">
                        </div>
                        <div class="col-12 col-md-2">
                            <label class="form-label fw-medium">Location</label>
                            <input type="text" name="location" value="{{ $location ?? '' }}" class="form-control" placeholder="Filter by location...">
                        </div>
                        <div class="col-12 col-md-2">
                            <label class="form-label fw-medium">Date From</label>
                            <input type="date" name="date_from" value="{{ $dateFrom }}" class="form-control">
                        </div>
                        <div class="col-12 col-md-2">
                            <label class="form-label fw-medium">Date To</label>
                            <input type="date" name="date_to" value="{{ $dateTo }}" class="form-control">
                        </div>
                        <div class="col-12 col-md-2">
                            <button type="submit" class="btn btn-sm btn-dark w-100" title="Apply Filters">
                                <i class="mdi mdi-check me-1"></i> Apply
                            </button>
                        </div>
                    </div>
                    @if(filled($businessName) || filled($location) || filled($dateFrom) || filled($dateTo))
                        <div class="mt-2 text-end">
                            <a href="{{ route('admin.estimated-scores.index') }}" class="btn btn-sm btn-outline-secondary">
                                <i class="mdi mdi-refresh"></i> Reset Filters
                            </a>
                        </div>
                    @endif
                </div>
            </form>

            <!-- Table -->
            <div class="table-responsive text-nowrap">
                <table class="table table-hover">
                    <thead>
                        @php
                            $getSortUrl = function($col) use ($sort, $direction) {
                                $newDir = ($sort === $col && $direction === 'asc') ? 'desc' : 'asc';
                                return route('admin.estimated-scores.index', array_merge(request()->query(), ['sort' => $col, 'direction' => $newDir]));
                            };
                            $getSortIcon = function($col) use ($sort, $direction) {
                                if ($sort !== $col) return 'mdi-minus text-muted';
                                return $direction === 'asc' ? 'mdi-arrow-up text-primary' : 'mdi-arrow-down text-primary';
                            };
                        @endphp
                        <tr>
                            <th>#</th>
                            <th><a href="{{ $getSortUrl('name') }}" class="text-dark fw-bold">Business Name <i class="mdi {{ $getSortIcon('name') }} ml-1"></i></a></th>
                            <th><a href="{{ $getSortUrl('total_score') }}" class="text-dark fw-bold">Total Estimated Score <i class="mdi {{ $getSortIcon('total_score') }} ml-1"></i></a></th>
                            <th><a href="{{ $getSortUrl('updated_at') }}" class="text-dark fw-bold">Calculated At <i class="mdi {{ $getSortIcon('updated_at') }} ml-1"></i></a></th>
                            <th class="text-end">Breakdown</th>
                        </tr>
                    </thead>
                    <tbody class="table-border-bottom-0">
                        @forelse($businesses as $business)
                            <!-- Main Row -->
                            <tr class="align-middle score-row-header" style="cursor: pointer;" onclick="toggleScoreDetail({{ $business->id }})">
                                <td class="cell-muted">{{ $business->id }}</td>
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="um-user-avatar bg-label-primary" style="width: 32px; height: 32px; font-size: 0.9rem;">
                                            {{ strtoupper(substr($business->name, 0, 1)) }}
                                        </div>
                                        <div>
                                            <div class="cell-primary fw-semibold">{{ $business->name }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge bg-label-success px-3 py-2 font-monospace fw-bold" style="font-size: 0.85rem;">
                                        {{ $business->total_score ?? 0 }} Points
                                    </span>
                                </td>
                                <td class="cell-muted">{{ $business->updated_at?->format('Y-m-d H:i') }}</td>
                                <td class="text-end">
                                    <button type="button" class="btn btn-sm btn-icon btn-outline-primary" style="width: 32px; height: 32px;">
                                        <i class="mdi mdi-chevron-down" id="toggle-icon-{{ $business->id }}"></i>
                                    </button>
                                </td>
                            </tr>

                            <!-- Collapsible Detail Row -->
                            <tr id="score-detail-{{ $business->id }}" class="d-none">
                                <td colspan="5" class="bg-light p-3 border-bottom">
                                    <div class="score-breakdown-card p-3 rounded bg-white shadow-sm border">
                                        <h6 class="fw-bold mb-3 text-primary d-flex align-items-center gap-1">
                                            <i class="mdi mdi-format-list-bulleted-type"></i> Rule-Wise Points Breakdown
                                        </h6>
                                        <div class="row g-2">
                                            @forelse($business->estimatedScores as $score)
                                                <div class="col-12 col-md-4 col-lg-3">
                                                    <div class="p-2 border rounded d-flex justify-content-between align-items-center bg-light">
                                                        <span class="small text-capitalize text-dark fw-medium">
                                                            {{ str_replace('_', ' ', $score->name) }}
                                                        </span>
                                                        <span class="badge {{ $score->points > 0 ? 'bg-label-success' : 'bg-label-secondary' }} font-monospace fw-bold">
                                                            {{ $score->points }} Pts
                                                        </span>
                                                    </div>
                                                </div>
                                            @empty
                                                <div class="col-12 text-muted small p-2">
                                                    No rule records synced for this business yet. Points score: 0
                                                </div>
                                            @endforelse
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center py-4 text-muted">No businesses found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            @if($businesses->hasPages())
                <div class="d-flex justify-content-between align-items-center mt-4">
                    <div>
                        <span class="text-muted small">Showing {{ $businesses->firstItem() ?? 0 }} to {{ $businesses->lastItem() ?? 0 }} of {{ $businesses->total() }} records</span>
                    </div>
                    <div>
                        {{ $businesses->links() }}
                    </div>
                </div>
            @endif
        </div>
    </div>

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
        gap: 0.9rem;
        box-shadow: 0 10px 24px rgba(15, 23, 42, 0.05);
    }

    .um-stat-card__icon {
        width: 48px;
        height: 48px;
        border-radius: 14px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 24px;
        flex-shrink: 0;
    }

    .um-stat-card__icon--primary {
        background: #eef2ff;
        color: #4f46e5;
    }

    .um-stat-card__icon--success {
        background: #e8f5e9;
        color: #2e7d32;
    }

    .um-stat-card__label {
        font-size: 0.72rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.06em;
        color: #64748b;
        margin-bottom: 0.2rem;
    }

    .um-stat-card__value {
        font-size: 1.5rem;
        font-weight: 800;
        color: #0f172a;
        line-height: 1.1;
    }

    .um-user-avatar {
        width: 38px;
        height: 38px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 700;
        font-size: 0.9rem;
        flex-shrink: 0;
    }
    
    .score-row-header:hover {
        background-color: rgba(67, 89, 113, 0.02) !important;
    }

    .score-breakdown-card {
        animation: fadeInSlide 0.25s ease-out;
    }

    @keyframes fadeInSlide {
        from {
            opacity: 0;
            transform: translateY(-5px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }
</style>
@endpush

@push('my-script')
<script>
    function toggleScoreDetail(businessId) {
        const detailRow = document.getElementById('score-detail-' + businessId);
        const icon = document.getElementById('toggle-icon-' + businessId);

        if (detailRow) {
            if (detailRow.classList.contains('d-none')) {
                // Show detail
                detailRow.classList.remove('d-none');
                if (icon) {
                    icon.classList.replace('mdi-chevron-down', 'mdi-chevron-up');
                }
            } else {
                // Hide detail
                detailRow.classList.add('d-none');
                if (icon) {
                    icon.classList.replace('mdi-chevron-up', 'mdi-chevron-down');
                }
            }
        }
    }

    document.addEventListener('DOMContentLoaded', function() {
        const searchInput = document.getElementById('global-search-input');
        const filterForm = document.getElementById('filter-form');
        let searchTimeout;

        if (searchInput && filterForm) {
            searchInput.addEventListener('input', function() {
                clearTimeout(searchTimeout);
                searchTimeout = setTimeout(() => {
                    filterForm.submit();
                }, 400); // 400ms delay
            });

            // Focus and put cursor at the end of input
            const length = searchInput.value.length;
            if (length > 0) {
                searchInput.focus();
                searchInput.setSelectionRange(length, length);
            }
        }
    });
</script>
@endpush
