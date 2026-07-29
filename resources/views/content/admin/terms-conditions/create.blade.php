@extends('layouts/contentNavbarLayout')

@section('title', 'Create Terms & Conditions')

@section('content')
<div class="admin-page">

    <div class="admin-page-header d-flex justify-content-between align-items-center mb-4">
        <div class="admin-page-header__left">
            <h4 class="admin-page-header__title">Create Terms Page</h4>
            <p class="admin-page-header__subtitle">Add a new terms or user agreement document.</p>
        </div>
        <div>
            <a href="{{ route('admin.terms-conditions.index') }}" class="btn btn-outline-secondary btn-sm">
                <i class="mdi mdi-arrow-left me-1"></i> Back to Terms
            </a>
        </div>
    </div>

    <form action="{{ route('admin.terms-conditions.store') }}" method="POST">
        @csrf

        <div class="row g-4">
            <div class="col-12 col-xl-9">
                <div class="card mb-4">
                    <div class="card-header"><h5 class="mb-0">Page Editor</h5></div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-semibold">Title <span class="text-danger">*</span></label>
                                <input type="text" name="title" id="title" class="form-control @error('title') is-invalid @enderror" placeholder="e.g. Terms of Service" value="{{ old('title') }}" required>
                                @error('title')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-semibold">URL Slug (Auto-generated if empty)</label>
                                <input type="text" name="slug" id="slug" class="form-control @error('slug') is-invalid @enderror" placeholder="e.g. terms-of-service" value="{{ old('slug') }}">
                                @error('slug')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>

                            <div class="col-12 mb-3">
                                <label class="form-label fw-semibold">Content <span class="text-danger">*</span></label>
                                <textarea name="content" id="content" class="form-control @error('content') is-invalid @enderror" required>{{ old('content') }}</textarea>
                                @error('content')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-12 col-xl-3">
                <div class="card mb-4">
                    <div class="card-header"><h5 class="mb-0">Publish Details</h5></div>
                    <div class="card-body d-grid gap-3">
                        <div>
                            <label class="form-label fw-semibold">Status</label>
                            <select name="status" class="form-select" required>
                                <option value="Active" @selected(old('status') === 'Active')>Active</option>
                                <option value="Inactive" @selected(old('status') === 'Inactive')>Inactive</option>
                            </select>
                            @error('status')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                        </div>

                        <div class="d-grid gap-2 border-top pt-3">
                            <button type="submit" class="btn btn-primary d-flex align-items-center justify-content-center gap-1">
                                <i class="mdi mdi-check"></i> Create Page
                            </button>
                            <a href="{{ route('admin.terms-conditions.index') }}" class="btn btn-outline-secondary">
                                Cancel
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>

</div>
@endsection

@push('my-script')
    <script src="https://cdn.ckeditor.com/4.22.1/standard/ckeditor.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            CKEDITOR.replace('content', {
                height: 400,
                removeButtons: 'About',
                allowedContent: true // Keep HTML formatting exactly as input
            });

            // Auto-slug generation from title
            const titleInput = document.getElementById('title');
            const slugInput = document.getElementById('slug');

            titleInput.addEventListener('input', function() {
                if (!slugInput.value || slugInput.dataset.edited !== 'true') {
                    slugInput.value = titleInput.value
                        .toLowerCase()
                        .replace(/[^a-z0-9 -]/g, '') // remove invalid chars
                        .replace(/\s+/g, '-')        // collapse whitespace and replace by -
                        .replace(/-+/g, '-');        // collapse dashes
                }
            });

            slugInput.addEventListener('change', function() {
                slugInput.dataset.edited = 'true';
            });
        });
    </script>
@endpush
