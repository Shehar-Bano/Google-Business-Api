@extends('layouts/contentNavbarLayout')

@section('title', 'Dashboard')

@section('content')

{{-- ===== Row 1: User Statistics ===== --}}
<div class="mb-2">
    <h5 class="dash-section-title"><i class="mdi mdi-account-group-outline me-2 text-primary"></i>User Statistics</h5>
</div>
<div class="dash-stats-grid mb-4">
    <div class="dash-stat-card dash-stat-card--primary">
        <div class="dash-stat-card__icon"><i class="mdi mdi-account-multiple"></i></div>
        <div>
            <div class="dash-stat-card__label">Total Users</div>
            <div class="dash-stat-card__value">{{ number_format($userStats['total']) }}</div>
        </div>
    </div>
    @foreach(\App\Models\User::ADMIN_STATUSES as $s)
        <div class="dash-stat-card dash-stat-card--user-{{ $s }}">
            <div class="dash-stat-card__icon"><i class="mdi {{ \App\Models\User::ADMIN_STATUS_ICONS[$s] }}"></i></div>
            <div>
                <div class="dash-stat-card__label">{{ \App\Models\User::ADMIN_STATUS_LABELS[$s] }}</div>
                <div class="dash-stat-card__value">{{ number_format($userStats[$s] ?? 0) }}</div>
            </div>
        </div>
    @endforeach
</div>

{{-- ===== Row 2: Order Statistics ===== --}}
<div class="mb-2">
    <h5 class="dash-section-title"><i class="mdi mdi-clipboard-list-outline me-2 text-primary"></i>Order Statistics</h5>
</div>
<div class="dash-stats-grid mb-4">
    <div class="dash-stat-card dash-stat-card--primary">
        <div class="dash-stat-card__icon"><i class="mdi mdi-clipboard-list-outline"></i></div>
        <div>
            <div class="dash-stat-card__label">Total Orders</div>
            <div class="dash-stat-card__value">{{ number_format($orderStats['total']) }}</div>
        </div>
    </div>
    @foreach(\App\Models\Order::STATUSES as $s)
        <div class="dash-stat-card dash-stat-card--order-{{ $s }}">
            <div class="dash-stat-card__icon"><i class="mdi {{ \App\Models\Order::STATUS_ICONS[$s] }}"></i></div>
            <div>
                <div class="dash-stat-card__label">{{ \App\Models\Order::STATUS_LABELS[$s] }}</div>
                <div class="dash-stat-card__value">{{ number_format($orderStats[$s] ?? 0) }}</div>
            </div>
        </div>
    @endforeach
</div>

{{-- ===== Row 3: Latest Orders Table ===== --}}
<div class="mb-2">
    <h5 class="dash-section-title"><i class="mdi mdi-history me-2 text-primary"></i>Latest Orders</h5>
</div>
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <div>
            <h5 class="mb-0">Latest 10 Orders</h5>
        </div>
        <a href="{{ route('admin.order-management.index') }}" class="btn btn-sm admin-add-btn">
            <i class="mdi mdi-arrow-right me-1"></i> View All
        </a>
    </div>
    <div class="table-responsive">
        <table class="table admin-datatable mb-0">
            <thead>
                <tr>
                    <th>Order Number</th>
                    <th>User Name</th>
                    <th>Status</th>
                    <th>Total Amount</th>
                    <th>Created Date</th>
                    <th class="text-end admin-actions-col">Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse($latestOrders as $order)
                    @php
                        $orderBadge = match($order->status) {
                            'active'    => 'bg-label-success',
                            'in_review' => 'bg-label-warning',
                            'cancelled' => 'bg-label-danger',
                            'completed' => 'bg-label-primary',
                            default     => 'bg-label-secondary',
                        };
                    @endphp
                    <tr>
                        <td class="fw-semibold">{{ $order->order_id }}</td>
                        <td>
                            @if($order->user)
                                <div class="cell-primary">{{ $order->user->name }}</div>
                                <div class="cell-muted">{{ $order->user->email }}</div>
                            @else
                                <span class="text-muted">—</span>
                            @endif
                        </td>
                        <td>
                            <span class="badge {{ $orderBadge }} text-capitalize">
                                {{ \App\Models\Order::STATUS_LABELS[$order->status] ?? $order->status }}
                            </span>
                        </td>
                        <td class="fw-semibold">{{ number_format($order->detail?->price ?? 0) }}</td>
                        <td class="cell-muted">{{ $order->created_at?->format('Y-m-d') }}</td>
                        <td class="text-end">
                            <a href="{{ route('admin.order-management.show', $order) }}"
                               class="action-icon-btn action-icon-view"
                               data-bs-toggle="tooltip"
                               title="View Order">
                                <i class="mdi mdi-eye-outline"></i>
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center text-muted py-4">No orders yet.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@endsection

@push('my-styles')
<style>
    .dash-section-title {
        font-size: 1rem;
        font-weight: 700;
        color: #0f172a;
        letter-spacing: -0.01em;
    }

    .dash-stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(175px, 1fr));
        gap: 1rem;
    }

    .dash-stat-card {
        background: linear-gradient(180deg, #ffffff 0%, #f9fbfc 100%);
        border: 1px solid #e6ebf2;
        border-radius: 18px;
        padding: 1rem 1.1rem;
        display: flex;
        align-items: center;
        gap: 0.9rem;
        box-shadow: 0 10px 24px rgba(15, 23, 42, 0.05);
    }

    .dash-stat-card__icon {
        width: 48px;
        height: 48px;
        border-radius: 14px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 24px;
        flex-shrink: 0;
    }

    .dash-stat-card__label {
        font-size: 0.7rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.06em;
        color: #64748b;
        margin-bottom: 0.2rem;
        white-space: nowrap;
    }

    .dash-stat-card__value {
        font-size: 1.5rem;
        font-weight: 800;
        color: #0f172a;
        line-height: 1.1;
    }

    /* User stat colours */
    .dash-stat-card--primary .dash-stat-card__icon           { background: #eef2ff; color: #4f46e5; }
    .dash-stat-card--user-active .dash-stat-card__icon       { background: #ecfdf5; color: #059669; }
    .dash-stat-card--user-pending .dash-stat-card__icon      { background: #fffbeb; color: #d97706; }
    .dash-stat-card--user-otp_pending .dash-stat-card__icon  { background: #f1f5f9; color: #64748b; }
    .dash-stat-card--user-profile_incomplete .dash-stat-card__icon { background: #f1f5f9; color: #64748b; }
    .dash-stat-card--user-rejected .dash-stat-card__icon     { background: #fef2f2; color: #dc2626; }
    .dash-stat-card--user-suspended .dash-stat-card__icon    { background: #1e293b; color: #f1f5f9; }

    /* Order stat colours */
    .dash-stat-card--order-in_review .dash-stat-card__icon   { background: #fffbeb; color: #d97706; }
    .dash-stat-card--order-active .dash-stat-card__icon      { background: #ecfdf5; color: #059669; }
    .dash-stat-card--order-cancelled .dash-stat-card__icon   { background: #fef2f2; color: #dc2626; }
    .dash-stat-card--order-completed .dash-stat-card__icon   { background: #eff6ff; color: #2563eb; }

    @media (max-width: 767px) {
        .dash-stats-grid {
            grid-template-columns: 1fr 1fr;
        }
    }
</style>
@endpush
