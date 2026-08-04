@extends('layouts/contentNavbarLayout')

@section('title', 'Roles')

@section('content')
@php
    $protectedRoles = ['super_admin', 'admin'];
@endphp

    <div class="admin-page roles-page">

        <div class="admin-page-header d-flex justify-content-between align-items-center mb-4">
            <div class="admin-page-header__left">
                <h4 class="admin-page-header__title">Roles</h4>
                <p class="admin-page-header__subtitle">Manage roles and permission boundaries.</p>
            </div>
        </div>

    <div class="card mt-4">
        <div class="card-header d-flex justify-content-between align-items-center">
            <div>
                <h5 class="mb-0 fw-bold">Roles</h5>
                <small class="text-muted">Manage roles and permission boundaries.</small>
            </div>
            <div>
                <span class="badge bg-label-primary"><strong>{{ number_format($roles->total()) }}</strong> records</span>
                <a href="{{ route('admin.roles.create') }}" class="btn btn-sm btn-primary ms-2">
                    <i class="mdi mdi-plus me-1"></i> Create Role
                </a>
            </div>
        </div>
        <div class="card-body">
            <!-- Filter & Search Form -->
            <form method="GET" id="filter-form" action="{{ route('admin.roles.index') }}">
                <input type="hidden" name="sort" value="{{ $sort }}">
                <input type="hidden" name="direction" value="{{ $direction }}">
                <input type="hidden" name="per_page" value="{{ $perPage }}">

                <!-- Global Search Box (No Button, Auto Filters on Keystroke) -->
                <div class="mb-4">
                    <label class="form-label fw-semibold">Global Search</label>
                    <div class="input-group input-group-merge border-primary">
                        <span class="input-group-text"><i class="mdi mdi-magnify text-primary"></i></span>
                        <input type="text" name="search" id="global-search-input" value="{{ $search }}" class="form-control" placeholder="Type anything to search (Name)..." autocomplete="off">
                    </div>
                    <small class="form-text text-muted">Type to search. Filtering happens automatically as you type.</small>
                </div>

                <!-- Separate Filters Panel (With Apply Button) -->
                <div class="border p-3 rounded mb-4 bg-light">
                    <h6 class="mb-3 fw-bold"><i class="mdi mdi-filter-variant me-1"></i> Filter Options</h6>
                    <div class="row g-3 align-items-end">
                        <div class="col-12 col-md-5">
                            <label class="form-label fw-medium">Date From</label>
                            <input type="date" name="date_from" value="{{ $dateFrom ?? '' }}" class="form-control">
                        </div>
                        <div class="col-12 col-md-5">
                            <label class="form-label fw-medium">Date To</label>
                            <input type="date" name="date_to" value="{{ $dateTo ?? '' }}" class="form-control">
                        </div>
                        <div class="col-12 col-md-2 d-grid gap-2">
                            <button type="submit" class="btn btn-dark">
                                <i class="mdi mdi-check me-1"></i> Apply Filters
                            </button>
                        </div>
                    </div>
                    @if(filled($dateFrom ?? '') || filled($dateTo ?? ''))
                        <div class="mt-2 text-end">
                            <a href="{{ route('admin.roles.index') }}" class="btn btn-sm btn-outline-secondary">
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
                                    return route('admin.roles.index', array_merge(request()->query(), ['sort' => $col, 'direction' => $newDir]));
                                };
                                $getSortIcon = function($col) use ($sort, $direction) {
                                    if ($sort !== $col) return 'mdi-minus text-muted';
                                    return $direction === 'asc' ? 'mdi-arrow-up text-primary' : 'mdi-arrow-down text-primary';
                                };
                            @endphp
                            <th><a href="{{ $getSortUrl('name') }}" class="text-dark fw-bold">Role Name <i class="mdi {{ $getSortIcon('name') }} ml-1"></i></a></th>
                            <th><a href="{{ $getSortUrl('permissions_count') }}" class="text-dark fw-bold">Permissions Count <i class="mdi {{ $getSortIcon('permissions_count') }} ml-1"></i></a></th>
                            <th><a href="{{ $getSortUrl('created_at') }}" class="text-dark fw-bold">Created At <i class="mdi {{ $getSortIcon('created_at') }} ml-1"></i></a></th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="table-border-bottom-0">
                        @forelse($roles as $role)
                            <tr>
                                <td class="fw-semibold">{{ $role->name }}</td>
                                <td>{{ $role->permissions_count }}</td>
                                <td>{{ optional($role->created_at)->format('Y-m-d H:i') ?? '-' }}</td>
                                <td class="text-end">
                                    <div class="d-inline-flex align-items-center gap-1">
                                        @include('admin.components.action-buttons', [
                                            'type' => 'permission',
                                            'href' => route('admin.roles.edit', $role),
                                            'title' => 'Assign Permissions',
                                        ])
                                        @include('admin.components.action-buttons', [
                                            'type' => 'edit',
                                            'href' => route('admin.roles.edit', $role),
                                        ])
                                        @if(!in_array($role->name, $protectedRoles, true))
                                            @include('admin.components.action-buttons', [
                                                'type' => 'delete',
                                                'formAction' => route('admin.roles.destroy', $role),
                                                'confirm' => 'Delete this role?',
                                            ])
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="text-center py-4 text-muted">No roles found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            @if($roles->total() > 0)
                <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mt-4">
                    <div class="d-flex align-items-center gap-2">
                        <span class="text-muted small">Showing {{ $roles->firstItem() ?? 0 }} to {{ $roles->lastItem() ?? 0 }} of {{ $roles->total() }} records</span>
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
                        {{ $roles->links() }}
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
