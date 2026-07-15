@extends('layouts/contentNavbarLayout')

@section('title', 'Poster Template Details')

@section('content')
<div class="admin-page">

    <div class="admin-page-header d-flex justify-content-between align-items-center mb-4">
        <div class="admin-page-header__left">
            <h4 class="admin-page-header__title">Poster Template Details</h4>
            <p class="admin-page-header__subtitle">Viewing metadata for template #{{ $poster->id }}</p>
        </div>
        <div>
            <a href="{{ route('admin.posters.index') }}" class="btn btn-outline-secondary btn-sm">
                <i class="mdi mdi-arrow-left me-1"></i> Back to Templates
            </a>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-md-4">
            <div class="card h-100">
                <div class="card-header border-bottom"><h6 class="mb-0 fw-bold">Design Image</h6></div>
                <div class="card-body text-center pt-4">
                    <img src="{{ asset($poster->image) }}" alt="{{ $poster->title }}" class="img-fluid rounded" style="max-height: 350px; object-fit: contain; border: 1px solid #e2e8f0; width: 100%;">
                </div>
            </div>
        </div>

        <div class="col-md-8">
            <div class="card h-100">
                <div class="card-header border-bottom"><h6 class="mb-0 fw-bold">Template Profile</h6></div>
                <div class="card-body pt-3">
                    <div class="border-bottom py-2">
                        <span class="text-muted small d-block">Title</span>
                        <strong class="cell-primary text-capitalize" style="font-size: 1.1rem;">{{ $poster->title }}</strong>
                    </div>

                    <div class="border-bottom py-2 mt-2">
                        <span class="text-muted small d-block">Status</span>
                        <span class="badge {{ $poster->status === 'Active' ? 'bg-label-success' : 'bg-label-danger' }} mt-1">
                            {{ $poster->status }}
                        </span>
                    </div>

                    <div class="border-bottom py-2 mt-2">
                        <span class="text-muted small d-block">Registered Date</span>
                        <strong class="cell-primary">{{ $poster->created_at?->format('Y-m-d H:i') ?? '—' }}</strong>
                    </div>

                    <div class="py-2 mt-2">
                        <span class="text-muted small d-block">Last Updated</span>
                        <strong class="cell-primary">{{ $poster->updated_at?->format('Y-m-d H:i') ?? '—' }}</strong>
                    </div>

                    <div class="mt-4 border-top pt-3">
                        <a href="{{ route('admin.posters.edit', $poster) }}" class="btn btn-primary">
                            <i class="mdi mdi-pencil me-1"></i> Edit Template
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

</div>
@endsection
