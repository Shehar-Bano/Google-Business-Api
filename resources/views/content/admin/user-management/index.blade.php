@extends('layouts/contentNavbarLayout')

@section('title', 'User Management')

@section('content')
    <div class="admin-page user-management-page">

        <div class="admin-page-header">
            <div class="admin-page-header__left">
                <h4 class="admin-page-header__title">User Management</h4>
                <p class="admin-page-header__subtitle">View and manage all registered users.</p>
            </div>
        </div>

        {{-- Stats Grid --}}
        <div class="um-stats-grid mb-4">
            <div class="um-stat-card">
                <div class="um-stat-card__icon um-stat-card__icon--primary"><i class="mdi mdi-account-multiple"></i></div>
                <div>
                    <div class="um-stat-card__label">Total Users</div>
                    <div class="um-stat-card__value">{{ number_format($stats['total']) }}</div>
                </div>
            </div>
            @foreach (\App\Models\User::ADMIN_STATUSES as $s)
                <div class="um-stat-card">
                    <div class="um-stat-card__icon um-stat-card__icon--{{ $s }}">
                        <i class="mdi {{ \App\Models\User::ADMIN_STATUS_ICONS[$s] }}"></i>
                    </div>
                    <div>
                        <div class="um-stat-card__label">{{ \App\Models\User::ADMIN_STATUS_LABELS[$s] }}</div>
                        <div class="um-stat-card__value">{{ number_format($stats[$s] ?? 0) }}</div>
                    </div>
                </div>
            @endforeach
        </div>

    <div class="card mt-4">
        <div class="card-header d-flex justify-content-between align-items-center">
            <div>
                <h5 class="mb-0 fw-bold">Users</h5>
                <small class="text-muted">Manage registered users, their roles and registration states.</small>
            </div>
            <div>
                <span class="badge bg-label-primary"><strong>{{ number_format($users->total()) }}</strong> records</span>
            </div>
        </div>
        <div class="card-body">
            <!-- Filter & Search Form -->
            <form method="GET" id="filter-form" action="{{ route('admin.user-management.index') }}">
                <input type="hidden" name="sort" value="{{ $sort }}">
                <input type="hidden" name="direction" value="{{ $direction }}">
                <input type="hidden" name="per_page" value="{{ $perPage }}">

                <!-- Global Search Box (No Button, Auto Filters on Keystroke) -->
                <div class="mb-4">
                    <label class="form-label fw-semibold">Global Search</label>
                    <div class="input-group input-group-merge border-primary">
                        <span class="input-group-text"><i class="mdi mdi-magnify text-primary"></i></span>
                        <input type="text" name="search" id="global-search-input" value="{{ $search }}" class="form-control" placeholder="Type anything to search (Name, Email, or Phone)..." autocomplete="off">
                    </div>
                    <small class="form-text text-muted">Type to search. Filtering happens automatically as you type.</small>
                </div>

                <!-- Separate Filters Panel (With Apply Button) -->
                <div class="border p-3 rounded mb-4 bg-light">
                    <h6 class="mb-3 fw-bold"><i class="mdi mdi-filter-variant me-1"></i> Filter Options</h6>
                    <div class="row g-3 align-items-end">
                        <div class="col-12 col-md-3">
                            <label class="form-label fw-medium">Phone</label>
                            <input type="text" name="phone" value="{{ $phone ?? '' }}" class="form-control" placeholder="Filter by phone number...">
                        </div>
                        <div class="col-12 col-md-3">
                            <label class="form-label fw-medium">Status</label>
                            <select name="status" class="form-select">
                                <option value="">All Statuses</option>
                                @foreach(\App\Models\User::ADMIN_STATUS_LABELS as $val => $label)
                                    <option value="{{ $val }}" @selected($status === $val)>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-12 col-md-2">
                            <label class="form-label fw-medium">Date From</label>
                            <input type="date" name="date_from" value="{{ $dateFrom }}" class="form-control">
                        </div>
                        <div class="col-12 col-md-2">
                            <label class="form-label fw-medium">Date To</label>
                            <input type="date" name="date_to" value="{{ $dateTo }}" class="form-control">
                        </div>
                        <div class="col-12 col-md-2 d-grid gap-2">
                            <button type="submit" class="btn btn-dark">
                                <i class="mdi mdi-check me-1"></i> Apply Filters
                            </button>
                        </div>
                    </div>
                    @if(filled($phone) || filled($status) || filled($dateFrom) || filled($dateTo))
                        <div class="mt-2 text-end">
                            <a href="{{ route('admin.user-management.index') }}" class="btn btn-sm btn-outline-secondary">
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
                            <!-- Custom Sort Headers -->
                            @php
                                $getSortUrl = function($col) use ($sort, $direction) {
                                    $newDir = ($sort === $col && $direction === 'asc') ? 'desc' : 'asc';
                                    return route('admin.user-management.index', array_merge(request()->query(), ['sort' => $col, 'direction' => $newDir]));
                                };
                                $getSortIcon = function($col) use ($sort, $direction) {
                                    if ($sort !== $col) return 'mdi-minus text-muted';
                                    return $direction === 'asc' ? 'mdi-arrow-up text-primary' : 'mdi-arrow-down text-primary';
                                };
                            @endphp
                            <th><a href="{{ $getSortUrl('id') }}" class="text-dark fw-bold"># <i class="mdi {{ $getSortIcon('id') }} ml-1"></i></a></th>
                            <th><a href="{{ $getSortUrl('phone') }}" class="text-dark fw-bold">Phone <i class="mdi {{ $getSortIcon('phone') }} ml-1"></i></a></th>
                            <th><a href="{{ $getSortUrl('status') }}" class="text-dark fw-bold">Status <i class="mdi {{ $getSortIcon('status') }} ml-1"></i></a></th>
                            <th><a href="{{ $getSortUrl('created_at') }}" class="text-dark fw-bold">Created At <i class="mdi {{ $getSortIcon('created_at') }} ml-1"></i></a></th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="table-border-bottom-0">
                        @forelse($users as $user)
                            @php
                                $statusBadge = match ($user->status) {
                                    'active' => 'bg-label-success',
                                    'otp_pending' => 'bg-label-secondary',
                                    'pending' => 'bg-label-warning',
                                    'suspended' => 'bg-label-dark',
                                    'profile_incomplete' => 'bg-label-secondary',
                                    'rejected' => 'bg-label-danger',
                                    default => 'bg-label-secondary',
                                };
                            @endphp
                            <tr>
                                <td class="cell-muted">{{ $user->id }}</td>
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="um-user-avatar bg-label-primary" style="width: 32px; height: 32px; display: flex; align-items: center; justify-content: center; border-radius: 8px;">
                                            <i class="mdi mdi-phone-outline text-primary"></i>
                                        </div>
                                        <div>
                                            <div class="cell-primary fw-semibold">{{ $user->phone ?: '— No Phone —' }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge {{ $statusBadge }} text-capitalize">
                                        {{ \App\Models\User::STATUS_LABELS[$user->status] ?? $user->status }}
                                    </span>
                                </td>
                                <td class="cell-muted">{{ $user->created_at?->format('Y-m-d') }}</td>
                                <td class="text-end">
                                    <div class="d-inline-flex align-items-center gap-1">
                                        <form action="{{ route('admin.user-management.update-status', $user) }}" method="POST" class="d-inline">
                                            @csrf
                                            @method('PATCH')
                                            <select name="status" class="form-select form-select-sm um-status-select" onchange="this.form.submit()" title="Update Status">
                                                @foreach (\App\Models\User::ADMIN_STATUS_LABELS as $val => $label)
                                                    <option value="{{ $val }}" @selected($user->status === $val)>
                                                        {{ $val === 'otp_pending' && $user->otp_verified ? 'OTP Verified' : $label }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </form>
                                        @include('admin.components.action-buttons', [
                                            'type' => 'view',
                                            'href' => route('admin.user-management.show', $user),
                                            'title' => 'View User',
                                        ])
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center py-4 text-muted">No users found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            @if($users->hasPages())
                <div class="d-flex justify-content-between align-items-center mt-4">
                    <div>
                        <span class="text-muted small">Showing {{ $users->firstItem() ?? 0 }} to {{ $users->lastItem() ?? 0 }} of {{ $users->total() }} records</span>
                    </div>
                    <div>
                        {{ $users->links() }}
                    </div>
                </div>
            @endif
        </div>
    </div>

    </div>
@endsection

@push('my-styles')
    <style>
        .um-stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(180px, 1fr));
            gap: 1rem;
        }

        .um-stat-card {
            background: linear-gradient(180deg, #ffffff 0%, #f9fbfc 100%);
            border: 1px solid #e6ebf2;
            border-radius: 18px;
            padding: 1rem 1.1rem;
            display: flex;
            align-items: center;
            gap: 0.9rem;
            box-shadow: 0 10px 24px rgba(15, 23, 42, 0.05);
        }

        .um-stat-card__icon {
            width: 48px;
            height: 48px;
            border-radius: 14px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            flex-shrink: 0;
        }

        .um-stat-card__icon--primary {
            background: #eef2ff;
            color: #4f46e5;
        }

        .um-stat-card__icon--otp_pending {
            background: #f1f5f9;
            color: #64748b;
        }

        .um-stat-card__icon--suspended {
            background: #1e293b;
            color: #f1f5f9;
        }

        .um-stat-card__label {
            font-size: 0.72rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.06em;
            color: #64748b;
            margin-bottom: 0.2rem;
        }

        .um-stat-card__value {
            font-size: 1.5rem;
            font-weight: 800;
            color: #0f172a;
            line-height: 1.1;
        }

        .um-user-avatar {
            width: 38px;
            height: 38px;
            border-radius: 12px;
            background: #eef2ff;
            color: #4f46e5;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            font-size: 0.9rem;
            flex-shrink: 0;
        }

        .um-status-select {
            min-width: 120px;
            font-size: 0.8rem;
        }

        @media (max-width: 767px) {
            .um-stats-grid {
                grid-template-columns: 1fr 1fr;
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
            searchInput.addEventListener('input', function() {
                clearTimeout(searchTimeout);
                searchTimeout = setTimeout(() => {
                    filterForm.submit();
                }, 400); // 400ms delay
            });

            // Focus and put cursor at the end of input
            const length = searchInput.value.length;
            if (length > 0) {
                searchInput.focus();
                searchInput.setSelectionRange(length, length);
            }
        }
    });
</script>
@endpush
