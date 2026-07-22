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

    <div class="row g-4 justify-content-center">
        {{-- Profile Card --}}
        <div class="col-lg-7">
            <div class="card h-100">
                <div class="card-body text-center pt-4">
                    @if($business->brand_logo)
                        <img src="{{ asset($business->brand_logo) }}" alt="Logo" class="rounded mb-3" style="width: 80px; height: 80px; object-fit: cover; border: 1px solid #eef2ff;">
                    @else
                        <div class="um-detail-avatar-placeholder mx-auto mb-3 bg-label-primary text-primary">
                            {{ strtoupper(substr($business->name, 0, 1)) }}
                        </div>
                    @endif

                    <h5 class="mb-1 fw-bold">
                        {{ $business->name }}
                        @if($business->isVerified)
                            <i class="mdi mdi-decagram text-primary" title="Verified Business"></i>
                        @endif
                    </h5>
                    <p class="text-muted mb-3"><i class="mdi mdi-map-marker me-1 text-danger"></i>{{ $business->location }}</p>

                    <div class="text-start mt-4">
                        <div class="border-bottom py-2">
                            <span class="text-muted small d-block">Verification Status</span>
                            @if($business->isVerified)
                                <span class="badge bg-label-primary">Verified</span>
                            @else
                                <span class="badge bg-label-secondary">Not Verified</span>
                            @endif
                        </div>

                        <div class="border-bottom py-2 mt-2">
                            <span class="text-muted small d-block">Rating & Reviews</span>
                            <strong class="cell-primary">
                                <i class="mdi mdi-star text-warning"></i> {{ number_format($business->rating ?? 0.0, 1) }} ({{ $business->reviews ?? 0 }} reviews)
                            </strong>
                        </div>

                        <div class="border-bottom py-2 mt-2">
                            <span class="text-muted small d-block">Category</span>
                            <strong class="cell-primary text-capitalize">{{ $business->category ?? '—' }}</strong>
                        </div>

                        <div class="border-bottom py-2 mt-2">
                            <span class="text-muted small d-block">Phone Number</span>
                            <strong class="cell-primary">{{ $business->phone_number ?? '—' }}</strong>
                        </div>

                        <div class="border-bottom py-2 mt-2">
                            <span class="text-muted small d-block">Address</span>
                            <strong class="cell-primary">{{ $business->address ?? '—' }}</strong>
                        </div>

                        <div class="border-bottom py-2 mt-2">
                            <span class="text-muted small d-block">Registered Date</span>
                            <strong class="cell-primary">{{ $business->created_at?->format('Y-m-d H:i') ?? '—' }}</strong>
                        </div>
                        
                        <div class="py-2 mt-2">
                            <span class="text-muted small d-block mb-1">Top Selling Items</span>
                            @if(is_array($business->top_selling_items) && count($business->top_selling_items) > 0)
                                <div class="d-flex flex-wrap gap-1 mt-1">
                                    @foreach($business->top_selling_items as $item)
                                        <span class="badge bg-label-success">{{ $item }}</span>
                                    @endforeach
                                </div>
                            @else
                                <span class="text-muted">—</span>
                            @endif
                        </div>
                    </div>

                    <div class="mt-4 border-top pt-3 text-center">
                        <a href="{{ route('admin.business-management.edit', $business) }}" class="btn btn-primary btn-sm w-100">
                            <i class="mdi mdi-pencil me-1"></i> Edit Profile
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

</div>
@endsection

@push('my-styles')
<style>
    .um-detail-avatar-placeholder {
        width: 80px;
        height: 80px;
        border-radius: 20px;
        background: #eef2ff;
        color: #4f46e5;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 2rem;
        font-weight: 800;
    }
</style>
@endpush
