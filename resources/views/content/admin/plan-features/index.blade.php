@extends('layouts/contentNavbarLayout')

@section('title', 'Plan Features')

@section('content')
<div class="admin-page plan-features-page">

    <div class="admin-page-header d-flex justify-content-between align-items-center mb-4">
        <div class="admin-page-header__left">
            <h4 class="admin-page-header__title">Plan Features</h4>
            <p class="admin-page-header__subtitle">Manage subscription features list and assign them to pricing plans.</p>
        </div>
        <div>
            <a href="{{ route('admin.plan-features.create') }}" class="btn btn-primary">
                <i class="mdi mdi-plus me-1"></i> Add Plan Feature
            </a>
        </div>
    </div>

    <div class="card mt-4">
        <div class="card-header d-flex justify-content-between align-items-center">
            <div>
                <h5 class="mb-0 fw-bold">All Plan Features</h5>
                <small class="text-muted">Master list of features available for subscription tiers.</small>
            </div>
            <div>
                <span class="badge bg-label-primary"><strong>{{ number_format($features->total()) }}</strong> records</span>
            </div>
        </div>
        <div class="card-body">
            <!-- Filter & Search Form -->
            <form method="GET" id="filter-form" action="{{ route('admin.plan-features.index') }}">
                <input type="hidden" name="sort" value="{{ $sort }}">
                <input type="hidden" name="direction" value="{{ $direction }}">
                <input type="hidden" name="per_page" value="{{ $perPage }}">

                <!-- Global Search Box -->
                <div class="mb-4">
                    <label class="form-label fw-semibold">Global Search</label>
                    <div class="input-group input-group-merge border-primary">
                        <span class="input-group-text"><i class="mdi mdi-magnify text-primary"></i></span>
                        <input type="text" name="search" id="global-search-input" value="{{ $search }}"
                            class="form-control" placeholder="Type feature name, slug or description to search..." autocomplete="off">
                    </div>
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
                            <a href="{{ route('admin.plan-features.index') }}" class="btn btn-sm btn-outline-secondary">
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
                            $getSortUrl = function ($col) use ($sort, $direction, $search, $perPage, $status) {
                                $newDirection = ($sort === $col && $direction === 'asc') ? 'desc' : 'asc';
                                return route('admin.plan-features.index', [
                                    'sort' => $col,
                                    'direction' => $newDirection,
                                    'search' => $search,
                                    'per_page' => $perPage,
                                    'status' => $status,
                                ]);
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
                            <th><a href="{{ $getSortUrl('name') }}" class="text-dark fw-bold">Feature Name <i class="mdi {{ $getSortIcon('name') }} ml-1"></i></a></th>
                            <th><a href="{{ $getSortUrl('slug') }}" class="text-dark fw-bold">Slug <i class="mdi {{ $getSortIcon('slug') }} ml-1"></i></a></th>
                            <th>Linked Plans</th>
                            <th><a href="{{ $getSortUrl('status') }}" class="text-dark fw-bold">Status <i class="mdi {{ $getSortIcon('status') }} ml-1"></i></a></th>
                            <th><a href="{{ $getSortUrl('created_at') }}" class="text-dark fw-bold">Created Date <i class="mdi {{ $getSortIcon('created_at') }} ml-1"></i></a></th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="table-border-bottom-0">
                        @forelse($features as $item)
                            <tr>
                                <td class="cell-muted">{{ $item->id }}</td>
                                <td>
                                    <span class="cell-primary fw-medium">{{ $item->name }}</span>
                                    @if($item->description)
                                        <div class="cell-muted small text-truncate" style="max-width: 250px;">{{ $item->description }}</div>
                                    @endif
                                </td>
                                <td>
                                    <code class="text-primary">{{ $item->slug }}</code>
                                </td>
                                <td>
                                    <span class="badge bg-label-info">{{ $item->subscription_plans_count }} Plans</span>
                                </td>
                                <td>
                                    <span class="badge {{ $item->status === 'active' ? 'bg-label-success' : 'bg-label-secondary' }} text-capitalize">
                                        {{ $item->status }}
                                    </span>
                                </td>
                                <td class="cell-muted">{{ $item->created_at?->format('Y-m-d') }}</td>
                                <td class="text-end">
                                    <div class="d-inline-flex align-items-center gap-1">
                                        @include('admin.components.action-buttons', [
                                            'type'  => 'edit',
                                            'href'  => route('admin.plan-features.edit', $item),
                                            'title' => 'Edit Feature',
                                        ])

                                        @include('admin.components.action-buttons', [
                                            'type'   => 'delete',
                                            'action' => route('admin.plan-features.destroy', $item),
                                            'title'  => 'Delete Feature',
                                            'name'   => $item->name,
                                        ])
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-4 text-muted">No plan features found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            @if ($features->total() > 0)
                <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mt-4">
                    <div class="d-flex align-items-center gap-2">
                        <span class="text-muted small">Showing {{ $features->firstItem() ?? 0 }} to
                            {{ $features->lastItem() ?? 0 }} of {{ $features->total() }} records</span>
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
                        {{ $features->links() }}
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
