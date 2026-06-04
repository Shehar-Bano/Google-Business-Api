@extends('layouts/contentNavbarLayout')

@section('title', (isset($court) ? 'Edit' : 'Add') . ' Court — ' . $club->club_name)

@section('content')
<div class="d-flex align-items-center gap-2 mb-4">
    <a href="{{ route('admin.clubs.show', $club) }}" class="btn btn-sm btn-outline-secondary">
        <i class="mdi mdi-arrow-left"></i> Back to Club
    </a>
    <h5 class="mb-0">{{ isset($court) ? 'Edit Court' : 'Add Court' }}</h5>
</div>

<div class="card" style="max-width:640px;">
    <div class="card-header">
        <h6 class="mb-0">{{ $club->club_name }}</h6>
    </div>
    <div class="card-body">
        <form action="{{ isset($court) ? route('admin.clubs.courts.update', [$club, $court]) : route('admin.clubs.courts.store', $club) }}" method="POST">
            @csrf
            @if(isset($court)) @method('PUT') @endif

            <div class="row g-3">
                <div class="col-12">
                    <label class="form-label">Court Name <span class="text-danger">*</span></label>
                    <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                        value="{{ old('name', $court->name ?? '') }}" placeholder="e.g. Court 1">
                    @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-12 col-md-6">
                    <label class="form-label">Court Type</label>
                    <select name="type" class="form-select @error('type') is-invalid @enderror">
                        @foreach(['glass' => 'Glass', 'wooden' => 'Wooden', 'synthetic' => 'Synthetic', 'other' => 'Other'] as $val => $label)
                            <option value="{{ $val }}" @selected(old('type', $court->type ?? '') === $val)>{{ $label }}</option>
                        @endforeach
                    </select>
                    @error('type')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-12 col-md-6">
                    <label class="form-label">Price per Hour ($) <span class="text-danger">*</span></label>
                    <input type="number" name="price_per_hour" step="0.01" min="0" class="form-control @error('price_per_hour') is-invalid @enderror"
                        value="{{ old('price_per_hour', $court->price_per_hour ?? '') }}" placeholder="0.00">
                    @error('price_per_hour')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-12 col-md-6">
                    <label class="form-label">Status</label>
                    <select name="status" class="form-select @error('status') is-invalid @enderror">
                        @foreach(['active' => 'Active', 'inactive' => 'Inactive', 'maintenance' => 'Maintenance'] as $val => $label)
                            <option value="{{ $val }}" @selected(old('status', $court->status ?? 'active') === $val)>{{ $label }}</option>
                        @endforeach
                    </select>
                    @error('status')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-12 col-md-6">
                    <label class="form-label">Capacity (players)</label>
                    <input type="number" name="capacity" min="1" class="form-control @error('capacity') is-invalid @enderror"
                        value="{{ old('capacity', $court->capacity ?? 2) }}">
                    @error('capacity')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-12">
                    <label class="form-label">Description</label>
                    <textarea name="description" rows="3" class="form-control @error('description') is-invalid @enderror"
                        placeholder="Optional notes about this court">{{ old('description', $court->description ?? '') }}</textarea>
                    @error('description')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
            </div>

            <div class="d-flex gap-2 mt-4">
                <button type="submit" class="btn btn-dark">{{ isset($court) ? 'Save Changes' : 'Add Court' }}</button>
                <a href="{{ route('admin.clubs.show', $club) }}" class="btn btn-outline-secondary">Cancel</a>
            </div>
        </form>
    </div>
</div>
@endsection
