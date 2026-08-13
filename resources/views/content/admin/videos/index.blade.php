@extends('layouts/contentNavbarLayout')

@section('title', 'Video Shorts')

@section('content')
<div class="admin-page videos-page">

    <div class="admin-page-header mb-4">
        <div class="admin-page-header__left">
            <h4 class="admin-page-header__title">Video Shorts</h4>
            <p class="admin-page-header__subtitle">Manage instructional short videos displayed on different app screens.</p>
        </div>
    </div>



    @if(session('success'))
        <div class="alert alert-success alert-dismissible" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="card mt-4">
        <div class="card-header d-flex justify-content-between align-items-center">
            <div>
                <h5 class="mb-0 fw-bold">Video Mappings</h5>
                <small class="text-muted">Search by screen name or YouTube short link.</small>
            </div>
            <div>
                <span class="badge bg-label-primary"><strong>{{ number_format($videos->total()) }}</strong> records</span>
            </div>
        </div>
        <div class="card-body">
            <!-- Filter & Search Form -->
            <form method="GET" id="filter-form" action="{{ route('admin.videos.index') }}">
                <input type="hidden" name="sort" value="{{ $sort }}">
                <input type="hidden" name="direction" value="{{ $direction }}">
                <input type="hidden" name="per_page" value="{{ $perPage }}">

                <div class="mb-4">
                    <label class="form-label fw-semibold">Global Search</label>
                    <div class="input-group input-group-merge border-primary">
                        <span class="input-group-text"><i class="mdi mdi-magnify text-primary"></i></span>
                        <input type="text" name="search" id="global-search-input" value="{{ $search }}" class="form-control" placeholder="Type anything to search (Screen Name or URL)..." autocomplete="off">
                    </div>
                </div>

                <div class="border p-3 rounded mb-4 bg-light">
                    <h6 class="mb-3 fw-bold"><i class="mdi mdi-filter-variant me-1"></i> Filter Options</h6>
                    <div class="row g-3 align-items-end">
                        <div class="col-12 col-md-10">
                            <label class="form-label fw-medium">Status</label>
                            <select name="status" class="form-select">
                                <option value="">All Statuses</option>
                                <option value="active" @selected(($status ?? '') === 'active')>Active</option>
                                <option value="inactive" @selected(($status ?? '') === 'inactive')>Inactive</option>
                            </select>
                        </div>
                        <div class="col-12 col-md-2 d-grid gap-2">
                            <button type="submit" class="btn btn-dark">
                                <i class="mdi mdi-check me-1"></i> Apply Filters
                            </button>
                        </div>
                    </div>
                </div>
            </form>

            <div class="table-responsive text-nowrap">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Screen Name</th>
                            <th>Short Video URL</th>
                            <th>Status</th>
                            <th>Created At</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody class="table-border-bottom-0">
                        @forelse($videos as $video)
                            <tr>
                                <td>
                                    <span class="fw-bold text-dark">{{ $video->screen_name }}</span>
                                </td>
                                <td>
                                    <a href="{{ $video->video_url }}" target="_blank" class="text-truncate d-inline-block" style="max-width: 300px;">
                                        {{ $video->video_url }}
                                    </a>
                                </td>
                                <td>
                                    @if($video->is_active)
                                        <span class="badge bg-label-success">Active</span>
                                    @else
                                        <span class="badge bg-label-secondary">Inactive</span>
                                    @endif
                                </td>
                                <td>{{ $video->created_at->format('Y-m-d H:i') }}</td>
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <a href="{{ route('admin.videos.edit', $video) }}" class="btn btn-xs btn-outline-primary">
                                            <i class="mdi mdi-pencil-outline me-1"></i> Edit
                                        </a>
                                        <form action="{{ route('admin.videos.destroy', $video) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this video mapping?');" class="d-inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-xs btn-outline-danger">
                                                <i class="mdi mdi-trash-can-outline me-1"></i> Delete
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center py-4 text-muted">
                                    <i class="mdi mdi-alert-circle-outline mdi-24px d-block mb-2"></i>
                                    No video mappings found.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="d-flex justify-content-between align-items-center mt-4">
                <div class="text-muted small">
                    Showing {{ $videos->firstItem() ?? 0 }} to {{ $videos->lastItem() ?? 0 }} of {{ $videos->total() }} records
                </div>
                <div>
                    {{ $videos->links() }}
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .videos-stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
        gap: 1.25rem;
    }
    .videos-stat-card {
        background: #fff;
        border: 1px solid #e9ecef;
        border-radius: 0.5rem;
        padding: 1.25rem;
        display: flex;
        align-items: center;
        gap: 1rem;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.02);
    }
    .videos-stat-card__icon {
        width: 3rem;
        height: 3rem;
        border-radius: 0.375rem;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.5rem;
    }
    .videos-stat-card__icon--primary {
        background-color: rgba(144, 104, 255, 0.08);
        color: #9068ff;
    }
    .videos-stat-card__icon--success {
        background-color: rgba(40, 199, 111, 0.08);
        color: #28c76f;
    }
    .videos-stat-card__icon--secondary {
        background-color: rgba(130, 134, 139, 0.08);
        color: #82868b;
    }
    .videos-stat-card__label {
        font-size: 0.8125rem;
        color: #82868b;
        font-weight: 500;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    .videos-stat-card__value {
        font-size: 1.5rem;
        font-weight: 700;
        color: #2f2b3d;
        line-height: 1.2;
        margin-top: 0.125rem;
    }
</style>
@endsection
