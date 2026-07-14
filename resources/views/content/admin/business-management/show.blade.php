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

    <div class="row g-4">
        {{-- Profile Card --}}
        <div class="col-lg-4">
            <div class="card h-100">
                <div class="card-body text-center pt-4">
                    <div class="um-detail-avatar-placeholder mx-auto mb-3 bg-label-primary text-primary">
                        {{ strtoupper(substr($business->name, 0, 1)) }}
                    </div>

                    <h5 class="mb-1 fw-bold">{{ $business->name }}</h5>
                    <p class="text-muted mb-3"><i class="mdi mdi-map-marker me-1 text-danger"></i>{{ $business->location }}</p>

                    <div class="text-start mt-4">
                        <div class="border-bottom py-2">
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

        {{-- Products & Services Catalog Card --}}
        <div class="col-lg-8">
            <div class="card h-100">
                <div class="card-header border-bottom d-flex justify-content-between align-items-center">
                    <h6 class="mb-0 fw-bold">Products & Services Catalog</h6>
                    <span class="badge bg-primary">{{ $business->offerings->count() }} Offerings</span>
                </div>
                <div class="card-body pt-3">
                    @if($business->offerings->count() > 0)
                        @php
                            // Group offerings by subcategory
                            $groupedOfferings = $business->offerings->groupBy(function($offering) {
                                return ($offering->subcategory->category->name ?? 'Category') . ' — ' . ($offering->subcategory->name ?? 'Subcategory');
                            });
                        @endphp

                        <div class="list-group list-group-flush">
                            @foreach($groupedOfferings as $subName => $offerings)
                                <div class="mb-3 border-bottom pb-2">
                                    <h6 class="fw-semibold text-primary mb-2">
                                        <i class="mdi mdi-folder-open-outline me-1"></i> {{ $subName }}
                                    </h6>
                                    <div class="row">
                                        @foreach($offerings as $offering)
                                            <div class="col-md-6 mb-2">
                                                <div class="d-flex align-items-center gap-2">
                                                    <i class="mdi {{ $offering->type === 'product' ? 'mdi-tag text-info' : 'mdi-hand-heart text-warning' }} mdi-18px"></i>
                                                    <div>
                                                        <span class="cell-primary fw-medium">{{ $offering->name }}</span>
                                                        <span class="badge {{ $offering->type === 'product' ? 'bg-label-info' : 'bg-label-warning' }} btn-xs ms-1">{{ $offering->type }}</span>
                                                    </div>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-5 text-muted">
                            <i class="mdi mdi-package-variant-closed mdi-48px mb-2 d-block"></i>
                            No offerings associated with this business. Click edit to assign items from the master catalog.
                        </div>
                    @endif
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
