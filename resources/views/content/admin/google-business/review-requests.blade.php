@extends('layouts/contentNavbarLayout')

@section('title', 'WhatsApp Review Requests')

@section('content')
<div class="admin-page whatsapp-review-requests-page">

    <div class="admin-page-header d-flex justify-content-between align-items-center mb-4">
        <div class="admin-page-header__left">
            <h4 class="admin-page-header__title">WhatsApp Review Requests</h4>
            <p class="admin-page-header__subtitle">Monitor Google Business review invitation messages sent to customers via WhatsApp.</p>
        </div>
    </div>

    <div class="card mt-4">
        <div class="card-header d-flex justify-content-between align-items-center">
            <div>
                <h5 class="mb-0 fw-bold">WhatsApp & SMS Review Invites</h5>
                <small class="text-muted">Track sending status, user views, and review completions.</small>
            </div>
            <div>
                <span class="badge bg-label-primary"><strong>{{ number_format($requests->total()) }}</strong> records</span>
            </div>
        </div>
        <div class="card-body">
            <!-- Filter & Search Form -->
            <form method="GET" id="filter-form" action="{{ route('admin.whatsapp-review-requests.index') }}">
                <input type="hidden" name="sort" value="{{ $sort }}">
                <input type="hidden" name="direction" value="{{ $direction }}">
                <input type="hidden" name="per_page" value="{{ $perPage }}">

                <!-- Global Search Box -->
                <div class="mb-4">
                    <label class="form-label fw-semibold">Global Search</label>
                    <div class="input-group input-group-merge border-primary">
                        <span class="input-group-text"><i class="mdi mdi-magnify text-primary"></i></span>
                        <input type="text" name="search" id="global-search-input" value="{{ $search }}"
                            class="form-control" placeholder="Type anything to search globally (Phone, status, Business)..." autocomplete="off">
                    </div>
                    <small class="form-text text-muted">Type to search. Filtering happens automatically as you type.</small>
                </div>

                <!-- Separate Filters Panel -->
                <div class="border p-3 rounded mb-4 bg-light">
                    <h6 class="mb-3 fw-bold"><i class="mdi mdi-filter-variant me-1"></i> Filter Options</h6>
                    <div class="row g-3 align-items-end">
                        <div class="col-12 col-md-5">
                            <label class="form-label fw-medium">Status</label>
                            <select name="status" class="form-select">
                                <option value="">All Statuses</option>
                                <option value="sent" @selected($status === 'sent')>Sent</option>
                                <option value="clicked" @selected($status === 'clicked')>Clicked</option>
                                <option value="reviewed" @selected($status === 'reviewed')>Reviewed</option>
                                <option value="failed" @selected($status === 'failed')>Failed</option>
                            </select>
                        </div>
                        <div class="col-12 col-md-5">
                            <label class="form-label fw-medium">Channel</label>
                            <select name="channel" class="form-select">
                                <option value="">All Channels</option>
                                <option value="personal" @selected($channel === 'personal')>Personal</option>
                                <option value="app" @selected($channel === 'app')>App</option>
                            </select>
                        </div>
                        <div class="col-12 col-md-2 d-grid gap-2">
                            <button type="submit" class="btn btn-sm btn-dark w-100" title="Apply Filters">
                                <i class="mdi mdi-check me-1"></i> Apply
                            </button>
                        </div>
                    </div>
                    @if(filled($status) || filled($channel))
                        <div class="mt-2 text-end">
                            <a href="{{ route('admin.whatsapp-review-requests.index') }}" class="btn btn-sm btn-outline-secondary">
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
                                    'admin.whatsapp-review-requests.index',
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
                            <th><a href="{{ $getSortUrl('business') }}" class="text-dark fw-bold">Business Profile <i class="mdi {{ $getSortIcon('business') }} ml-1"></i></a></th>
                            <th><a href="{{ $getSortUrl('sender') }}" class="text-dark fw-bold">Sender Admin/User <i class="mdi {{ $getSortIcon('sender') }} ml-1"></i></a></th>
                            <th><a href="{{ $getSortUrl('sent_to_user') }}" class="text-dark fw-bold">Sent To Customer <i class="mdi {{ $getSortIcon('sent_to_user') }} ml-1"></i></a></th>
                            <th><a href="{{ $getSortUrl('phone_number') }}" class="text-dark fw-bold">Phone Number <i class="mdi {{ $getSortIcon('phone_number') }} ml-1"></i></a></th>
                            <th><a href="{{ $getSortUrl('channel') }}" class="text-dark fw-bold">Channel <i class="mdi {{ $getSortIcon('channel') }} ml-1"></i></a></th>
                            <th><a href="{{ $getSortUrl('status') }}" class="text-dark fw-bold">Status <i class="mdi {{ $getSortIcon('status') }} ml-1"></i></a></th>
                            <th><a href="{{ $getSortUrl('created_at') }}" class="text-dark fw-bold">Sent At <i class="mdi {{ $getSortIcon('created_at') }} ml-1"></i></a></th>
                        </tr>
                    </thead>
                    <tbody class="table-border-bottom-0">
                        @forelse($requests as $req)
                            @php
                                $statusBadge = match($req->status) {
                                    'sent'     => 'bg-label-info',
                                    'clicked'  => 'bg-label-warning',
                                    'reviewed' => 'bg-label-success',
                                    'failed'   => 'bg-label-danger',
                                    default    => 'bg-label-secondary',
                                };
                            @endphp
                            <tr>
                                <td class="cell-muted">{{ $req->id }}</td>
                                <td>
                                    @if($req->business)
                                        <div class="cell-primary fw-semibold">{{ $req->business->name }}</div>
                                        <small class="text-muted">{{ $req->business->location }}</small>
                                    @else
                                        <span class="text-muted">— Business Deleted —</span>
                                    @endif
                                </td>
                                <td>
                                    @if($req->sender)
                                        <div class="cell-primary fw-medium">{{ $req->sender->name }}</div>
                                        <small class="text-muted">{{ $req->sender->email }}</small>
                                    @else
                                        <span class="text-muted">— System Auto —</span>
                                    @endif
                                </td>
                                <td>
                                    @if($req->sentToUser)
                                        <div class="cell-primary fw-medium">{{ $req->sentToUser->name }}</div>
                                    @else
                                        <span class="text-secondary fw-semibold">— Guest Customer —</span>
                                    @endif
                                </td>
                                <td class="font-monospace text-xs text-dark">{{ $req->phone_number }}</td>
                                <td>
                                    <span class="badge {{ $req->channel === 'personal' ? 'bg-label-success' : 'bg-label-primary' }} text-uppercase d-inline-flex align-items-center gap-1">
                                        @if($req->channel === 'personal')
                                            <i class="mdi mdi-account-outline text-success"></i> Personal
                                        @else
                                            <i class="mdi mdi-apps text-primary"></i> App
                                        @endif
                                    </span>
                                </td>
                                <td>
                                    <div class="d-inline-block">
                                        <span class="badge {{ $statusBadge }} text-capitalize d-block mb-1">{{ $req->status }}</span>
                                        @if($req->status === 'failed' && $req->failure_reason)
                                            <small class="text-danger d-block" style="max-width: 150px; font-size: 0.7rem; white-space: normal;" title="{{ $req->failure_reason }}">
                                                {{ $req->failure_reason }}
                                            </small>
                                        @endif
                                    </div>
                                </td>
                                <td class="cell-muted">{{ $req->created_at?->format('Y-m-d H:i') ?? '—' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center py-4 text-muted">No WhatsApp review requests found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            @if ($requests->hasPages())
                <div class="d-flex justify-content-between align-items-center mt-4">
                    <div>
                        <span class="text-muted small">Showing {{ $requests->firstItem() ?? 0 }} to
                            {{ $requests->lastItem() ?? 0 }} of {{ $requests->total() }} records</span>
                    </div>
                    <div>
                        {{ $requests->links() }}
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


