@extends('layouts/contentNavbarLayout')

@section('title', 'Add Poster Template')

@section('content')
<div class="admin-page">

    <div class="admin-page-header mb-4">
        <div class="admin-page-header__left">
            <h4 class="admin-page-header__title">Add Poster Template</h4>
            <p class="admin-page-header__subtitle">Create a new poster design template for AI poster generations.</p>
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-header"><h5 class="mb-0">Poster Template Details</h5></div>
        <div class="card-body">
            <form action="{{ route('admin.posters.store') }}" method="POST" enctype="multipart/form-data">
                @csrf

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-semibold">Title</label>
                        <input type="text" name="title" class="form-control" value="{{ old('title') }}" placeholder="e.g. Summer Special Sale" required>
                        @error('title')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-semibold">Status</label>
                        <select name="status" class="form-select" required>
                            <option value="Active" @selected(old('status') === 'Active')>Active</option>
                            <option value="Inactive" @selected(old('status') === 'Inactive')>Inactive</option>
                        </select>
                        @error('status')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-12 mb-3">
                        <label class="form-label fw-semibold">Template Image</label>
                        <input type="file" name="image" class="form-control" required>
                        <small class="text-muted">Only images (JPEG, PNG, JPG, WEBP) are allowed. Max size 5MB.</small>
                        @error('image')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                    </div>
                </div>

                <div class="mt-4">
                    <button type="submit" class="btn btn-primary">Save Template</button>
                    <a href="{{ route('admin.posters.index') }}" class="btn btn-outline-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection
