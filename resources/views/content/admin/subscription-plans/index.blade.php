@extends('layouts/contentNavbarLayout')

@section('title', 'Subscription Plans')

@section('content')
<div class="admin-page subscription-plans-page">

    <div class="admin-page-header d-flex justify-content-between align-items-center mb-4">
        <div class="admin-page-header__left">
            <h4 class="admin-page-header__title">Subscription Plans</h4>
            <p class="admin-page-header__subtitle">Manage subscription pricing plans, features, and active packages.</p>
        </div>
        <div>
            <a href="{{ route('admin.subscription-plans.create') }}" class="btn btn-primary">
                <i class="mdi mdi-plus me-1"></i> Add Pricing Plan
            </a>
        </div>
    </div>

    <div class="card mt-4">
        <div class="card-header d-flex justify-content-between align-items-center">
            <div>
                <h5 class="mb-0 fw-bold">Pricing Plans</h5>
                <small class="text-muted">Configure tiers, features list, and billing models.</small>
            </div>
            <div>
                <span class="badge bg-label-primary"><strong>{{ number_format($plans->total()) }}</strong> records</span>
            </div>
        </div>
        <div class="card-body">
            <!-- Filter & Search Form -->
            <form method="GET" id="filter-form" action="{{ route('admin.subscription-plans.index') }}">
                <input type="hidden" name="sort" value="{{ $sort }}">
                <input type="hidden" name="direction" value="{{ $direction }}">
                <input type="hidden" name="per_page" value="{{ $perPage }}">

                <!-- Global Search Box -->
                <div class="mb-4">
                    <label class="form-label fw-semibold">Global Search</label>
                    <div class="input-group input-group-merge border-primary">
                        <span class="input-group-text"><i class="mdi mdi-magnify text-primary"></i></span>
                        <input type="text" name="search" id="global-search-input" value="{{ $search }}"
                            class="form-control" placeholder="Type plan title to search globally..." autocomplete="off">
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
                                <option value="active" @selected($status === 'active')>Active</option>
                                <option value="inactive" @selected($status === 'inactive')>Inactive</option>
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
                            <a href="{{ route('admin.subscription-plans.index') }}" class="btn btn-sm btn-outline-secondary">
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
                                    'admin.subscription-plans.index',
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
                            <th><a href="{{ $getSortUrl('title') }}" class="text-dark fw-bold">Plan Title <i class="mdi {{ $getSortIcon('title') }} ml-1"></i></a></th>
                            <th><a href="{{ $getSortUrl('price') }}" class="text-dark fw-bold">Price <i class="mdi {{ $getSortIcon('price') }} ml-1"></i></a></th>
                            <th><a href="{{ $getSortUrl('billing_period') }}" class="text-dark fw-bold">Billing Period <i class="mdi {{ $getSortIcon('billing_period') }} ml-1"></i></a></th>
                            <th><a href="{{ $getSortUrl('is_popular') }}" class="text-dark fw-bold">Popularity <i class="mdi {{ $getSortIcon('is_popular') }} ml-1"></i></a></th>
                            <th>Included Features</th>
                            <th><a href="{{ $getSortUrl('status') }}" class="text-dark fw-bold">Status <i class="mdi {{ $getSortIcon('status') }} ml-1"></i></a></th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="table-border-bottom-0">
                        @forelse($plans as $plan)
                            <tr>
                                <td class="cell-muted">{{ $plan->id }}</td>
                                <td><strong class="cell-primary">{{ $plan->title }}</strong></td>
                                <td>
                                    <span class="fw-bold text-success">Rs. {{ number_format($plan->price, 2) }}</span>
                                </td>
                                <td class="cell-primary text-capitalize">{{ $plan->billing_period }}</td>
                                <td>
                                    @if($plan->is_popular)
                                        <span class="badge bg-label-primary fw-semibold d-inline-flex align-items-center gap-1"><i class="mdi mdi-star"></i> Popular</span>
                                    @else
                                        <span class="text-muted small">— Standard —</span>
                                    @endif
                                </td>
                                <td>
                                    @if($plan->features && $plan->features->count() > 0)
                                        <div class="d-flex flex-wrap gap-1" style="max-width: 280px;">
                                            @foreach($plan->features as $feature)
                                                <span class="badge bg-label-primary font-monospace py-1" style="font-size: 11px;">
                                                    <i class="mdi mdi-check me-1 text-success"></i>{{ $feature->name }}
                                                </span>
                                            @endforeach
                                        </div>
                                    @else
                                        <span class="text-muted small">No features added.</span>
                                    @endif
                                </td>
                                <td>
                                    @if($plan->status === 'active')
                                        <span class="badge bg-label-success">Active</span>
                                    @else
                                        <span class="badge bg-label-danger">Inactive</span>
                                    @endif
                                </td>
                                <td class="text-end">
                                    <div class="d-inline-flex align-items-center gap-1">
                                        @include('admin.components.action-buttons', [
                                            'type'  => 'edit',
                                            'href'  => route('admin.subscription-plans.edit', $plan),
                                            'title' => 'Edit Plan',
                                        ])
                                        <form action="{{ route('admin.subscription-plans.destroy', $plan) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this subscription plan?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="action-icon-btn action-icon-delete border-0 bg-transparent" title="Delete Plan">
                                                <i class="mdi mdi-trash-can-outline text-danger"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center py-4 text-muted">No subscription plans found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            @if ($plans->total() > 0)
                <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mt-4">
                    <div class="d-flex align-items-center gap-2">
                        <span class="text-muted small">Showing {{ $plans->firstItem() ?? 0 }} to
                            {{ $plans->lastItem() ?? 0 }} of {{ $plans->total() }} records</span>
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
                        {{ $plans->links() }}
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
