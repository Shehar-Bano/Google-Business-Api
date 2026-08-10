@extends('layouts/contentNavbarLayout')

@section('title', 'Order Management')

@section('content')
<div class="admin-page order-management-page">

    <div class="admin-page-header">
        <div class="admin-page-header__left">
            <h4 class="admin-page-header__title">Order Management</h4>
            <p class="admin-page-header__subtitle">View and manage all orders in the system.</p>
        </div>
    </div>

    {{-- Stats Grid --}}
    <div class="om-stats-grid mb-4">
        <div class="om-stat-card">
            <div class="om-stat-card__icon om-stat-card__icon--primary"><i class="mdi mdi-clipboard-list-outline"></i></div>
            <div>
                <div class="om-stat-card__label">Total Orders</div>
                <div class="om-stat-card__value">{{ number_format($stats['total']) }}</div>
            </div>
        </div>
        @foreach(\App\Models\Order::STATUSES as $s)
            <div class="om-stat-card">
                <div class="om-stat-card__icon om-stat-card__icon--{{ $s }}">
                    <i class="mdi {{ \App\Models\Order::STATUS_ICONS[$s] ?? 'mdi-clipboard-outline' }}"></i>
                </div>
                <div>
                    <div class="om-stat-card__label">{{ \App\Models\Order::STATUS_LABELS[$s] }}</div>
                    <div class="om-stat-card__value">{{ number_format($stats[$s] ?? 0) }}</div>
                </div>
            </div>
        @endforeach
    </div>

    @component('admin.components.datatable', [
        'title'     => 'Orders',
        'subtitle'  => 'Search by order number or user name.',
        'paginator' => $orders,
        'createUrl' => null,
        'search'    => $search,
        'perPage'   => $perPage,
        'sort'      => $sort,
        'direction' => $direction,
        'filters'   => [
            [
                'name'    => 'status',
                'label'   => 'Status',
                'value'   => $status,
                'options' => array_merge(['' => 'All Statuses'], \App\Models\Order::STATUS_LABELS),
            ],
            [
                'name'  => 'date_from',
                'label' => 'Date From',
                'type'  => 'date',
                'value' => $dateFrom,
            ],
            [
                'name'  => 'date_to',
                'label' => 'Date To',
                'type'  => 'date',
                'value' => $dateTo,
            ],
        ],
        'columns'   => [
            ['label' => 'Order #', 'field' => 'order_id', 'sortable' => true],
            ['label' => 'User', 'sortable' => false],
            ['label' => 'Status', 'field' => 'status', 'sortable' => true],
            ['label' => 'Total Amount', 'sortable' => false],
            ['label' => 'Created At', 'field' => 'created_at', 'sortable' => true],
            ['label' => 'Actions', 'actions' => true],
        ],
    ])
        @forelse($orders as $order)
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
                <td>
                    <span class="fw-semibold">{{ $order->order_id }}</span>
                </td>
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
                    <div class="d-inline-flex align-items-center gap-1">
                        <form action="{{ route('admin.order-management.update-status', $order) }}" method="POST" class="d-inline om-update-form">
                            @csrf
                            @method('PATCH')
                            <div class="d-flex align-items-center gap-1 flex-wrap">
                                @if($order->status === \App\Models\Order::STATUS_IN_REVIEW)
                                    <input type="number"
                                           name="price"
                                           class="form-control form-control-sm om-price-input"
                                           placeholder="Amount"
                                           min="0"
                                           value="{{ $order->detail?->price ?? '' }}"
                                           title="Set Total Amount">
                                @endif
                                <select name="status" class="form-select form-select-sm om-status-select" title="Update Status"
                                    onchange="{{ $order->status === \App\Models\Order::STATUS_IN_REVIEW ? '' : 'this.form.submit()' }}">
                                    @foreach(\App\Models\Order::STATUS_LABELS as $val => $label)
                                        <option value="{{ $val }}" @selected($order->status === $val)>{{ $label }}</option>
                                    @endforeach
                                </select>
                                @if($order->status === \App\Models\Order::STATUS_IN_REVIEW)
                                    <button type="submit" class="btn btn-sm btn-dark px-2" title="Save">
                                        <i class="mdi mdi-check"></i>
                                    </button>
                                @endif
                            </div>
                        </form>
                        @include('admin.components.action-buttons', [
                            'type'  => 'view',
                            'href'  => route('admin.order-management.show', $order),
                            'title' => 'View Order',
                        ])
                    </div>
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="6" class="admin-empty-state">No orders found.</td>
            </tr>
        @endforelse
    @endcomponent

</div>
@endsection

@push('my-styles')
<style>
    .om-stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(180px, 1fr));
        gap: 1rem;
    }

    .om-stat-card {
        background: linear-gradient(180deg, #ffffff 0%, #f9fbfc 100%);
        border: 1px solid #e6ebf2;
        border-radius: 18px;
        padding: 1rem 1.1rem;
        display: flex;
        align-items: center;
        gap: 0.9rem;
        box-shadow: 0 10px 24px rgba(15, 23, 42, 0.05);
    }

    .om-stat-card__icon {
        width: 48px;
        height: 48px;
        border-radius: 14px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 24px;
        flex-shrink: 0;
    }

    .om-stat-card__icon--primary    { background: #eef2ff; color: #4f46e5; }
    .om-stat-card__icon--in_review  { background: #fffbeb; color: #d97706; }
    .om-stat-card__icon--active     { background: #ecfdf5; color: #059669; }
    .om-stat-card__icon--cancelled  { background: #fef2f2; color: #dc2626; }
    .om-stat-card__icon--completed  { background: #eff6ff; color: #2563eb; }

    .om-stat-card__label {
        font-size: 0.72rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.06em;
        color: #64748b;
        margin-bottom: 0.2rem;
    }

    .om-stat-card__value {
        font-size: 1.5rem;
        font-weight: 800;
        color: #0f172a;
        line-height: 1.1;
    }

    .om-status-select {
        min-width: 120px;
        font-size: 0.8rem;
    }

    .om-price-input {
        width: 90px;
        font-size: 0.8rem;
    }

    @media (max-width: 767px) {
        .om-stats-grid {
            grid-template-columns: 1fr 1fr;
        }
    }
</style>
@endpush
