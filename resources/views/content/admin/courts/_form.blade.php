@php
    $court = $court ?? null;
@endphp

<div class="row g-3">
    <div class="col-12">
        <label class="form-label">Club <span class="text-danger">*</span></label>
        <select name="club_id" class="form-select @error('club_id') is-invalid @enderror">
            <option value="">Select club</option>
            @foreach($clubs as $club)
                <option value="{{ $club->id }}" @selected(old('club_id', $court->club_id ?? '') == $club->id)>
                    {{ $club->club_name ?? $club->name }}
                </option>
            @endforeach
        </select>
        @error('club_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    <div class="col-12 col-md-6">
        <label class="form-label">Court Name <span class="text-danger">*</span></label>
        <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
            value="{{ old('name', $court->name ?? '') }}" placeholder="e.g. Court 1">
        @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    <div class="col-12 col-md-6">
        <label class="form-label">Court Type <span class="text-danger">*</span></label>
        <select name="type" class="form-select @error('type') is-invalid @enderror">
            @foreach(['glass' => 'Glass', 'wooden' => 'Wooden', 'synthetic' => 'Synthetic', 'other' => 'Other'] as $val => $label)
                <option value="{{ $val }}" @selected(old('type', $court->type ?? '') === $val)>{{ $label }}</option>
            @endforeach
        </select>
        @error('type')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    <div class="col-12 col-md-6">
        <label class="form-label">Price per Hour <span class="text-danger">*</span></label>
        <input type="number" name="price_per_hour" step="0.01" min="0" class="form-control @error('price_per_hour') is-invalid @enderror"
            value="{{ old('price_per_hour', $court->price_per_hour ?? '') }}" placeholder="0.00">
        @error('price_per_hour')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    <div class="col-12 col-md-6">
        <label class="form-label">Capacity</label>
        <input type="number" name="capacity" min="1" class="form-control @error('capacity') is-invalid @enderror"
            value="{{ old('capacity', $court->capacity ?? 2) }}">
        @error('capacity')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    <div class="col-12 col-md-6">
        <label class="form-label">Status <span class="text-danger">*</span></label>
        <select name="status" class="form-select @error('status') is-invalid @enderror">
            @foreach(['active' => 'Active', 'inactive' => 'Inactive', 'maintenance' => 'Maintenance'] as $val => $label)
                <option value="{{ $val }}" @selected(old('status', $court->status ?? 'active') === $val)>{{ $label }}</option>
            @endforeach
        </select>
        @error('status')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    <div class="col-12">
        <label class="form-label">Description</label>
        <textarea name="description" rows="3" class="form-control @error('description') is-invalid @enderror"
            placeholder="Optional notes about the court">{{ old('description', $court->description ?? '') }}</textarea>
        @error('description')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    <div class="col-12">
        <label class="form-label">Maintenance Note</label>
        <textarea name="maintenance_note" rows="2" class="form-control @error('maintenance_note') is-invalid @enderror"
            placeholder="Visible when court is under maintenance">{{ old('maintenance_note', $court->maintenance_note ?? '') }}</textarea>
        @error('maintenance_note')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
</div>
