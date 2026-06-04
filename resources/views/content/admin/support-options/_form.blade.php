@php
    $supportOption = $supportOption ?? null;
    $selectedType = old('type', $supportOption->type ?? 'whatsapp');
@endphp

<div class="row g-3">
    <div class="col-12 col-md-6">
        <label class="form-label">Title <span class="text-danger">*</span></label>
        <input type="text" name="title" class="form-control @error('title') is-invalid @enderror"
            value="{{ old('title', $supportOption->title ?? '') }}" placeholder="e.g. WhatsApp Support">
        @error('title')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    <div class="col-12 col-md-6">
        <label class="form-label">Type <span class="text-danger">*</span></label>
        <select name="type" class="form-select @error('type') is-invalid @enderror">
            @foreach(['whatsapp' => 'WhatsApp', 'call' => 'Call', 'email' => 'Email', 'chat' => 'Chat', 'website' => 'Website'] as $value => $label)
                <option value="{{ $value }}" @selected($selectedType === $value)>{{ $label }}</option>
            @endforeach
        </select>
        @error('type')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    <div class="col-12 col-md-8">
        <label class="form-label">Value <span class="text-danger">*</span></label>
        <input type="text" name="value" class="form-control @error('value') is-invalid @enderror"
            value="{{ old('value', $supportOption->value ?? '') }}" placeholder="+923001234567 or support@example.com">
        @error('value')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    <div class="col-12 col-md-4">
        <label class="form-label">Sort Order</label>
        <input type="number" name="sort_order" min="0" class="form-control @error('sort_order') is-invalid @enderror"
            value="{{ old('sort_order', $supportOption->sort_order ?? 0) }}">
        @error('sort_order')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    <div class="col-12">
        <label class="form-label">Image/Icon</label>
        <input type="file" name="image" class="form-control @error('image') is-invalid @enderror" accept=".jpg,.jpeg,.png,.webp,.svg">
        <div class="form-text">Supported formats: JPG, PNG, WEBP, SVG. Maximum size 2MB.</div>
        @error('image')<div class="invalid-feedback">{{ $message }}</div>@enderror

        @if($supportOption?->image)
            <div class="mt-3 d-flex align-items-center gap-2">
                <img src="{{ str_starts_with($supportOption->image, 'http') ? $supportOption->image : asset('storage/' . $supportOption->image) }}"
                    alt="{{ $supportOption->title }}" class="support-form-preview">
                <span class="text-muted small">Current image</span>
            </div>
        @endif
    </div>

    <div class="col-12">
        <div class="form-check form-switch">
            <input type="hidden" name="is_active" value="0">
            <input class="form-check-input" type="checkbox" name="is_active" value="1" id="is_active"
                @checked(old('is_active', $supportOption->is_active ?? true))>
            <label class="form-check-label" for="is_active">Active</label>
        </div>
    </div>
</div>

@push('my-styles')
<style>
    .support-form-preview {
        width: 56px;
        height: 56px;
        border-radius: 14px;
        object-fit: cover;
        border: 1px solid #e6ebf2;
    }
</style>
@endpush
