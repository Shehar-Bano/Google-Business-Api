@extends('layouts/contentNavbarLayout')

@section('title', 'Help & Support')

@section('content')
<div class="admin-page support-options-page">

    <div class="admin-page-header mb-4">
        <div class="admin-page-header__left">
            <h4 class="admin-page-header__title">Help & Support</h4>
            <p class="admin-page-header__subtitle">Manage support options shown in the app.</p>
        </div>
    </div>

    <div class="support-stats-grid mb-4">
        <div class="support-stat-card">
            <div class="support-stat-card__icon support-stat-card__icon--primary"><i class="mdi mdi-lifebuoy"></i></div>
            <div>
                <div class="support-stat-card__label">Total Options</div>
                <div class="support-stat-card__value">{{ number_format($stats['total']) }}</div>
            </div>
        </div>
        <div class="support-stat-card">
            <div class="support-stat-card__icon support-stat-card__icon--success"><i class="mdi mdi-check-circle-outline"></i></div>
            <div>
                <div class="support-stat-card__label">Active</div>
                <div class="support-stat-card__value">{{ number_format($stats['active']) }}</div>
            </div>
        </div>
        <div class="support-stat-card">
            <div class="support-stat-card__icon support-stat-card__icon--secondary"><i class="mdi mdi-pause-circle-outline"></i></div>
            <div>
                <div class="support-stat-card__label">Inactive</div>
                <div class="support-stat-card__value">{{ number_format($stats['inactive']) }}</div>
            </div>
        </div>
    </div>

    <div class="card mt-4">
        <div class="card-header d-flex justify-content-between align-items-center">
            <div>
                <h5 class="mb-0 fw-bold">Support Options</h5>
                <small class="text-muted">Search by title, type, or support value.</small>
            </div>
            <div>
                <span class="badge bg-label-primary"><strong>{{ number_format($supportOptions->total()) }}</strong> records</span>
                <a href="{{ route('admin.support-options.create') }}" class="btn btn-sm btn-primary ms-2">
                    <i class="mdi mdi-plus me-1"></i> Add Option
                </a>
            </div>
        </div>
        <div class="card-body">
            <!-- Filter & Search Form -->
            <form method="GET" id="filter-form" action="{{ route('admin.support-options.index') }}">
                <input type="hidden" name="sort" value="{{ $sort }}">
                <input type="hidden" name="direction" value="{{ $direction }}">
                <input type="hidden" name="per_page" value="{{ $perPage }}">

                <!-- Global Search Box (No Button, Auto Filters on Keystroke) -->
                <div class="mb-4">
                    <label class="form-label fw-semibold">Global Search</label>
                    <div class="input-group input-group-merge border-primary">
                        <span class="input-group-text"><i class="mdi mdi-magnify text-primary"></i></span>
                        <input type="text" name="search" id="global-search-input" value="{{ $search }}" class="form-control" placeholder="Type anything to search (Title, Type, or Value)..." autocomplete="off">
                    </div>
                    <small class="form-text text-muted">Type to search. Filtering happens automatically as you type.</small>
                </div>

                <!-- Separate Filters Panel (With Apply Button) -->
                <div class="border p-3 rounded mb-4 bg-light">
                    <h6 class="mb-3 fw-bold"><i class="mdi mdi-filter-variant me-1"></i> Filter Options</h6>
                    <div class="row g-3 align-items-end">
                        <div class="col-12 col-md-5">
                            <label class="form-label fw-medium">Type</label>
                            <select name="type" class="form-select">
                                <option value="">All Types</option>
                                <option value="whatsapp" @selected(($type ?? '') === 'whatsapp')>WhatsApp</option>
                                <option value="call" @selected(($type ?? '') === 'call')>Call</option>
                                <option value="email" @selected(($type ?? '') === 'email')>Email</option>
                                <option value="chat" @selected(($type ?? '') === 'chat')>Chat</option>
                                <option value="website" @selected(($type ?? '') === 'website')>Website</option>
                            </select>
                        </div>
                        <div class="col-12 col-md-5">
                            <label class="form-label fw-medium">Status</label>
                            <select name="status" class="form-select">
                                <option value="">All Statuses</option>
                                <option value="active" @selected(($status ?? '') === 'active')>Active</option>
                                <option value="inactive" @selected(($status ?? '') === 'inactive')>Inactive</option>
                            </select>
                        </div>
                        <div class="col-12 col-md-2 d-grid gap-2">
                            <button type="submit" class="btn btn-dark">
                                <i class="mdi mdi-check me-1"></i> Apply Filters
                            </button>
                        </div>
                    </div>
                    @if(filled($type ?? '') || filled($status ?? ''))
                        <div class="mt-2 text-end">
                            <a href="{{ route('admin.support-options.index') }}" class="btn btn-sm btn-outline-secondary">
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
                                    return route('admin.support-options.index', array_merge(request()->query(), ['sort' => $col, 'direction' => $newDir]));
                                };
                                $getSortIcon = function($col) use ($sort, $direction) {
                                    if ($sort !== $col) return 'mdi-minus text-muted';
                                    return $direction === 'asc' ? 'mdi-arrow-up text-primary' : 'mdi-arrow-down text-primary';
                                };
                            @endphp
                            <th><a href="{{ $getSortUrl('title') }}" class="text-dark fw-bold">Option <i class="mdi {{ $getSortIcon('title') }} ml-1"></i></a></th>
                            <th><a href="{{ $getSortUrl('type') }}" class="text-dark fw-bold">Type <i class="mdi {{ $getSortIcon('type') }} ml-1"></i></a></th>
                            <th>Value</th>
                            <th><a href="{{ $getSortUrl('sort_order') }}" class="text-dark fw-bold">Order <i class="mdi {{ $getSortIcon('sort_order') }} ml-1"></i></a></th>
                            <th><a href="{{ $getSortUrl('is_active') }}" class="text-dark fw-bold">Status <i class="mdi {{ $getSortIcon('is_active') }} ml-1"></i></a></th>
                            <th><a href="{{ $getSortUrl('created_at') }}" class="text-dark fw-bold">Created <i class="mdi {{ $getSortIcon('created_at') }} ml-1"></i></a></th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="table-border-bottom-0">
                        @forelse($supportOptions as $option)
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        @if($option->image)
                                            <img src="{{ str_starts_with($option->image, 'http') ? $option->image : asset('storage/' . $option->image) }}" alt="{{ $option->title }}" class="support-option-icon">
                                        @else
                                            <div class="support-option-avatar"><i class="mdi mdi-lifebuoy"></i></div>
                                        @endif
                                        <div>
                                            <div class="cell-primary fw-semibold">{{ $option->title }}</div>
                                            <div class="cell-muted">Option #{{ $option->id }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td>{{ \Illuminate\Support\Str::headline($option->type) }}</td>
                                <td class="cell-muted">{{ $option->value }}</td>
                                <td>{{ $option->sort_order }}</td>
                                <td>
                                    <span class="badge {{ $option->is_active ? 'bg-label-success' : 'bg-label-secondary' }}">
                                        {{ $option->is_active ? 'Active' : 'Inactive' }}
                                    </span>
                                </td>
                                <td class="cell-muted">{{ $option->created_at?->format('Y-m-d') }}</td>
                                <td class="text-end">
                                    <div class="d-inline-flex align-items-center gap-1">
                                        @include('admin.components.action-buttons', [
                                            'type' => 'edit',
                                            'href' => route('admin.support-options.edit', $option),
                                            'title' => 'Edit Support Option',
                                        ])
                                        @include('admin.components.action-buttons', [
                                            'type' => 'delete',
                                            'formAction' => route('admin.support-options.destroy', $option),
                                            'confirm' => "Delete support option \"{$option->title}\"?",
                                        ])
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-4 text-muted">No support options found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            @if($supportOptions->total() > 0)
                <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mt-4">
                    <div class="d-flex align-items-center gap-2">
                        <span class="text-muted small">Showing {{ $supportOptions->firstItem() ?? 0 }} to {{ $supportOptions->lastItem() ?? 0 }} of {{ $supportOptions->total() }} records</span>
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
                        {{ $supportOptions->links() }}
                    </div>
                </div>
            @endif
        </div>
    </div>

</div>
@endsection

@push('my-styles')
<style>
    .support-stats-grid {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 1rem;
    }

    .support-stat-card {
        background: linear-gradient(180deg, #ffffff 0%, #f9fbfc 100%);
        border: 1px solid #e6ebf2;
        border-radius: 18px;
        padding: 1rem 1.1rem;
        display: flex;
        align-items: center;
        gap: 0.9rem;
        box-shadow: 0 10px 24px rgba(15, 23, 42, 0.05);
    }

    .support-stat-card__icon {
        width: 48px;
        height: 48px;
        border-radius: 14px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 24px;
        flex-shrink: 0;
    }

    .support-stat-card__icon--primary { background: #eef2ff; color: #4f46e5; }
    .support-stat-card__icon--success { background: #ecfdf5; color: #059669; }
    .support-stat-card__icon--secondary { background: #f1f5f9; color: #64748b; }

    .support-stat-card__label {
        font-size: 0.75rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.06em;
        color: #64748b;
        margin-bottom: 0.2rem;
    }

    .support-stat-card__value {
        font-size: 1.5rem;
        font-weight: 800;
        color: #0f172a;
        line-height: 1.1;
    }

    .support-option-icon,
    .support-option-avatar {
        width: 42px;
        height: 42px;
        border-radius: 14px;
        flex-shrink: 0;
    }

    .support-option-icon {
        object-fit: cover;
    }

    .support-option-avatar {
        background: #eef2ff;
        color: #4f46e5;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    @media (max-width: 991px) {
        .support-stats-grid {
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
