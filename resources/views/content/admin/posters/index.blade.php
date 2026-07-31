@extends('layouts/contentNavbarLayout')

@section('title', 'Poster Management')

@section('content')
<div class="admin-page posters-page">

    <div class="admin-page-header d-flex justify-content-between align-items-center mb-4">
        <div class="admin-page-header__left">
            <h4 class="admin-page-header__title">Poster Templates</h4>
            <p class="admin-page-header__subtitle">Manage base designs and templates used by users to generate AI marketing posters.</p>
        </div>
        <div>
            <a href="{{ route('admin.posters.create') }}" class="btn btn-primary">
                <i class="mdi mdi-plus me-1"></i> Add Template
            </a>
        </div>
    </div>

    <div class="card mt-4">
        <div class="card-header d-flex justify-content-between align-items-center">
            <div>
                <h5 class="mb-0 fw-bold">Poster Templates</h5>
                <small class="text-muted">Search and manage poster templates by title and status.</small>
            </div>
            <div>
                <span class="badge bg-label-primary"><strong>{{ number_format($posters->total()) }}</strong> records</span>
            </div>
        </div>
        <div class="card-body">
            <!-- Filter & Search Form -->
            <form method="GET" id="filter-form" action="{{ route('admin.posters.index') }}">
                <input type="hidden" name="sort" value="{{ $sort }}">
                <input type="hidden" name="direction" value="{{ $direction }}">
                <input type="hidden" name="per_page" value="{{ $perPage }}">

                <!-- Global Search Box -->
                <div class="mb-4">
                    <label class="form-label fw-semibold">Global Search</label>
                    <div class="input-group input-group-merge border-primary">
                        <span class="input-group-text"><i class="mdi mdi-magnify text-primary"></i></span>
                        <input type="text" name="search" id="global-search-input" value="{{ $search }}"
                            class="form-control" placeholder="Type template title to search globally..." autocomplete="off">
                    </div>
                    <small class="form-text text-muted">Type to search. Filtering happens automatically as you type.</small>
                </div>

                <!-- Separate Filters Panel -->
                <div class="border p-3 rounded mb-4 bg-light">
                    <h6 class="mb-3 fw-bold"><i class="mdi mdi-filter-variant me-1"></i> Filter Options</h6>
                    <div class="row g-3 align-items-end">
                        <div class="col-12 col-md-10">
                            <label class="form-label fw-medium">Status</label>
                            <select name="status" class="form-select">
                                <option value="">All Statuses</option>
                                <option value="Active" @selected($status === 'Active')>Active</option>
                                <option value="Inactive" @selected($status === 'Inactive')>Inactive</option>
                            </select>
                        </div>
                        <div class="col-12 col-md-2 d-grid gap-2">
                            <button type="submit" class="btn btn-sm btn-dark w-100" title="Apply Filters">
                                <i class="mdi mdi-check me-1"></i> Apply
                            </button>
                        </div>
                    </div>
                    @if(filled($status))
                        <div class="mt-2 text-end">
                            <a href="{{ route('admin.posters.index') }}" class="btn btn-sm btn-outline-secondary">
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
                                    'admin.posters.index',
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
                            <th>#</th>
                            <th>Template Image</th>
                            <th><a href="{{ $getSortUrl('title') }}" class="text-dark fw-bold">Title <i class="mdi {{ $getSortIcon('title') }} ml-1"></i></a></th>
                            <th><a href="{{ $getSortUrl('status') }}" class="text-dark fw-bold">Status <i class="mdi {{ $getSortIcon('status') }} ml-1"></i></a></th>
                            <th><a href="{{ $getSortUrl('created_at') }}" class="text-dark fw-bold">Created At <i class="mdi {{ $getSortIcon('created_at') }} ml-1"></i></a></th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="table-border-bottom-0">
                        @forelse($posters as $poster)
                            <tr>
                                <td class="cell-muted">{{ $poster->id }}</td>
                                <td>
                                    <img src="{{ asset($poster->image) }}" alt="{{ $poster->title }}" class="rounded" style="width: 50px; height: 50px; object-fit: cover; border: 1px solid #e2e8f0;">
                                </td>
                                <td class="cell-primary fw-semibold">{{ $poster->title }}</td>
                                <td>
                                    <span class="badge {{ $poster->status === 'Active' ? 'bg-label-success' : 'bg-label-danger' }}">
                                        {{ $poster->status }}
                                    </span>
                                </td>
                                <td class="cell-muted">{{ $poster->created_at?->format('Y-m-d') }}</td>
                                <td class="text-end">
                                    <div class="d-inline-flex align-items-center gap-1">
                                        @include('admin.components.action-buttons', [
                                            'type'  => 'view',
                                            'href'  => route('admin.posters.show', $poster),
                                            'title' => 'View Template',
                                        ])
                                        @include('admin.components.action-buttons', [
                                            'type'  => 'edit',
                                            'href'  => route('admin.posters.edit', $poster),
                                            'title' => 'Edit Template',
                                        ])
                                        <form action="{{ route('admin.posters.destroy', $poster) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this template?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="action-icon-btn action-icon-delete border-0 bg-transparent" title="Delete Template">
                                                <i class="mdi mdi-trash-can-outline text-danger"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-4 text-muted">No poster templates found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            @if ($posters->hasPages())
                <div class="d-flex justify-content-between align-items-center mt-4">
                    <div>
                        <span class="text-muted small">Showing {{ $posters->firstItem() ?? 0 }} to
                            {{ $posters->lastItem() ?? 0 }} of {{ $posters->total() }} records</span>
                    </div>
                    <div>
                        {{ $posters->links() }}
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


