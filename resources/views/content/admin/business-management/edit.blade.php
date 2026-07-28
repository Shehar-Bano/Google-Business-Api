@extends('layouts/contentNavbarLayout')

@section('title', 'Edit Business Status')

@section('content')
<div class="admin-page">

    <div class="admin-page-header mb-4">
        <div class="admin-page-header__left">
            <h4 class="admin-page-header__title">Edit Business Status</h4>
            <p class="admin-page-header__subtitle">Change the active or suspension status of the business profile.</p>
        </div>
    </div>

    <div class="card mb-4" style="max-width: 600px;">
        <div class="card-header"><h5 class="mb-0">Update Status for {{ $business->name }}</h5></div>
        <div class="card-body">
            <form action="{{ route('admin.business-management.update', $business) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="mb-4">
                    <label class="form-label fw-semibold">Business Status</label>
                    <select name="status" class="form-select select-field" required>
                        <option value="approved" @selected(old('status', $business->status) === 'approved')>Approved</option>
                        <option value="suspended" @selected(old('status', $business->status) === 'suspended')>Suspended</option>
                    </select>
                    <small class="text-muted mt-1 d-block">Suspended businesses will have restricted portal and api access privileges.</small>
                    @error('status')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                </div>

                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-primary d-flex align-items-center gap-1">
                        <i class="mdi mdi-check"></i> Save Status
                    </button>
                    <a href="{{ route('admin.business-management.index') }}" class="btn btn-outline-secondary">
                        Cancel
                    </a>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection
