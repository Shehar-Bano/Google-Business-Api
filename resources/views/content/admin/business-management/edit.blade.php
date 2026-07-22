@extends('layouts/contentNavbarLayout')

@section('title', 'Edit Business')

@section('content')
<div class="admin-page">

    <div class="admin-page-header mb-4">
        <div class="admin-page-header__left">
            <h4 class="admin-page-header__title">Edit Business</h4>
            <p class="admin-page-header__subtitle">Modify business profile information, top selling items, and offerings catalog.</p>
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-header"><h5 class="mb-0">Edit Business Details</h5></div>
        <div class="card-body">
            <form action="{{ route('admin.business-management.update', $business) }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-semibold">Business Name</label>
                        <input type="text" name="name" class="form-control" value="{{ old('name', $business->name) }}" required>
                        @error('name')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-semibold">Business Location</label>
                        <input type="text" name="location" class="form-control" value="{{ old('location', $business->location) }}" required>
                        @error('location')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-semibold">Phone Number</label>
                        <input type="text" name="phone_number" class="form-control" value="{{ old('phone_number', $business->phone_number) }}" placeholder="e.g. +92 300 1234567">
                        @error('phone_number')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-semibold">Address</label>
                        <input type="text" name="address" class="form-control" value="{{ old('address', $business->address) }}" placeholder="e.g. Main Boulevard, Gulberg">
                        @error('address')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-semibold">Category</label>
                        <input type="text" name="category" class="form-control" value="{{ old('category', $business->category) }}" placeholder="e.g. Restaurant, Electronics">
                        @error('category')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-md-8 mb-3">
                        <label class="form-label fw-semibold">Brand Logo</label>
                        <input type="file" name="brand_logo" class="form-control">
                        <small class="text-muted">Only images (jpeg, png, jpg, gif, svg, webp) up to 5MB are allowed. Leave blank to keep current.</small>
                        @error('brand_logo')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-md-4 mb-3">
                        <label class="form-label d-block fw-semibold">Current Logo</label>
                        @if($business->brand_logo)
                            <img src="{{ asset($business->brand_logo) }}" alt="Logo" class="rounded" style="width: 80px; height: 80px; object-fit: cover; border: 1px solid #cbd5e1;">
                        @else
                            <span class="text-muted small">No logo uploaded.</span>
                        @endif
                    </div>

                    <div class="col-md-4 mb-3">
                        <label class="form-label fw-semibold">Rating (0.0 to 5.0)</label>
                        <input type="number" step="0.1" min="0" max="5" name="rating" class="form-control" value="{{ old('rating', $business->rating) }}" placeholder="e.g. 4.5">
                        @error('rating')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-md-4 mb-3">
                        <label class="form-label fw-semibold">Reviews Count</label>
                        <input type="number" min="0" name="reviews" class="form-control" value="{{ old('reviews', $business->reviews) }}" placeholder="e.g. 120">
                        @error('reviews')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-md-4 mb-3 d-flex align-items-center mt-3">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="isVerified" value="1" id="isVerified" @checked(old('isVerified', $business->isVerified))>
                            <label class="form-check-label fw-semibold" for="isVerified">
                                Is Verified Business?
                            </label>
                        </div>
                        @error('isVerified')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-12 mb-3">
                        <label class="form-label fw-semibold">Top Selling Items (comma-separated)</label>
                        <input type="text" name="top_selling_items" class="form-control" value="{{ old('top_selling_items', $topSellingString) }}" required>
                        <small class="text-muted">Enter items separated by a comma. These will be stored as an array in a JSON column.</small>
                        @error('top_selling_items')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                    </div>
                </div>

                <div class="border-top pt-3 mt-3">
                    <h5 class="mb-3">Select Products & Services (Offerings)</h5>
                    
                    <div class="accordion mb-4" id="offeringsAccordion">
                        @foreach($subcategories as $index => $sub)
                            @if($sub->offerings->count() > 0)
                                <div class="accordion-item card mb-2">
                                    <h2 class="accordion-header" id="heading-{{ $sub->id }}">
                                        <button class="accordion-button collapsed py-2 px-3 fw-semibold" type="button" data-bs-toggle="collapse" data-bs-target="#collapse-{{ $sub->id }}" aria-expanded="false" aria-controls="collapse-{{ $sub->id }}">
                                            <i class="mdi mdi-folder-outline me-2 text-primary"></i>
                                            {{ $sub->category->name ?? 'Category' }} — {{ $sub->name }} ({{ $sub->offerings->count() }} Offerings)
                                        </button>
                                    </h2>
                                    <div id="collapse-{{ $sub->id }}" class="accordion-collapse collapse" aria-labelledby="heading-{{ $sub->id }}" data-bs-parent="#offeringsAccordion">
                                        <div class="accordion-body py-3 px-4">
                                            <div class="row">
                                                @foreach($sub->offerings as $offering)
                                                    <div class="col-md-4 col-sm-6 mb-2">
                                                        <div class="form-check">
                                                            <input class="form-check-input" type="checkbox" name="offering_ids[]" value="{{ $offering->id }}" id="offering-{{ $offering->id }}" @checked(in_array($offering->id, $selectedOfferingIds))>
                                                            <label class="form-check-label" for="offering-{{ $offering->id }}">
                                                                {{ $offering->name }} 
                                                                <span class="badge {{ $offering->type === 'product' ? 'bg-label-info' : 'bg-label-warning' }} btn-xs ms-1">{{ $offering->type }}</span>
                                                            </label>
                                                        </div>
                                                    </div>
                                                @endforeach
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endif
                        @endforeach
                    </div>
                </div>

                <div class="mt-4">
                    <button type="submit" class="btn btn-primary">Update Business</button>
                    <a href="{{ route('admin.business-management.index') }}" class="btn btn-outline-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection

@section('page-script')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const topSellingInput = document.querySelector('input[name="top_selling_items"]');
    const checkboxes = document.querySelectorAll('input[name="offering_ids[]"]');

    checkboxes.forEach(checkbox => {
        checkbox.addEventListener('change', function () {
            const label = document.querySelector(`label[for="${this.id}"]`);
            if (!label) return;
            
            // Get only the text node contents ignoring children (badges)
            let text = "";
            for (let i = 0; i < label.childNodes.length; i++) {
                if (label.childNodes[i].nodeType === Node.TEXT_NODE) {
                    text += label.childNodes[i].textContent;
                }
            }
            text = text.trim();

            if (!text) return;

            let currentVal = topSellingInput.value.split(',').map(item => item.trim()).filter(item => item.length > 0);
            
            if (this.checked) {
                if (!currentVal.includes(text)) {
                    currentVal.push(text);
                }
            } else {
                currentVal = currentVal.filter(item => item !== text);
            }
            
            topSellingInput.value = currentVal.join(', ');
        });
    });
});
</script>
@endsection
