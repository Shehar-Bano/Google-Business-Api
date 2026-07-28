@extends('layouts/contentNavbarLayout')

@section('title', 'Edit Subscription Plan')

@section('content')
<div class="admin-page">

    <div class="admin-page-header d-flex justify-content-between align-items-center mb-4">
        <div class="admin-page-header__left">
            <h4 class="admin-page-header__title">Edit Pricing Plan</h4>
            <p class="admin-page-header__subtitle">Modify tier parameters, pricing, or included features list.</p>
        </div>
        <div>
            <a href="{{ route('admin.subscription-plans.index') }}" class="btn btn-outline-secondary btn-sm">
                <i class="mdi mdi-arrow-left me-1"></i> Back to Plans
            </a>
        </div>
    </div>

    <div class="card mb-4" style="max-width: 700px;">
        <div class="card-header"><h5 class="mb-0">Edit Subscription Plan Details</h5></div>
        <div class="card-body">
            <form action="{{ route('admin.subscription-plans.update', $subscriptionPlan) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-semibold">Plan Title</label>
                        <input type="text" name="title" class="form-control" placeholder="e.g. Premium Plan" value="{{ old('title', $subscriptionPlan->title) }}" required>
                        @error('title')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-semibold">Status</label>
                        <select name="status" class="form-select" required>
                            <option value="active" @selected(old('status', $subscriptionPlan->status) === 'active')>Active</option>
                            <option value="inactive" @selected(old('status', $subscriptionPlan->status) === 'inactive')>Inactive</option>
                        </select>
                        @error('status')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-semibold">Price (Rs.)</label>
                        <input type="number" step="0.01" min="0" name="price" class="form-control" placeholder="e.g. 1999.00" value="{{ old('price', $subscriptionPlan->price) }}" required>
                        @error('price')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-semibold">Billing Period</label>
                        <select name="billing_period" class="form-select" required>
                            <option value="monthly" @selected(old('billing_period', $subscriptionPlan->billing_period) === 'monthly')>Monthly</option>
                            <option value="yearly" @selected(old('billing_period', $subscriptionPlan->billing_period) === 'yearly')>Yearly</option>
                            <option value="one-time" @selected(old('billing_period', $subscriptionPlan->billing_period) === 'one-time')>One-time Lifetime</option>
                        </select>
                        @error('billing_period')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-12 mb-3">
                        <label class="form-label fw-semibold">Included Features (One feature per line)</label>
                        <textarea name="features" class="form-control" rows="6" placeholder="Feature 1&#10;Feature 2&#10;Feature 3">{{ old('features', $featuresString) }}</textarea>
                        <small class="text-muted">Enter features separated by pressing Enter (New Line). They will be stored as JSON in the database.</small>
                        @error('features')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                    </div>
                </div>

                <div class="d-flex gap-2 border-top pt-3 mt-3">
                    <button type="submit" class="btn btn-primary d-flex align-items-center gap-1">
                        <i class="mdi mdi-check"></i> Save Changes
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
