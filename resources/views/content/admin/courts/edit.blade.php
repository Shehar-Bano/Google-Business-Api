@extends('layouts/contentNavbarLayout')

@section('title', 'Edit Court')

@section('content')
<div class="admin-page courts-page">

    <div class="admin-page-header">
        <div class="admin-page-header__left">
            <h4 class="admin-page-header__title">Edit Court</h4>
            <p class="admin-page-header__subtitle">Update court settings, pricing, or status.</p>
        </div>
        <div class="admin-page-header__actions">
            <a href="{{ route('admin.courts.show', $court) }}" class="btn btn-outline-secondary">
                <i class="mdi mdi-eye-outline me-1"></i> View
            </a>
            <a href="{{ route('admin.courts.index') }}" class="btn btn-outline-secondary">
                <i class="mdi mdi-arrow-left me-1"></i> Back
            </a>
        </div>
    </div>

    <form action="{{ route('admin.courts.update', $court) }}" method="POST">
        @csrf
        @method('PUT')

        <div class="row g-4">
            <div class="col-12 col-xl-8">
                <div class="card">
                    <div class="card-header">
                        <h6 class="mb-0">Court Details</h6>
                    </div>
                    <div class="card-body">
                        @include('content.admin.courts._form')
                    </div>
                </div>
            </div>

            <div class="col-12 col-xl-4">
                <div class="card mb-4">
                    <div class="card-header">
                        <h6 class="mb-0">Current Snapshot</h6>
                    </div>
                    <div class="card-body">
                        <div class="courts-note-box mb-3">
                            <div class="d-flex align-items-center justify-content-between mb-2">
                                <span class="text-muted small">Court ID</span>
                                <strong>#{{ $court->id }}</strong>
                            </div>
                            <div class="d-flex align-items-center justify-content-between mb-2">
                                <span class="text-muted small">Club</span>
                                <strong>{{ $court->club?->club_name ?? '—' }}</strong>
                            </div>
                            <div class="d-flex align-items-center justify-content-between">
                                <span class="text-muted small">Status</span>
                                <strong>{{ \Illuminate\Support\Str::headline($court->status) }}</strong>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card">
                    <div class="card-body d-grid gap-2">
                        <button type="submit" class="btn btn-dark">
                            <i class="mdi mdi-content-save-outline me-1"></i> Save Changes
                        </button>
                        <a href="{{ route('admin.courts.index') }}" class="btn btn-outline-secondary">Cancel</a>
                    </div>
                </div>
            </div>
        </div>
    </form>

</div>
@endsection
