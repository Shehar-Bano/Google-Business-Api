@extends('layouts/contentNavbarLayout')

@section('title', 'Google Business Integration')

@section('content')
<div class="admin-page google-business-connections-page">

    <div class="admin-page-header d-flex justify-content-between align-items-center mb-4">
        <div class="admin-page-header__left">
            <h4 class="admin-page-header__title">Google Business Integration</h4>
            <p class="admin-page-header__subtitle">Monitor active Google Business account connections, API status, and place integrations.</p>
        </div>
    </div>

    <div class="card mt-4">
        <div class="card-header d-flex justify-content-between align-items-center">
            <div>
                <h5 class="mb-0 fw-bold">Google Business Account Connections</h5>
                <small class="text-muted">Review place registrations, connected social channels, and keyword ideas availability.</small>
            </div>
            <div>
                <span class="badge bg-label-primary"><strong>{{ number_format($connections->total()) }}</strong> records</span>
            </div>
        </div>
        <div class="card-body">
            <!-- Filter & Search Form -->
            <form method="GET" id="filter-form" action="{{ route('admin.google-business-connections.index') }}">
                <input type="hidden" name="sort" value="{{ $sort }}">
                <input type="hidden" name="direction" value="{{ $direction }}">
                <input type="hidden" name="per_page" value="{{ $perPage }}">

                <!-- Global Search Box -->
                <div class="mb-4">
                    <label class="form-label fw-semibold">Global Search</label>
                    <div class="input-group input-group-merge border-primary">
                        <span class="input-group-text"><i class="mdi mdi-magnify text-primary"></i></span>
                        <input type="text" name="search" id="global-search-input" value="{{ $search }}"
                            class="form-control" placeholder="Type anything to search globally (Name, Location)..." autocomplete="off">
                    </div>
                    <small class="form-text text-muted">Type to search. Filtering happens automatically as you type.</small>
                </div>

                <!-- Separate Filters Panel -->
                <div class="border p-3 rounded mb-4 bg-light">
                    <h6 class="mb-3 fw-bold"><i class="mdi mdi-filter-variant me-1"></i> Filter Options</h6>
                    <div class="row g-3 align-items-end">
                        <div class="col-12 col-md-5">
                            <label class="form-label fw-medium">Location</label>
                            <input type="text" name="location" value="{{ $location ?? '' }}" class="form-control" placeholder="Filter by location...">
                        </div>
                        <div class="col-12 col-md-5">
                            <label class="form-label fw-medium">Verification Status</label>
                            <select name="is_verified" class="form-select">
                                <option value="">All Connections</option>
                                <option value="1" @selected($isVerified === '1')>Verified</option>
                                <option value="0" @selected($isVerified === '0')>Unverified</option>
                            </select>
                        </div>
                        <div class="col-12 col-md-2 d-grid gap-2">
                            <button type="submit" class="btn btn-sm btn-dark w-100" title="Apply Filters">
                                <i class="mdi mdi-check me-1"></i> Apply
                            </button>
                        </div>
                    </div>
                    @if(filled($location) || ($isVerified !== null && $isVerified !== ''))
                        <div class="mt-2 text-end">
                            <a href="{{ route('admin.google-business-connections.index') }}" class="btn btn-sm btn-outline-secondary">
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
                                    'admin.google-business-connections.index',
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
                            <th><a href="{{ $getSortUrl('name') }}" class="text-dark fw-bold">Business Profile <i class="mdi {{ $getSortIcon('name') }} ml-1"></i></a></th>
                            <th><a href="{{ $getSortUrl('google_place_id') }}" class="text-dark fw-bold">Google Place ID <i class="mdi {{ $getSortIcon('google_place_id') }} ml-1"></i></a></th>
                            <th>Social Connections</th>
                            <th><a href="{{ $getSortUrl('isVerified') }}" class="text-dark fw-bold">Verification <i class="mdi {{ $getSortIcon('isVerified') }} ml-1"></i></a></th>
                            <th>Stored Keywords</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="table-border-bottom-0">
                        @forelse($connections as $connection)
                            <tr>
                                <td class="cell-muted">{{ $connection->id }}</td>
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        @if($connection->brand_logo)
                                            <img src="{{ asset($connection->brand_logo) }}" alt="Logo" class="rounded" style="width: 32px; height: 32px; object-fit: cover;">
                                        @else
                                            <div class="um-user-avatar bg-label-primary" style="width: 32px; height: 32px; font-size: 0.8rem; display: flex; align-items: center; justify-content: center; border-radius: 8px;">
                                                {{ strtoupper(substr($connection->name, 0, 1)) }}
                                            </div>
                                        @endif
                                        <div>
                                            <div class="cell-primary fw-semibold">{{ $connection->name }}</div>
                                            <small class="text-muted">{{ $connection->location }}</small>
                                        </div>
                                    </div>
                                </td>
                                <td class="font-monospace text-xs text-secondary">
                                    {{ $connection->google_place_id ?: '— Not Linked —' }}
                                </td>
                                <td>
                                    @if($connection->user && $connection->user->socialAccounts->count() > 0)
                                        <div class="d-flex flex-wrap gap-1">
                                            @foreach($connection->user->socialAccounts as $account)
                                                @php
                                                    $badgeClass = 'bg-label-secondary';
                                                    if (strtolower($account->provider) === 'google') $badgeClass = 'bg-label-danger';
                                                    elseif (strtolower($account->provider) === 'facebook') $badgeClass = 'bg-label-primary';
                                                    elseif (strtolower($account->provider) === 'instagram') $badgeClass = 'bg-label-warning';
                                                @endphp
                                                <span class="badge {{ $badgeClass }} text-capitalize d-inline-flex align-items-center gap-1">
                                                    <i class="mdi mdi-link-variant"></i> {{ $account->provider }}
                                                </span>
                                            @endforeach
                                        </div>
                                    @else
                                        <span class="badge bg-label-secondary d-inline-flex align-items-center gap-1">
                                            <i class="mdi mdi-alert-circle-outline"></i> Disconnected
                                        </span>
                                    @endif
                                </td>
                                <td>
                                    @if($connection->isVerified)
                                        <span class="badge bg-label-primary">Verified</span>
                                    @else
                                        <span class="badge bg-label-warning">Unverified</span>
                                    @endif
                                </td>
                                <td>
                                    <span class="badge bg-label-info fw-bold">
                                        {{ $connection->keywordIdeas->count() }} Keywords
                                    </span>
                                </td>
                                <td class="text-end">
                                    <div class="d-inline-flex align-items-center gap-1">
                                        <a href="{{ route('admin.business-management.keyword-ideas', $connection) }}" class="btn btn-outline-primary btn-xs d-inline-flex align-items-center gap-1" title="View Keywords">
                                            <i class="mdi mdi-google-ads"></i> View Keywords
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-4 text-muted">No business connections found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            @if ($connections->hasPages())
                <div class="d-flex justify-content-between align-items-center mt-4">
                    <div>
                        <span class="text-muted small">Showing {{ $connections->firstItem() ?? 0 }} to
                            {{ $connections->lastItem() ?? 0 }} of {{ $connections->total() }} records</span>
                    </div>
                    <div>
                        {{ $connections->links() }}
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
</style>
@endpush

@push('my-script')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const searchInput = document.getElementById('global-search-input');
            const filterForm = document.getElementById('filter-form');
            let searchTimeout;

            if (searchInput && filterForm) {
                searchInput.addEventListener('input', function() {
                    clearTimeout(searchTimeout);
                    searchTimeout = setTimeout(() => {
                        filterForm.submit();
                    }, 400);
                });

                const length = searchInput.value.length;
                if (length > 0) {
                    searchInput.focus();
                    searchInput.setSelectionRange(length, length);
                }
            }
        });
    </script>
@endpush
