@extends('layouts/contentNavbarLayout')

@section('title', 'Admin Audit Logs')

@section('content')
<div class="admin-page audit-logs-page">

    <div class="admin-page-header d-flex justify-content-between align-items-center mb-4">
        <div class="admin-page-header__left">
            <h4 class="admin-page-header__title">Admin Audit Logs</h4>
            <p class="admin-page-header__subtitle">Track all administrative actions, logins, updates, and permission changes.</p>
        </div>
    </div>

    <div class="card mt-4">
        <div class="card-header d-flex justify-content-between align-items-center">
            <div>
                <h5 class="mb-0 fw-bold">Audit History</h5>
                <small class="text-muted">Review administrative and system events logs.</small>
            </div>
            <div>
                <span class="badge bg-label-primary"><strong>{{ number_format($logs->total()) }}</strong> records</span>
            </div>
        </div>
        <div class="card-body">
            <!-- Filter & Search Form -->
            <form method="GET" id="filter-form" action="{{ route('admin.audit-logs.index') }}">
                <input type="hidden" name="sort" value="{{ $sort }}">
                <input type="hidden" name="direction" value="{{ $direction }}">
                <input type="hidden" name="per_page" value="{{ $perPage }}">

                <!-- Global Search Box -->
                <div class="mb-4">
                    <label class="form-label fw-semibold">Global Search</label>
                    <div class="input-group input-group-merge border-primary">
                        <span class="input-group-text"><i class="mdi mdi-magnify text-primary"></i></span>
                        <input type="text" name="search" id="global-search-input" value="{{ $search }}"
                            class="form-control" placeholder="Search by action, target, user name, or description..." autocomplete="off">
                    </div>
                    <small class="form-text text-muted">Type to search. Filtering happens automatically as you type.</small>
                </div>

                <!-- Separate Filters Panel -->
                <div class="border p-3 rounded mb-4 bg-light">
                    <h6 class="mb-3 fw-bold"><i class="mdi mdi-filter-variant me-1"></i> Filter Options</h6>
                    <div class="row g-3 align-items-end">
                        <div class="col-12 col-md-4">
                            <label class="form-label fw-medium">Admin User</label>
                            <select name="user_filter" class="form-select">
                                <option value="">All Users</option>
                                @foreach($logUsers as $u)
                                    <option value="{{ $u->id }}" @selected((string) $userFilter === (string) $u->id)>{{ $u->name }} ({{ $u->email }})</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-12 col-md-3">
                            <label class="form-label fw-medium">Date From</label>
                            <input type="date" name="date_from" value="{{ $dateFrom }}" class="form-control">
                        </div>
                        <div class="col-12 col-md-3">
                            <label class="form-label fw-medium">Date To</label>
                            <input type="date" name="date_to" value="{{ $dateTo }}" class="form-control">
                        </div>
                        <div class="col-12 col-md-2 d-grid gap-2">
                            <button type="submit" class="btn btn-sm btn-dark w-100" title="Apply Filters">
                                <i class="mdi mdi-check me-1"></i> Apply
                            </button>
                        </div>
                    </div>
                    @if(filled($userFilter) || filled($dateFrom) || filled($dateTo))
                        <div class="mt-2 text-end">
                            <a href="{{ route('admin.audit-logs.index') }}" class="btn btn-sm btn-outline-secondary">
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
                            $getSortUrl = function ($col) use ($sort, $direction) {
                                $newDir = $sort === $col && $direction === 'asc' ? 'desc' : 'asc';
                                return route(
                                    'admin.audit-logs.index',
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
                        <tr>
                            <th><a href="{{ $getSortUrl('id') }}" class="text-dark fw-bold"># <i class="mdi {{ $getSortIcon('id') }} ml-1"></i></a></th>
                            <th><a href="{{ $getSortUrl('user') }}" class="text-dark fw-bold">Admin User <i class="mdi {{ $getSortIcon('user') }} ml-1"></i></a></th>
                            <th><a href="{{ $getSortUrl('action') }}" class="text-dark fw-bold">Action Type <i class="mdi {{ $getSortIcon('action') }} ml-1"></i></a></th>
                            <th><a href="{{ $getSortUrl('target_type') }}" class="text-dark fw-bold">Target <i class="mdi {{ $getSortIcon('target_type') }} ml-1"></i></a></th>
                            <th><a href="{{ $getSortUrl('description') }}" class="text-dark fw-bold">Description <i class="mdi {{ $getSortIcon('description') }} ml-1"></i></a></th>
                            <th><a href="{{ $getSortUrl('ip_address') }}" class="text-dark fw-bold">IP Address <i class="mdi {{ $getSortIcon('ip_address') }} ml-1"></i></a></th>
                            <th><a href="{{ $getSortUrl('created_at') }}" class="text-dark fw-bold">Timestamp <i class="mdi {{ $getSortIcon('created_at') }} ml-1"></i></a></th>
                        </tr>
                    </thead>
                    <tbody class="table-border-bottom-0">
                        @forelse($logs as $log)
                            <tr>
                                <td class="cell-muted">{{ $log->id }}</td>
                                <td>
                                    @if($log->user)
                                        <div class="d-flex align-items-center gap-2">
                                            <div class="um-user-avatar bg-label-primary" style="width: 32px; height: 32px; font-size: 0.85rem; display: flex; align-items: center; justify-content: center; border-radius: 8px;">
                                                {{ strtoupper(substr($log->user->name, 0, 1)) }}
                                            </div>
                                            <div>
                                                <div class="cell-primary fw-semibold">{{ $log->user->name }}</div>
                                                <small class="text-muted">{{ $log->user->email }}</small>
                                            </div>
                                        </div>
                                    @else
                                        <span class="text-muted">— System / Deleted User —</span>
                                    @endif
                                </td>
                                <td>
                                    <span class="badge bg-label-info text-capitalize">{{ str_replace('_', ' ', $log->action) }}</span>
                                </td>
                                <td>
                                    @if($log->target_type)
                                        <span class="text-secondary fw-semibold">{{ $log->target_type }}</span>
                                        @if($log->target_id)
                                            <small class="text-muted">(ID: {{ $log->target_id }})</small>
                                        @endif
                                    @else
                                        <span class="text-muted">—</span>
                                    @endif
                                </td>
                                <td style="max-width: 320px; white-space: normal;" class="cell-primary">
                                    {{ $log->description }}
                                </td>
                                <td class="cell-muted font-monospace">{{ $log->ip_address }}</td>
                                <td class="cell-muted">{{ $log->created_at?->format('Y-m-d H:i:s') }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-4 text-muted">No audit logs found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            @if ($logs->total() > 0)
                <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mt-4">
                    <div class="d-flex align-items-center gap-2">
                        <span class="text-muted small">Showing {{ $logs->firstItem() ?? 0 }} to
                            {{ $logs->lastItem() ?? 0 }} of {{ $logs->total() }} records</span>
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
                        {{ $logs->links() }}
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
        font-family: SFMono-Regular, Menlo, Monaco, Consolas, monospace;
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
