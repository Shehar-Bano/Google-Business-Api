@extends('layouts/contentNavbarLayout')

@section('title', 'Business Details')

@section('content')
    <div class="admin-page">

        <div class="admin-page-header d-flex justify-content-between align-items-center mb-4">
            <div class="admin-page-header__left">
                <h4 class="admin-page-header__title">Business Details</h4>
                <p class="admin-page-header__subtitle">Viewing profile details for {{ $business->name }}</p>
            </div>
            <div>
                <a href="{{ route('admin.business-management.index') }}" class="btn btn-outline-secondary btn-sm">
                    <i class="mdi mdi-arrow-left me-1"></i> Back to Businesses
                </a>
            </div>
        </div>

        <div class="row">
            {{-- Profile Card --}}
            <div class="col-lg-4">
                <div class="card mb-4">
                    <div class="card-body text-center pt-4">
                        @if ($business->brand_logo)
                            <img src="{{ asset($business->brand_logo) }}" alt="Logo" class="rounded mb-3"
                                style="width: 90px; height: 90px; object-fit: cover; border: 1px solid #eef2ff;">
                        @else
                            <div class="um-detail-avatar-placeholder mx-auto mb-3 bg-label-primary text-primary">
                                {{ strtoupper(substr($business->name, 0, 1)) }}
                            </div>
                        @endif

                        <h5 class="mb-1 fw-bold">
                            {{ $business->name }}
                            @if ($business->isVerified)
                                <i class="mdi mdi-decagram text-primary" title="Verified Business"></i>
                            @endif
                        </h5>
                        <p class="text-muted mb-3"><i
                                class="mdi mdi-map-marker me-1 text-danger"></i>{{ $business->location }}</p>

                        <div class="text-start mt-4">
                            <div class="border-bottom py-2 d-flex justify-content-between align-items-center">
                                <span class="text-muted small">Status</span>
                                @if (($business->status ?? 'approved') === 'suspended')
                                    <span class="badge bg-label-danger">Suspended</span>
                                @else
                                    <span class="badge bg-label-success">Approved</span>
                                @endif
                            </div>

                            <div class="border-bottom py-2 d-flex justify-content-between align-items-center">
                                <span class="text-muted small">Verification</span>
                                @if ($business->isVerified)
                                    <span class="badge bg-label-primary">Verified</span>
                                @else
                                    <span class="badge bg-label-secondary">Not Verified</span>
                                @endif
                            </div>

                            <div class="border-bottom py-2 d-flex justify-content-between align-items-center mt-2">
                                <span class="text-muted small">Rating & Reviews</span>
                                <strong class="cell-primary">
                                    <i class="mdi mdi-star text-warning"></i>
                                    {{ number_format($business->rating ?? 0.0, 1) }} ({{ $business->reviews ?? 0 }} reviews)
                                </strong>
                            </div>

                            <div class="border-bottom py-2 d-flex justify-content-between align-items-center mt-2">
                                <span class="text-muted small">Category</span>
                                <strong class="cell-primary text-capitalize">{{ $business->category ?? '—' }}</strong>
                            </div>

                            <div class="border-bottom py-2 d-flex justify-content-between align-items-center mt-2">
                                <span class="text-muted small">Phone Number</span>
                                <strong class="cell-primary">{{ $business->phone_number ?? '—' }}</strong>
                            </div>

                            <div class="border-bottom py-2 mt-2">
                                <span class="text-muted small d-block">Address</span>
                                <strong class="cell-primary">{{ $business->address ?? '—' }}</strong>
                            </div>

                            <div class="py-2 mt-2">
                                <span class="text-muted small d-block">Registered Date</span>
                                <strong
                                    class="cell-primary">{{ $business->created_at?->format('Y-m-d H:i') ?? '—' }}</strong>
                            </div>
                            <div class="mt-4 border-top pt-3 text-center d-grid gap-2">
                                <a href="{{ route('admin.business-management.edit', $business) }}"
                                    class="btn btn-primary btn-sm w-100 d-flex align-items-center justify-content-center gap-1">
                                    <i class="mdi mdi-pencil me-1"></i> Edit Status
                                </a>
                                <a href="{{ route('admin.business-management.keyword-ideas', $business) }}"
                                    class="btn btn-outline-primary btn-sm w-100 d-flex align-items-center justify-content-center gap-1">
                                    <i class="mdi mdi-google me-1"></i> View Keyword Ideas
                                </a>
                            </div>
                        </div>
                    </div> {{-- Closes Card --}}
                </div> {{-- Closes col-lg-4 --}}


            </div>
            {{-- Top Selling & Scores Panels --}}
            <div class="col-lg-8">
                {{-- Top Selling Items --}}
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0 fw-bold">Top Selling Items</h5>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Media</th>
                                    <th>Item Name</th>
                                    <th>Price</th>
                                    <th>Description</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($business->topSellingItems as $item)
                                    <tr>
                                        <td>
                                            @if ($item->media)
                                                <img src="{{ asset($item->media) }}" alt="Item Media" class="rounded"
                                                    style="width: 45px; height: 45px; object-fit: cover; border: 1px solid #eef2ff;">
                                            @else
                                                <span class="text-muted text-xs">—</span>
                                            @endif
                                        </td>
                                        <td><strong class="cell-primary">{{ $item->item_name }}</strong></td>
                                        <td>
                                            @if ($item->price)
                                                <span class="fw-semibold text-success">Rs.
                                                    {{ number_format($item->price, 2) }}</span>
                                            @else
                                                <span class="text-muted">—</span>
                                            @endif
                                        </td>
                                        <td style="max-width: 300px; white-space: normal;" class="cell-muted">
                                            {{ $item->description ?? 'No description provided.' }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="text-center text-muted py-4">No top selling items
                                            registered.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    @endsection

    @push('my-styles')
        <style>
            .um-detail-avatar-placeholder {
                width: 90px;
                height: 90px;
                border-radius: 20px;
                background: #eef2ff;
                color: #4f46e5;
                display: flex;
                align-items: center;
                justify-content: center;
                font-size: 2.2rem;
                font-weight: 800;
            }

            .font-monospace {
                font-family: SFMono-Regular, Menlo, Monaco, Consolas, monospace;
            }
        </style>
    @endpush
