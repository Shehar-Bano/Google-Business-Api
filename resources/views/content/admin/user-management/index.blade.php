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

        @component('admin.components.datatable', [
            'title' => 'Users',
            'subtitle' => 'Search by name or email.',
            'paginator' => $users,
            'createUrl' => null,
            'search' => $search,
            'perPage' => $perPage,
            'sort' => $sort,
            'direction' => $direction,
            'filters' => [
                [
                    'name' => 'status',
                    'label' => 'Status',
                    'value' => $status,
                    'options' => array_merge(['' => 'All Statuses'], \App\Models\User::ADMIN_STATUS_LABELS),
                ],
                [
                    'name' => 'date_from',
                    'label' => 'Date From',
                    'type' => 'date',
                    'value' => $dateFrom,
                ],
                [
                    'name' => 'date_to',
                    'label' => 'Date To',
                    'type' => 'date',
                    'value' => $dateTo,
                ],
            ],
            'columns' => [
                ['label' => '#', 'field' => 'id', 'sortable' => false],
                ['label' => 'Phone Number', 'field' => 'phone_number', 'sortable' => true],
                ['label' => 'Status', 'field' => 'status', 'sortable' => true],
                ['label' => 'Created At', 'field' => 'created_at', 'sortable' => true],
                ['label' => 'Actions', 'actions' => true],
            ],
        ])
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
                            <div class="um-user-avatar bg-label-primary"
                                style="width: 32px; height: 32px; display: flex; align-items: center; justify-content: center; border-radius: 8px;">
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
                            <form action="{{ route('admin.user-management.update-status', $user) }}" method="POST"
                                class="d-inline">
                                @csrf
                                @method('PATCH')
                                <select name="status" class="form-select form-select-sm um-status-select"
                                    onchange="this.form.submit()" title="Update Status">
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
                    <td colspan="6" class="admin-empty-state">No users found.</td>
                </tr>
            @endforelse
        @endcomponent

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
