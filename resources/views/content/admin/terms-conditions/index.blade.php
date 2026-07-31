@extends('layouts/contentNavbarLayout')

@section('title', 'Terms & Conditions')

@section('content')
<div class="admin-page terms-conditions-page">

    <div class="admin-page-header d-flex justify-content-between align-items-center mb-4">
        <div class="admin-page-header__left">
            <h4 class="admin-page-header__title">Terms & Conditions</h4>
            <p class="admin-page-header__subtitle">Manage terms, user agreements, and disclaimer pages served to client applications.</p>
        </div>
    </div>

    <div class="card mt-4">
        <div class="card-header d-flex justify-content-between align-items-center">
            <div>
                <h5 class="mb-0 fw-bold">Terms and Agreements</h5>
                <small class="text-muted">Configure policies and standard user terms rules.</small>
            </div>
            <div>
                <span class="badge bg-label-primary"><strong>{{ number_format($terms->total()) }}</strong> records</span>
                <a href="{{ route('admin.terms-conditions.create') }}" class="btn btn-sm btn-primary ms-2">
                    <i class="mdi mdi-plus me-1"></i> Add Terms Page
                </a>
            </div>
        </div>
        <div class="card-body">
            <!-- Filter & Search Form -->
            <form method="GET" id="filter-form" action="{{ route('admin.terms-conditions.index') }}">
                <input type="hidden" name="sort" value="{{ $sort }}">
                <input type="hidden" name="direction" value="{{ $direction }}">
                <input type="hidden" name="per_page" value="{{ $perPage }}">

                <!-- Global Search Box (No Button, Auto Filters on Keystroke) -->
                <div class="mb-4">
                    <label class="form-label fw-semibold">Global Search</label>
                    <div class="input-group input-group-merge border-primary">
                        <span class="input-group-text"><i class="mdi mdi-magnify text-primary"></i></span>
                        <input type="text" name="search" id="global-search-input" value="{{ $search }}" class="form-control" placeholder="Type anything to search (Title, Slug, or Content)..." autocomplete="off">
                    </div>
                    <small class="form-text text-muted">Type to search. Filtering happens automatically as you type.</small>
                </div>

                <!-- Separate Filters Panel (With Apply Button) -->
                <div class="border p-3 rounded mb-4 bg-light">
                    <h6 class="mb-3 fw-bold"><i class="mdi mdi-filter-variant me-1"></i> Filter Options</h6>
                    <div class="row g-3 align-items-end">
                        <div class="col-12 col-md-3">
                            <label class="form-label fw-medium">Status</label>
                            <select name="status" class="form-select">
                                <option value="">All Statuses</option>
                                <option value="Active" @selected(($status ?? '') === 'Active')>Active</option>
                                <option value="Inactive" @selected(($status ?? '') === 'Inactive')>Inactive</option>
                            </select>
                        </div>
                        <div class="col-12 col-md-3">
                            <label class="form-label fw-medium">Date From</label>
                            <input type="date" name="date_from" value="{{ $dateFrom ?? '' }}" class="form-control">
                        </div>
                        <div class="col-12 col-md-3">
                            <label class="form-label fw-medium">Date To</label>
                            <input type="date" name="date_to" value="{{ $dateTo ?? '' }}" class="form-control">
                        </div>
                        <div class="col-12 col-md-3 d-grid gap-2">
                            <button type="submit" class="btn btn-dark">
                                <i class="mdi mdi-check me-1"></i> Apply Filters
                            </button>
                        </div>
                    </div>
                    @if(filled($status ?? '') || filled($dateFrom ?? '') || filled($dateTo ?? ''))
                        <div class="mt-2 text-end">
                            <a href="{{ route('admin.terms-conditions.index') }}" class="btn btn-sm btn-outline-secondary">
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
                                    return route('admin.terms-conditions.index', array_merge(request()->query(), ['sort' => $col, 'direction' => $newDir]));
                                };
                                $getSortIcon = function($col) use ($sort, $direction) {
                                    if ($sort !== $col) return 'mdi-minus text-muted';
                                    return $direction === 'asc' ? 'mdi-arrow-up text-primary' : 'mdi-arrow-down text-primary';
                                };
                            @endphp
                            <th><a href="{{ $getSortUrl('id') }}" class="text-dark fw-bold"># <i class="mdi {{ $getSortIcon('id') }} ml-1"></i></a></th>
                            <th><a href="{{ $getSortUrl('title') }}" class="text-dark fw-bold">Page Title <i class="mdi {{ $getSortIcon('title') }} ml-1"></i></a></th>
                            <th><a href="{{ $getSortUrl('slug') }}" class="text-dark fw-bold">URL Slug <i class="mdi {{ $getSortIcon('slug') }} ml-1"></i></a></th>
                            <th><a href="{{ $getSortUrl('status') }}" class="text-dark fw-bold">Status <i class="mdi {{ $getSortIcon('status') }} ml-1"></i></a></th>
                            <th><a href="{{ $getSortUrl('created_at') }}" class="text-dark fw-bold">Created At <i class="mdi {{ $getSortIcon('created_at') }} ml-1"></i></a></th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="table-border-bottom-0">
                        @forelse($terms as $term)
                            <tr>
                                <td class="cell-muted">{{ $term->id }}</td>
                                <td><strong class="cell-primary">{{ $term->title }}</strong></td>
                                <td class="font-monospace text-xs text-secondary">{{ $term->slug }}</td>
                                <td>
                                    @if($term->status === 'Active')
                                        <span class="badge bg-label-success">Active</span>
                                    @else
                                        <span class="badge bg-label-danger">Inactive</span>
                                    @endif
                                </td>
                                <td class="cell-muted">{{ $term->created_at?->format('Y-m-d H:i') ?? '—' }}</td>
                                <td class="text-end">
                                    <div class="d-inline-flex align-items-center gap-1">
                                        @include('admin.components.action-buttons', [
                                            'type'  => 'edit',
                                            'href'  => route('admin.terms-conditions.edit', $term),
                                            'title' => 'Edit Terms',
                                        ])
                                        <form action="{{ route('admin.terms-conditions.destroy', $term) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this Terms & Conditions page?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="action-icon-btn action-icon-delete border-0 bg-transparent" title="Delete Terms">
                                                <i class="mdi mdi-trash-can-outline text-danger"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-4 text-muted">No Terms & Conditions found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            @if($terms->hasPages())
                <div class="d-flex justify-content-between align-items-center mt-4">
                    <div>
                        <span class="text-muted small">Showing {{ $terms->firstItem() ?? 0 }} to {{ $terms->lastItem() ?? 0 }} of {{ $terms->total() }} records</span>
                    </div>
                    <div>
                        {{ $terms->links() }}
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
