@extends('layouts/contentNavbarLayout')

@section('title', 'Permissions')

@section('content')
    <div class="admin-page permissions-page">

        <div class="admin-page-header d-flex justify-content-between align-items-center mb-4">
            <div class="admin-page-header__left">
                <h4 class="admin-page-header__title">Permissions</h4>
                <p class="admin-page-header__subtitle">Permission inventory grouped by modules and actions.</p>
            </div>
        </div>

    <div class="card mt-4">
        <div class="card-header d-flex justify-content-between align-items-center">
            <div>
                <h5 class="mb-0 fw-bold">Permissions</h5>
                <small class="text-muted">Manage system permissions and access rules.</small>
            </div>
            <div>
                <span class="badge bg-label-primary"><strong>{{ number_format($permissions->total()) }}</strong> records</span>
                <a href="{{ route('admin.permissions.create') }}" class="btn btn-sm btn-primary ms-2">
                    <i class="mdi mdi-plus me-1"></i> Create Permission
                </a>
            </div>
        </div>
        <div class="card-body">
            <!-- Filter & Search Form -->
            <form method="GET" id="filter-form" action="{{ route('admin.permissions.index') }}">
                <input type="hidden" name="sort" value="{{ $sort }}">
                <input type="hidden" name="direction" value="{{ $direction }}">
                <input type="hidden" name="per_page" value="{{ $perPage }}">

                <!-- Global Search Box (No Button, Auto Filters on Keystroke) -->
                <div class="mb-4">
                    <label class="form-label fw-semibold">Global Search</label>
                    <div class="input-group input-group-merge border-primary">
                        <span class="input-group-text"><i class="mdi mdi-magnify text-primary"></i></span>
                        <input type="text" name="search" id="global-search-input" value="{{ $search }}" class="form-control" placeholder="Type anything to search (Name or Guard)..." autocomplete="off">
                    </div>
                    <small class="form-text text-muted">Type to search. Filtering happens automatically as you type.</small>
                </div>

                <!-- Separate Filters Panel (With Apply Button) -->
                <div class="border p-3 rounded mb-4 bg-light">
                    <h6 class="mb-3 fw-bold"><i class="mdi mdi-filter-variant me-1"></i> Filter Options</h6>
                    <div class="row g-3 align-items-end">
                        <div class="col-12 col-md-3">
                            <label class="form-label fw-medium">Permission Name</label>
                            <input type="text" name="permission_name" value="{{ $permissionNameFilter ?? '' }}" class="form-control" placeholder="Filter by name...">
                        </div>
                        <div class="col-12 col-md-3">
                            <label class="form-label fw-medium">Role</label>
                            <select name="role" class="form-select">
                                <option value="">All Roles</option>
                                @foreach($roles as $r)
                                    <option value="{{ $r->name }}" @selected(($roleFilter ?? '') === $r->name)>{{ $r->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-12 col-md-2">
                            <label class="form-label fw-medium">Date From</label>
                            <input type="date" name="date_from" value="{{ $dateFrom ?? '' }}" class="form-control">
                        </div>
                        <div class="col-12 col-md-2">
                            <label class="form-label fw-medium">Date To</label>
                            <input type="date" name="date_to" value="{{ $dateTo ?? '' }}" class="form-control">
                        </div>
                        <div class="col-12 col-md-2 d-grid gap-2">
                            <button type="submit" class="btn btn-dark">
                                <i class="mdi mdi-check me-1"></i> Apply Filters
                            </button>
                        </div>
                    </div>
                    @if(filled($permissionNameFilter ?? '') || filled($roleFilter ?? '') || filled($dateFrom ?? '') || filled($dateTo ?? ''))
                        <div class="mt-2 text-end">
                            <a href="{{ route('admin.permissions.index') }}" class="btn btn-sm btn-outline-secondary">
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
                            @php
                                $getSortUrl = function($col) use ($sort, $direction) {
                                    $newDir = ($sort === $col && $direction === 'asc') ? 'desc' : 'asc';
                                    return route('admin.permissions.index', array_merge(request()->query(), ['sort' => $col, 'direction' => $newDir]));
                                };
                                $getSortIcon = function($col) use ($sort, $direction) {
                                    if ($sort !== $col) return 'mdi-minus text-muted';
                                    return $direction === 'asc' ? 'mdi-arrow-up text-primary' : 'mdi-arrow-down text-primary';
                                };
                            @endphp
                            <th><a href="{{ $getSortUrl('name') }}" class="text-dark fw-bold">Permission Name <i class="mdi {{ $getSortIcon('name') }} ml-1"></i></a></th>
                            <th><a href="{{ $getSortUrl('guard_name') }}" class="text-dark fw-bold">Guard <i class="mdi {{ $getSortIcon('guard_name') }} ml-1"></i></a></th>
                            <th>Module</th>
                            <th><a href="{{ $getSortUrl('created_at') }}" class="text-dark fw-bold">Created At <i class="mdi {{ $getSortIcon('created_at') }} ml-1"></i></a></th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="table-border-bottom-0">
                        @forelse($permissions as $permission)
                            @php
                                $parts = explode('_', $permission->name);
                                $module = count($parts) > 1 ? implode(' ', array_slice($parts, 1)) : $permission->name;
                            @endphp
                            <tr>
                                <td class="fw-semibold">{{ $permission->name }}</td>
                                <td>{{ $permission->guard_name }}</td>
                                <td class="text-capitalize">{{ str_replace('_', ' ', $module) }}</td>
                                <td>{{ optional($permission->created_at)->format('Y-m-d H:i') ?? '-' }}</td>
                                <td class="text-end">
                                    <div class="d-inline-flex align-items-center gap-1">
                                        @include('admin.components.action-buttons', [
                                            'type' => 'edit',
                                            'href' => route('admin.permissions.edit', $permission),
                                        ])
                                        @include('admin.components.action-buttons', [
                                            'type' => 'delete',
                                            'formAction' => route('admin.permissions.destroy', $permission),
                                            'confirm' => 'Delete this permission?',
                                        ])
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center py-4 text-muted">No permissions found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            @if($permissions->total() > 0)
                <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mt-4">
                    <div class="d-flex align-items-center gap-2">
                        <span class="text-muted small">Showing {{ $permissions->firstItem() ?? 0 }} to {{ $permissions->lastItem() ?? 0 }} of {{ $permissions->total() }} records</span>
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
                        {{ $permissions->links() }}
                    </div>
                </div>
            @endif
        </div>
    </div>

    </div>
@endsection

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

                        const thead = document.querySelector('table thead');
                        const newThead = doc.querySelector('table thead');
                        if (newThead && thead) {
                            thead.innerHTML = newThead.innerHTML;
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

                        const badgeSelector = '.badge.bg-label-primary';
                        const currentBadge = document.querySelector(badgeSelector);
                        const newBadge = doc.querySelector(badgeSelector);
                        if (currentBadge && newBadge) {
                            currentBadge.innerHTML = newBadge.innerHTML;
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
