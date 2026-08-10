@extends('layouts/contentNavbarLayout')

@section('title', 'Privacy Policy')

@section('content')
<div class="admin-page privacy-policy-page">
    <div class="admin-page-header">
        <div class="admin-page-header__left">
            <h4 class="admin-page-header__title">Privacy Policy</h4>
            <p class="admin-page-header__subtitle">Manage the privacy policy content returned by the API.</p>
        </div>
    </div>

    <form action="{{ route('admin.privacy-policy.update') }}" method="POST">
        @csrf
        @method('PUT')

        <div class="row g-4">
            <div class="col-12 col-xl-8">
                <div class="card">
                    <div class="card-header">
                        <h6 class="mb-0">Policy Content</h6>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="form-label">Page Title <span class="text-danger">*</span></label>
                            <input type="text" name="title" class="form-control @error('title') is-invalid @enderror"
                                value="{{ old('title', $privacyPolicy->title) }}" placeholder="Privacy Policy">
                            @error('title')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold">Content <span class="text-danger">*</span></label>
                            <textarea name="content" id="content" class="form-control @error('content') is-invalid @enderror" required>{{ old('content', $privacyPolicy->content) }}</textarea>
                            @error('content')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="form-check form-switch">
                            <input type="hidden" name="is_active" value="0">
                            <input class="form-check-input" type="checkbox" name="is_active" value="1" id="is_active"
                                @checked(old('is_active', $privacyPolicy->is_active))>
                            <label class="form-check-label" for="is_active">Active</label>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-12 col-xl-4">
                <div class="card">
                    <div class="card-body d-grid gap-2">
                        <button type="submit" class="btn btn-dark">
                            <i class="mdi mdi-content-save-outline me-1"></i> Save Policy
                        </button>
                        <a href="{{ route('admin.dashboard.index') }}" class="btn btn-outline-secondary">Cancel</a>
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
        });
    </script>
@endpush
