@extends('layouts/contentNavbarLayout')

@section('title', 'Create Subscription Plan')

@section('content')
<div class="admin-page">

    <div class="admin-page-header d-flex justify-content-between align-items-center mb-4">
        <div class="admin-page-header__left">
            <h4 class="admin-page-header__title">Create Pricing Plan</h4>
            <p class="admin-page-header__subtitle">Add a new package tier for mobile application subscribers.</p>
        </div>
        <div>
            <a href="{{ route('admin.subscription-plans.index') }}" class="btn btn-outline-secondary btn-sm">
                <i class="mdi mdi-arrow-left me-1"></i> Back to Plans
            </a>
        </div>
    </div>

    <div class="card mb-4" style="max-width: 700px;">
        <div class="card-header"><h5 class="mb-0">Subscription Plan Details</h5></div>
        <div class="card-body">
            <form action="{{ route('admin.subscription-plans.store') }}" method="POST">
                @csrf

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-semibold">Plan Title</label>
                        <input type="text" name="title" class="form-control" placeholder="e.g. Premium Plan" value="{{ old('title') }}" required>
                        @error('title')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-semibold">Status</label>
                        <select name="status" class="form-select" required>
                            <option value="active" @selected(old('status') === 'active')>Active</option>
                            <option value="inactive" @selected(old('status') === 'inactive')>Inactive</option>
                        </select>
                        @error('status')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-semibold">Price (Rs.)</label>
                        <input type="number" step="0.01" min="0" name="price" class="form-control" placeholder="e.g. 1999.00" value="{{ old('price') }}" required>
                        @error('price')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-semibold">Billing Period</label>
                        <select name="billing_period" class="form-select" required>
                            <option value="monthly" @selected(old('billing_period') === 'monthly')>Monthly</option>
                            <option value="yearly" @selected(old('billing_period') === 'yearly')>Yearly</option>
                            <option value="one-time" @selected(old('billing_period') === 'one-time')>One-time Lifetime</option>
                        </select>
                        @error('billing_period')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-12 mb-3">
                        <div class="form-check form-switch mt-1">
                            <input type="hidden" name="is_popular" value="0">
                            <input class="form-check-input" type="checkbox" name="is_popular" value="1" id="is_popular" @checked(old('is_popular') == 1)>
                            <label class="form-check-label fw-semibold" for="is_popular">Mark as Most Popular Plan</label>
                        </div>
                    </div>

                    <div class="col-12 mb-3">
                        <label class="form-label fw-semibold">Included Features (One feature per line)</label>
                        <textarea name="features" class="form-control" rows="6" placeholder="Feature 1&#10;Feature 2&#10;Feature 3">{{ old('features') }}</textarea>
                        <small class="text-muted">Enter features separated by pressing Enter (New Line). They will be stored as JSON in the database.</small>
                        @error('features')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                    </div>
                </div>

                <div class="d-flex gap-2 border-top pt-3 mt-3">
                    <button type="submit" class="btn btn-primary d-flex align-items-center gap-1">
                        <i class="mdi mdi-check"></i> Create Plan
                    </button>
                    <a href="{{ route('admin.subscription-plans.index') }}" class="btn btn-outline-secondary">
                        Cancel
                    </a>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection
