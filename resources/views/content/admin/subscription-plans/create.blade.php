@extends('layouts/contentNavbarLayout')

@section('title', 'Create Subscription Plan')

@section('page-style')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<style>
.select2-container--default .select2-selection--multiple {
    border: 1px solid #d9dee3;
    border-radius: 0.375rem;
    min-height: 42px;
    padding: 3px 6px;
}
.select2-container--default.select2-container--focus .select2-selection--multiple {
    border-color: #696cff;
}
.select2-container--default .select2-selection--multiple .select2-selection__choice {
    background-color: #e7e7ff;
    border: 1px solid #696cff;
    color: #696cff;
    border-radius: 4px;
    padding: 2px 8px;
    font-weight: 500;
}
.select2-container--default .select2-selection--multiple .select2-selection__choice__remove {
    color: #696cff;
    margin-right: 5px;
}
</style>
@endsection

@section('content')
<div class="admin-page">

    <div class="admin-page-header d-flex justify-content-between align-items-center mb-4">
        <div class="admin-page-header__left">
            <h4 class="admin-page-header__title">Create Pricing Plan</h4>
            <p class="admin-page-header__subtitle">Add a new package tier and assign plan features for subscribers.</p>
        </div>
        <div>
            <a href="{{ route('admin.subscription-plans.index') }}" class="btn btn-outline-secondary btn-sm">
                <i class="mdi mdi-arrow-left me-1"></i> Back to Plans
            </a>
        </div>
    </div>

    <div class="card mb-4" style="max-width: 750px;">
        <div class="card-header border-bottom"><h5 class="mb-0 fw-bold">Subscription Plan Details</h5></div>
        <div class="card-body pt-4">
            <form action="{{ route('admin.subscription-plans.store') }}" method="POST">
                @csrf

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-semibold">Plan Title <span class="text-danger">*</span></label>
                        <input type="text" name="title" class="form-control" placeholder="e.g. Premium Plan" value="{{ old('title') }}" required>
                        @error('title')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-semibold">Status <span class="text-danger">*</span></label>
                        <select name="status" class="form-select" required>
                            <option value="active" @selected(old('status') === 'active')>Active</option>
                            <option value="inactive" @selected(old('status') === 'inactive')>Inactive</option>
                        </select>
                        @error('status')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-semibold">Price (Rs.) <span class="text-danger">*</span></label>
                        <input type="number" step="0.01" min="0" name="price" class="form-control" placeholder="e.g. 1999.00" value="{{ old('price') }}" required>
                        @error('price')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-semibold">Billing Period <span class="text-danger">*</span></label>
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

                    <div class="col-12 mb-4">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <label class="form-label fw-semibold mb-0" for="feature_ids">Select Plan Features (Select2 Multi-Select)</label>
                            <a href="{{ route('admin.plan-features.create') }}" target="_blank" class="small text-primary">
                                <i class="mdi mdi-plus me-1"></i> Add New Feature
                            </a>
                        </div>
                        <select name="feature_ids[]" id="feature_ids" class="form-select select2-multiple" multiple="multiple" style="width: 100%;">
                            @foreach($features as $feature)
                                <option value="{{ $feature->id }}" @selected(in_array($feature->id, old('feature_ids', [])))>
                                    {{ $feature->name }} ({{ $feature->slug }})
                                </option>
                            @endforeach
                        </select>
                        <small class="text-muted">Select multiple features to include in this subscription tier.</small>
                        @error('feature_ids')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                    </div>
                </div>

                <div class="d-flex justify-content-end gap-2 border-top pt-3 mt-2">
                    <a href="{{ route('admin.subscription-plans.index') }}" class="btn btn-outline-secondary">
                        Cancel
                    </a>
                    <button type="submit" class="btn btn-primary d-flex align-items-center gap-1">
                        <i class="mdi mdi-check"></i> Create Plan
                    </button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection

@section('page-script')
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
$(document).ready(function() {
    $('#feature_ids').select2({
        placeholder: 'Select plan features...',
        allowClear: true,
        width: '100%'
    });
});
</script>
@endsection
