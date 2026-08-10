@extends('layouts/contentNavbarLayout')

@section('title', 'Add Support Option')

@section('content')
<div class="admin-page support-options-page">
    <div class="admin-page-header">
        <div class="admin-page-header__left">
            <h4 class="admin-page-header__title">Add Support Option</h4>
            <p class="admin-page-header__subtitle">Create a support channel for the app.</p>
        </div>
        <div class="admin-page-header__actions">
            <a href="{{ route('admin.support-options.index') }}" class="btn btn-outline-secondary">
                <i class="mdi mdi-arrow-left me-1"></i> Back
            </a>
        </div>
    </div>

    <form action="{{ route('admin.support-options.store') }}" method="POST" enctype="multipart/form-data">
        @csrf

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
                <div class="card">
                    <div class="card-body d-grid gap-2">
                        <button type="submit" class="btn btn-dark">
                            <i class="mdi mdi-check me-1"></i> Save Option
                        </button>
                        <a href="{{ route('admin.support-options.index') }}" class="btn btn-outline-secondary">Cancel</a>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>
@endsection
