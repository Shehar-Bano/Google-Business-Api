@extends('layouts/contentNavbarLayout')

@section('title', 'Add Court')

@section('content')
<div class="admin-page courts-page">

    <div class="admin-page-header">
        <div class="admin-page-header__left">
            <h4 class="admin-page-header__title">Add Court</h4>
            <p class="admin-page-header__subtitle">Create a new court and assign it to a club.</p>
        </div>
        <div class="admin-page-header__actions">
            <a href="{{ route('admin.courts.index') }}" class="btn btn-outline-secondary">
                <i class="mdi mdi-arrow-left me-1"></i> Back
            </a>
        </div>
    </div>

    <form action="{{ route('admin.courts.store') }}" method="POST">
        @csrf

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
                        <h6 class="mb-0">Creation Notes</h6>
                    </div>
                    <div class="card-body">
                        <div class="courts-note-box">
                            <p class="mb-2 fw-semibold">Tips</p>
                            <ul class="mb-0 ps-3 text-muted small">
                                <li>Select the club first.</li>
                                <li>Use maintenance status for blocked courts.</li>
                                <li>Keep price and capacity accurate for bookings.</li>
                            </ul>
                        </div>
                    </div>
                </div>

                <div class="card">
                    <div class="card-body d-grid gap-2">
                        <button type="submit" class="btn btn-dark">
                            <i class="mdi mdi-check me-1"></i> Save Court
                        </button>
                        <a href="{{ route('admin.courts.index') }}" class="btn btn-outline-secondary">Cancel</a>
                    </div>
                </div>
            </div>
        </div>
    </form>

</div>
@endsection

@push('my-styles')
<style>
    .courts-note-box {
        background: linear-gradient(180deg, #f8fafc 0%, #ffffff 100%);
        border: 1px solid #e6ebf2;
        border-radius: 16px;
        padding: 1rem;
    }
</style>
@endpush
