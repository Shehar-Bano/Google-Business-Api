@extends('layouts/contentNavbarLayout')

@section('title', 'Order Details')

@section('content')
    <div class="admin-page">

        <div class="admin-page-header mb-4">
            <div class="admin-page-header__left">
                <h4 class="admin-page-header__title">Order Details</h4>
                <p class="admin-page-header__subtitle">Order #{{ $order->order_id }}</p>
            </div>
            <div>
                <a href="{{ route('admin.order-management.index') }}" class="btn btn-outline-secondary btn-sm">
                    <i class="mdi mdi-arrow-left me-1"></i> Back to Orders
                </a>
            </div>
        </div>

        <div class="row g-4">

            {{-- Order Summary --}}
            <div class="col-lg-4">
                <div class="card mb-4">
                    <div class="card-header">
                        <h6 class="mb-0">Order Summary</h6>
                    </div>
                    <div class="card-body">
                        @php
                            $orderBadge = match ($order->status) {
                                'active' => 'bg-label-success',
                                'in_review' => 'bg-label-warning',
                                'cancelled' => 'bg-label-danger',
                                'completed' => 'bg-label-primary',
                                default => 'bg-label-secondary',
                            };
                        @endphp

                        <div class="d-flex justify-content-between border-bottom py-2">
                            <span class="text-muted small">Order Number</span>
                            <strong>{{ $order->order_id }}</strong>
                        </div>
                        <div class="d-flex justify-content-between border-bottom py-2">
                            <span class="text-muted small">Status</span>
                            <span
                                class="badge {{ $orderBadge }}">{{ \App\Models\Order::STATUS_LABELS[$order->status] ?? $order->status }}</span>
                        </div>
                        <div class="d-flex justify-content-between border-bottom py-2">
                            <span class="text-muted small">Total Amount</span>
                            <strong>{{ number_format($order->detail?->price ?? 0) }}</strong>
                        </div>
                        <div class="d-flex justify-content-between border-bottom py-2">
                            <span class="text-muted small">Date</span>
                            <strong>{{ $order->detail?->date?->format('Y-m-d') ?? '—' }}</strong>
                        </div>
                        <div class="d-flex justify-content-between py-2">
                            <span class="text-muted small">Created</span>
                            <strong>{{ $order->created_at?->format('Y-m-d H:i') ?? '—' }}</strong>
                        </div>

                        {{-- Update Status --}}
                        <div class="mt-4">
                            <form action="{{ route('admin.order-management.update-status', $order) }}" method="POST">
                                @csrf
                                @method('PATCH')
                                <label class="form-label fw-semibold small">Update Status</label>
                                @if ($order->status === \App\Models\Order::STATUS_IN_REVIEW)
                                    <div class="mb-2">
                                        <label class="form-label fw-semibold small">Total Amount</label>
                                        <div class="input-group input-group-sm">
                                            {{-- <span class="input-group-text">$</span> --}}
                                            <input type="number" name="price" class="form-control"
                                                placeholder="Enter amount" min="0"
                                                value="{{ $order->detail?->price ?? '' }}">
                                        </div>
                                    </div>
                                @endif
                                <div class="input-group input-group-sm">
                                    <select name="status" class="form-select">
                                        @foreach (\App\Models\Order::STATUS_LABELS as $val => $label)
                                            <option value="{{ $val }}" @selected($order->status === $val)>
                                                {{ $label }}</option>
                                        @endforeach
                                    </select>
                                    <button class="btn btn-dark" type="submit">Update</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                {{-- User Details --}}
                @if ($order->user)
                    <div class="card">
                        <div class="card-header">
                            <h6 class="mb-0">User Details</h6>
                        </div>
                        <div class="card-body">
                            <div class="d-flex align-items-center gap-3 mb-3">
                                <div class="om-user-avatar">{{ strtoupper(substr($order->user->name, 0, 1)) }}</div>
                                <div>
                                    <div class="fw-semibold">{{ $order->user->name }}</div>
                                    <div class="text-muted small">{{ $order->user->email }}</div>
                                </div>
                            </div>
                            <div class="d-flex justify-content-between border-bottom py-2">
                                <span class="text-muted small">User Type</span>
                                <strong class="text-capitalize">{{ $order->user->role ?? '—' }}</strong>
                            </div>
                            <div class="d-flex justify-content-between border-bottom py-2">
                                <span class="text-muted small">Phone</span>
                                <strong>{{ $order->user->phone ?? '—' }}</strong>
                            </div>
                            <div class="d-flex justify-content-between py-2">
                                <span class="text-muted small">Status</span>
                                <span class="badge bg-label-secondary text-capitalize">
                                    {{ \App\Models\User::STATUS_LABELS[$order->user->status] ?? $order->user->status }}
                                </span>
                            </div>
                            <div class="mt-3">
                                <a href="{{ route('admin.user-management.show', $order->user) }}"
                                    class="btn btn-sm btn-outline-primary w-100">
                                    View User Profile
                                </a>
                            </div>
                        </div>
                    </div>
                @endif
            </div>

            {{-- Order Details --}}
            <div class="col-lg-8">
                @if ($order->detail)
                    <div class="card mb-4">
                        <div class="card-header">
                            <h6 class="mb-0">Order Information</h6>
                        </div>
                        <div class="card-body">
                            <div class="row g-3">
                                <div class="col-sm-6">
                                    <label class="form-label text-muted small">Phone</label>
                                    <p class="mb-0 fw-semibold">{{ $order->detail->phone ?? '—' }}</p>
                                </div>
                                <div class="col-sm-6">
                                    <label class="form-label text-muted small">Time</label>
                                    <p class="mb-0 fw-semibold">{{ $order->detail->time ?? '—' }}</p>
                                </div>
                                <div class="col-12">
                                    <label class="form-label text-muted small">Address</label>
                                    <p class="mb-0 fw-semibold">{{ $order->detail->address ?? '—' }}</p>
                                </div>
                                <div class="col-12">
                                    <label class="form-label text-muted small">Description</label>
                                    <p class="mb-0">{{ $order->detail->description ?? '—' }}</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Images --}}
                    @if (!empty($order->detail->images))
                        <div class="card">
                            <div class="card-header">
                                <h6 class="mb-0">Order Images</h6>
                            </div>
                            <div class="card-body">
                                <div class="om-images-grid">
                                    @foreach ($order->detail->images as $image)
                                        <a href="{{ asset('storage/' . $image) }}" target="_blank">
                                            <img src="{{ asset('storage/' . $image) }}" alt="Order image"
                                                class="om-order-image">
                                        </a>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    @endif
                @else
                    <div class="card">
                        <div class="card-body text-center text-muted py-5">
                            <i class="mdi mdi-clipboard-off-outline fs-1 mb-3 d-block"></i>
                            No order details available.
                        </div>
                    </div>
                @endif
            </div>

        </div>
    </div>
@endsection

@push('my-styles')
    <style>
        .om-user-avatar {
            width: 42px;
            height: 42px;
            border-radius: 12px;
            background: #eef2ff;
            color: #4f46e5;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            font-size: 1rem;
            flex-shrink: 0;
        }

        .om-images-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(140px, 1fr));
            gap: 0.75rem;
        }

        .om-order-image {
            width: 100%;
            aspect-ratio: 1;
            object-fit: cover;
            border-radius: 12px;
            border: 1px solid #e6ebf2;
            transition: opacity 0.2s;
        }

        .om-order-image:hover {
            opacity: 0.85;
        }
    </style>
@endpush
