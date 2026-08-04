@extends('layouts/contentNavbarLayout')

@section('title', 'Business Management')

@section('content')
    <div class="admin-page business-management-page">

        <div class="admin-page-header d-flex justify-content-between align-items-center mb-4">
            <div class="admin-page-header__left">
                <h4 class="admin-page-header__title">Business Management</h4>
                <p class="admin-page-header__subtitle">View and manage registered businesses, catalog items, and top selling
                    products.</p>
            </div>
        </div>

        {{-- Stats Grid --}}
        <div class="um-stats-grid mb-4">
            <div class="um-stat-card">
                <div class="um-stat-card__icon um-stat-card__icon--primary"><i class="mdi mdi-briefcase"></i></div>
                <div>
                    <div class="um-stat-card__label">Total Businesses</div>
                    <div class="um-stat-card__value">{{ number_format($stats['total']) }}</div>
                </div>
            </div>
            {{-- <div class="um-stat-card">
            <div class="um-stat-card__icon um-stat-card__icon--success"><i class="mdi mdi-check-decagram"></i></div>
            <div>
                <div class="um-stat-card__label">With Offerings</div>
                <div class="um-stat-card__value">{{ number_format($stats['with_offerings']) }}</div>
            </div>
        </div> --}}
            <div class="um-stat-card">
                <div class="um-stat-card__icon um-stat-card__icon--warning"><i class="mdi mdi-map-marker-radius"></i></div>
                <div>
                    <div class="um-stat-card__label">Unique Locations</div>
                    <div class="um-stat-card__value">{{ number_format($stats['locations_count']) }}</div>
                </div>
            </div>
        </div>

        <div class="card mt-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <div>
                    <h5 class="mb-0 fw-bold">Businesses</h5>
                    <small class="text-muted">Manage registered businesses, catalog items, and preferences.</small>
                </div>
                <div>
                    <span class="badge bg-label-primary"><strong>{{ number_format($businesses->total()) }}</strong>
                        records</span>
                </div>
            </div>
            <div class="card-body">
                <!-- Filter & Search Form -->
                <form method="GET" id="filter-form" action="{{ route('admin.business-management.index') }}">
                    <input type="hidden" name="sort" value="{{ $sort }}">
                    <input type="hidden" name="direction" value="{{ $direction }}">
                    <input type="hidden" name="per_page" value="{{ $perPage }}">

                    <!-- Global Search Box (No Button, Auto Filters on Keystroke) -->
                    <div class="mb-4">
                        <label class="form-label fw-semibold">Global Search</label>
                        <div class="input-group input-group-merge border-primary">
                            <span class="input-group-text"><i class="mdi mdi-magnify text-primary"></i></span>
                            <input type="text" name="search" id="global-search-input" value="{{ $search }}"
                                class="form-control"
                                placeholder="Type anything to search globally (Name, Location, Phone, Email, Category)..."
                                autocomplete="off">
                        </div>
                        <small class="form-text text-muted">Type to search. Filtering happens automatically as you type.</small>
                    </div>

                    <!-- Separate Filters Panel (With Apply Button) -->
                    <div class="border p-3 rounded mb-4 bg-light">
                        <h6 class="mb-3 fw-bold"><i class="mdi mdi-filter-variant me-1"></i> Filter Options</h6>
                        <div class="row g-3 align-items-end">
                            <div class="col-12 col-md-3">
                                <label class="form-label fw-medium">Business Name</label>
                                <input type="text" name="business_name" value="{{ $businessName ?? '' }}"
                                    class="form-control" placeholder="Filter by name...">
                            </div>
                            <div class="col-12 col-md-2">
                                <label class="form-label fw-medium">Location</label>
                                <input type="text" name="location" value="{{ $location ?? '' }}" class="form-control"
                                    placeholder="Filter by location...">
                            </div>
                            <div class="col-12 col-md-2">
                                <label class="form-label fw-medium">Status</label>
                                <select name="status" class="form-select">
                                    <option value="">All Statuses</option>
                                    <option value="approved" @selected($status === 'approved')>Approved</option>
                                    <option value="suspended" @selected($status === 'suspended')>Suspended</option>
                                </select>
                            </div>
                            <div class="col-12 col-md-2">
                                <label class="form-label fw-medium">Date From</label>
                                <input type="date" name="date_from" value="{{ $dateFrom }}" class="form-control">
                            </div>
                            <div class="col-12 col-md-2">
                                <label class="form-label fw-medium">Date To</label>
                                <input type="date" name="date_to" value="{{ $dateTo }}" class="form-control">
                            </div>
                            <div class="col-12 col-md-1 d-grid gap-2">
                                <button type="submit" class="btn btn-dark" title="Apply Filters">
                                    Apply
                                </button>
                            </div>
                        </div>
                        @if (filled($businessName) || filled($status) || filled($location) || filled($dateFrom) || filled($dateTo))
                            <div class="mt-2 text-end">
                                <a href="{{ route('admin.business-management.index') }}"
                                    class="btn btn-sm btn-outline-secondary">
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
                            <tr>
                                <!-- Custom Sort Headers -->
                                @php
                                    $getSortUrl = function ($col) use ($sort, $direction) {
                                        $newDir = $sort === $col && $direction === 'asc' ? 'desc' : 'asc';
                                        return route(
                                            'admin.business-management.index',
                                            array_merge(request()->query(), ['sort' => $col, 'direction' => $newDir]),
                                        );
                                    };
                                    $getSortIcon = function ($col) use ($sort, $direction) {
                                        if ($sort !== $col) {
                                            return 'mdi-minus text-muted';
                                        }
                                        return $direction === 'asc'
                                            ? 'mdi-arrow-up text-primary'
                                            : 'mdi-arrow-down text-primary';
                                    };
                                @endphp
                                <th><a href="{{ $getSortUrl('id') }}" class="text-dark fw-bold"># <i
                                            class="mdi {{ $getSortIcon('id') }} ml-1"></i></a></th>
                                <th><a href="{{ $getSortUrl('name') }}" class="text-dark fw-bold">Business Name <i
                                            class="mdi {{ $getSortIcon('name') }} ml-1"></i></a></th>
                                <th><a href="{{ $getSortUrl('location') }}" class="text-dark fw-bold">Location <i
                                            class="mdi {{ $getSortIcon('location') }} ml-1"></i></a></th>
                                <th><a href="{{ $getSortUrl('status') }}" class="text-dark fw-bold">Status <i
                                            class="mdi {{ $getSortIcon('status') }} ml-1"></i></a></th>
                                <th>Preferences</th>
                                <th><a href="{{ $getSortUrl('created_at') }}" class="text-dark fw-bold">Created At <i
                                            class="mdi {{ $getSortIcon('created_at') }} ml-1"></i></a></th>
                                <th class="text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="table-border-bottom-0">
                            @forelse($businesses as $business)
                                <tr>
                                    <td class="cell-muted">{{ $business->id }}</td>
                                    <td>
                                        <div class="d-flex align-items-center gap-2">
                                            <div class="um-user-avatar bg-label-primary">
                                                {{ strtoupper(substr($business->name, 0, 1)) }}
                                            </div>
                                            <div>
                                                <div class="cell-primary fw-semibold">{{ $business->name }}</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="cell-primary">
                                        {!! implode(
                                            '<br>',
                                            array_map(fn($chunk) => implode(' ', $chunk), array_chunk(explode(' ', $business->location), 4)),
                                        ) !!}
                                    </td>
                                    <td>
                                        @if (($business->status ?? 'approved') === 'suspended')
                                            <span class="badge bg-label-danger">Suspended</span>
                                        @else
                                            <span class="badge bg-label-success">Approved</span>
                                        @endif
                                    </td>
                                    <td>
                                        <a href="{{ route('admin.business-management.preferences', $business) }}"
                                            class="btn btn-outline-primary btn-xs d-inline-flex align-items-center gap-1"
                                            title="View Preferences">
                                            <i class="mdi mdi-cog-outline"></i> Preferences
                                        </a>
                                    </td>
                                    <td class="cell-muted">{{ $business->created_at?->format('Y-m-d') }}</td>
                                    <td class="text-end">
                                        <div class="d-inline-flex align-items-center gap-1">
                                            @include('admin.components.action-buttons', [
                                                'type' => 'view',
                                                'href' => route('admin.business-management.show', $business),
                                                'title' => 'View Business Details',
                                            ])
                                            <form action="{{ route('admin.business-management.update', $business) }}" method="POST" class="d-inline">
                                                @csrf
                                                @method('PUT')
                                                <select name="status" class="form-select form-select-sm um-status-select" onchange="this.form.submit()" title="Update Status">
                                                    <option value="approved" @selected(($business->status ?? 'approved') === 'approved')>Approved</option>
                                                    <option value="suspended" @selected(($business->status ?? 'approved') === 'suspended')>Suspended</option>
                                                </select>
                                            </form>
                                            <form action="{{ route('admin.business-management.destroy', $business) }}"
                                                method="POST" class="d-inline"
                                                onsubmit="return confirm('Are you sure you want to delete this business?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit"
                                                    class="action-icon-btn action-icon-delete border-0 bg-transparent"
                                                    title="Delete Business">
                                                    <i class="mdi mdi-trash-can-outline text-danger"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center py-4 text-muted">No businesses found.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                @if ($businesses->total() > 0)
                    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mt-4">
                        <div class="d-flex align-items-center gap-2">
                            <span class="text-muted small">Showing {{ $businesses->firstItem() ?? 0 }} to
                                {{ $businesses->lastItem() ?? 0 }} of {{ $businesses->total() }} records</span>
                            <form method="GET" action="{{ request()->url() }}" class="d-inline-block ms-3">
                                @foreach(request()->query() as $key => $value)
                                    @if(!in_array($key, ['per_page', 'page'], true))
                                        <input type="hidden" name="{{ $key }}" value="{{ $value }}">
                                    @endif
                                @endforeach
                                <select name="per_page" class="form-select form-select-sm d-inline-block w-auto" onchange="this.form.submit()">
                                    @foreach([10, 15, 20, 25, 50, 100] as $size)
                                        <option value="{{ $size }}" @selected($perPage == $size)>{{ $size }} / page</option>
                                    @endforeach
                                </select>
                            </form>
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

        .um-stat-card__icon--warning {
            background: #fff3e0;
            color: #ef6c00;
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

        .action-icon-btn {
            padding: 0.35rem;
            border-radius: 8px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            transition: all 0.2s;
        }

        .action-icon-btn:hover {
            background: #f1f5f9;
        }

        .um-status-select {
            width: auto;
            min-width: 110px;
            font-size: 0.75rem;
            padding: 0.25rem 1.5rem 0.25rem 0.5rem;
            border-radius: 6px;
        }

        @media (max-width: 767px) {
            .um-stats-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
@endpush

@push('my-script')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const searchInput = document.getElementById('global-search-input');
            const filterForm = document.getElementById('filter-form');
            
            let searchTimeout;

            if (searchInput && filterForm) {
                const performSearch = () => {
                    const formData = new FormData(filterForm);
                    const params = new URLSearchParams(formData);
                    const url = `${filterForm.action}?${params.toString()}`;

                    fetch(url, {
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    })
                    .then(response => response.text())
                    .then(html => {
                        const parser = new DOMParser();
                        const doc = parser.parseFromString(html, 'text/html');
                        
                        const tbody = document.querySelector('table tbody');
                        const newTbody = doc.querySelector('table tbody');
                        if (newTbody && tbody) {
                            tbody.innerHTML = newTbody.innerHTML;
                        }
                        
                        const paginationSelector = '.d-flex.justify-content-between.align-items-center.mt-4, .card-body > .d-flex.justify-content-between';
                        const currentPagination = document.querySelector(paginationSelector);
                        const newPagination = doc.querySelector(paginationSelector);
                        
                        if (currentPagination) {
                            if (newPagination) {
                                currentPagination.outerHTML = newPagination.outerHTML;
                            } else {
                                currentPagination.remove();
                            }
                        } else if (newPagination) {
                            const cardBody = document.querySelector('.card-body');
                            if (cardBody) {
                                cardBody.appendChild(newPagination);
                            }
                        }
                        
                        window.history.pushState({}, '', url);
                    })
                    .catch(err => console.error('Error filtering:', err));
                };

                searchInput.addEventListener('input', function() {
                    clearTimeout(searchTimeout);
                    searchTimeout = setTimeout(performSearch, 300);
                });

                filterForm.addEventListener('submit', function(e) {
                    e.preventDefault();
                    performSearch();
                });

                filterForm.querySelectorAll('select, input[type="date"]').forEach(el => {
                    el.addEventListener('change', performSearch);
                });
            }
        });
    </script>
@endpush


