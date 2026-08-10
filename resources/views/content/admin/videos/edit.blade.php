@extends('layouts/contentNavbarLayout')

@section('title', 'Edit Video Short')

@section('content')
<div class="admin-page videos-page">
    <div class="admin-page-header mb-4">
        <div class="admin-page-header__left">
            <h4 class="admin-page-header__title">Edit Video Short</h4>
            <p class="admin-page-header__subtitle">Modify screen name mapping or YouTube short URL.</p>
        </div>
    </div>

    <form action="{{ route('admin.videos.update', $video) }}" method="POST">
        @csrf
        @method('PUT')

        <div class="row g-4">
            <div class="col-12 col-xl-8">
                <div class="card">
                    <div class="card-header">
                        <h6 class="mb-0 fw-bold">Video Details</h6>
                    </div>
                    <div class="card-body">
                        <!-- Screen Name -->
                        <div class="mb-3">
                            <label for="screen_name" class="form-label">Screen Name <span class="text-danger">*</span></label>
                            <input type="text" name="screen_name" id="screen_name" class="form-control @error('screen_name') is-invalid @enderror" value="{{ old('screen_name', $video->screen_name) }}" placeholder="e.g. business_name" required>
                            @error('screen_name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="form-text text-muted">Specify the system-level name of the screen (e.g. `business_name`, `top_selling_items`).</small>
                        </div>

                        <!-- Video URL -->
                        <div class="mb-3">
                            <label for="video_url" class="form-label">Short Video URL <span class="text-danger">*</span></label>
                            <input type="url" name="video_url" id="video_url" class="form-control @error('video_url') is-invalid @enderror" value="{{ old('video_url', $video->video_url) }}" placeholder="https://youtube.com/shorts/..." required>
                            @error('video_url')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Status Toggle -->
                        <div class="mb-3">
                            <div class="form-check form-switch mt-4">
                                <input type="hidden" name="is_active" value="0">
                                <input class="form-check-input" type="checkbox" name="is_active" id="is_active" value="1" {{ old('is_active', $video->is_active ? '1' : '0') == '1' ? 'checked' : '' }}>
                                <label class="form-check-label fw-semibold" for="is_active">Status Active</label>
                            </div>
                            <small class="form-text text-muted d-block mt-1">If inactive, the video won't be sent in the API payload.</small>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-12 col-xl-4">
                <div class="card">
                    <div class="card-body d-grid gap-2">
                        <button type="submit" class="btn btn-dark">
                            <i class="mdi mdi-check me-1"></i> Update Video Mappings
                        </button>
                        <a href="{{ route('admin.videos.index') }}" class="btn btn-outline-secondary">Cancel</a>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>
@endsection
