@extends('layouts/contentNavbarLayout')

@section('title', 'Add Plan Feature')

@section('content')
<div class="admin-page plan-features-create-page">

    <div class="admin-page-header d-flex justify-content-between align-items-center mb-4">
        <div class="admin-page-header__left">
            <h4 class="admin-page-header__title">Add New Plan Feature</h4>
            <p class="admin-page-header__subtitle">Create a reusable feature that can be linked to subscription plans.</p>
        </div>
        <div>
            <a href="{{ route('admin.plan-features.index') }}" class="btn btn-outline-secondary btn-sm">
                <i class="mdi mdi-arrow-left me-1"></i> Back to Listing
            </a>
        </div>
    </div>

    <div class="row">
        <div class="col-12 col-md-8 col-lg-7">
            <div class="card">
                <div class="card-header border-bottom">
                    <h5 class="mb-0 fw-bold">Feature Information</h5>
                </div>
                <div class="card-body pt-4">
                    <form action="{{ route('admin.plan-features.store') }}" method="POST">
                        @csrf

                        <div class="mb-3">
                            <label class="form-label fw-semibold" for="name">Feature Name <span class="text-danger">*</span></label>
                            <input type="text" name="name" id="feature-name" class="form-control @error('name') is-invalid @enderror"
                                value="{{ old('name') }}" placeholder="e.g. AI Poster Generation" required>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold" for="slug">Slug (Auto-generated) <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <input type="text" name="slug" id="feature-slug" class="form-control font-monospace @error('slug') is-invalid @enderror"
                                    value="{{ old('slug') }}" placeholder="e.g. ai-poster-generation" required>
                            </div>
                            <small class="form-text text-muted">Unique identifier used by system/APIs. Auto-generated from name.</small>
                            @error('slug')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold" for="status">Status <span class="text-danger">*</span></label>
                            <select name="status" id="status" class="form-select @error('status') is-invalid @enderror" required>
                                <option value="active" @selected(old('status', 'active') === 'active')>Active</option>
                                <option value="inactive" @selected(old('status') === 'inactive')>Inactive</option>
                            </select>
                            @error('status')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-4">
                            <label class="form-label fw-semibold" for="description">Description (Optional)</label>
                            <textarea name="description" id="description" rows="3" class="form-control @error('description') is-invalid @enderror"
                                placeholder="Short explanation of what this feature includes...">{{ old('description') }}</textarea>
                            @error('description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="d-flex justify-content-end gap-2 pt-2 border-top">
                            <a href="{{ route('admin.plan-features.index') }}" class="btn btn-outline-secondary">Cancel</a>
                            <button type="submit" class="btn btn-primary">
                                <i class="mdi mdi-content-save me-1"></i> Save Feature
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

</div>

@push('page-script')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const nameInput = document.getElementById('feature-name');
    const slugInput = document.getElementById('feature-slug');
    let isSlugManual = false;

    slugInput.addEventListener('input', function () {
        isSlugManual = slugInput.value.trim() !== '';
    });

    nameInput.addEventListener('input', function () {
        if (!isSlugManual || slugInput.value.trim() === '') {
            slugInput.value = nameInput.value
                .toLowerCase()
                .trim()
                .replace(/[^a-z0-9\s-]/g, '')
                .replace(/[\s_-]+/g, '-')
                .replace(/^-+|-+$/g, '');
        }
    });
});
</script>
@endpush
@endsection
