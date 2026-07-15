@extends('layouts/contentNavbarLayout')

@section('title', 'Edit Poster Template')

@section('content')
<div class="admin-page">

    <div class="admin-page-header mb-4">
        <div class="admin-page-header__left">
            <h4 class="admin-page-header__title">Edit Poster Template</h4>
            <p class="admin-page-header__subtitle">Modify template details and design resources.</p>
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-header"><h5 class="mb-0">Edit Poster Template</h5></div>
        <div class="card-body">
            <form action="{{ route('admin.posters.update', $poster) }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-semibold">Title</label>
                        <input type="text" name="title" class="form-control" value="{{ old('title', $poster->title) }}" required>
                        @error('title')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-semibold">Status</label>
                        <select name="status" class="form-select" required>
                            <option value="Active" @selected(old('status', $poster->status) === 'Active')>Active</option>
                            <option value="Inactive" @selected(old('status', $poster->status) === 'Inactive')>Inactive</option>
                        </select>
                        @error('status')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-md-8 mb-3">
                        <label class="form-label fw-semibold">Replace Template Image (optional)</label>
                        <input type="file" name="image" class="form-control">
                        <small class="text-muted">Only images (JPEG, PNG, JPG, WEBP) are allowed. Max size 5MB. Leave blank to keep current image.</small>
                        @error('image')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-md-4 mb-3">
                        <label class="form-label fw-semibold d-block">Current Design</label>
                        <img src="{{ asset($poster->image) }}" alt="{{ $poster->title }}" class="rounded" style="width: 120px; height: 120px; object-fit: cover; border: 1px solid #cbd5e1;">
                    </div>
                </div>

                <div class="mt-4">
                    <button type="submit" class="btn btn-primary">Update Template</button>
                    <a href="{{ route('admin.posters.index') }}" class="btn btn-outline-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection
