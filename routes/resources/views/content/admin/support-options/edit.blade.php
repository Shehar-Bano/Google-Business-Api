@extends('layouts/contentNavbarLayout')

@section('title', 'Edit Support Option')

@section('content')
<div class="admin-page support-options-page">
    <div class="admin-page-header">
        <div class="admin-page-header__left">
            <h4 class="admin-page-header__title">Edit Support Option</h4>
            <p class="admin-page-header__subtitle">Update the support channel details shown to players.</p>
        </div>
        <div class="admin-page-header__actions">
            <a href="{{ route('admin.support-options.index') }}" class="btn btn-outline-secondary">
                <i class="mdi mdi-arrow-left me-1"></i> Back
            </a>
        </div>
    </div>

    <form action="{{ route('admin.support-options.update', $supportOption) }}" method="POST" enctype="multipart/form-data">
        @csrf
        @method('PUT')

        <div class="row g-4">
            <div class="col-12 col-xl-8">
                <div class="card">
                    <div class="card-header">
                        <h6 class="mb-0">Support Details</h6>
                    </div>
                    <div class="card-body">
                        @include('content.admin.support-options._form')
                    </div>
                </div>
            </div>

            <div class="col-12 col-xl-4">
                <div class="card mb-4">
                    <div class="card-header">
                        <h6 class="mb-0">Current Snapshot</h6>
                    </div>
                    <div class="card-body">
                        <div class="d-flex align-items-center justify-content-between mb-2">
                            <span class="text-muted small">Option ID</span>
                            <strong>#{{ $supportOption->id }}</strong>
                        </div>
                        <div class="d-flex align-items-center justify-content-between mb-2">
                            <span class="text-muted small">Type</span>
                            <strong>{{ \Illuminate\Support\Str::headline($supportOption->type) }}</strong>
                        </div>
                        <div class="d-flex align-items-center justify-content-between">
                            <span class="text-muted small">Status</span>
                            <strong>{{ $supportOption->is_active ? 'Active' : 'Inactive' }}</strong>
                        </div>
                    </div>
                </div>

                <div class="card">
                    <div class="card-body d-grid gap-2">
                        <button type="submit" class="btn btn-dark">
                            <i class="mdi mdi-content-save-outline me-1"></i> Save Changes
                        </button>
                        <a href="{{ route('admin.support-options.index') }}" class="btn btn-outline-secondary">Cancel</a>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>
@endsection
