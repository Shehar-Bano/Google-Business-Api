@extends('layouts/contentNavbarLayout')

@section('title', 'Business Preferences Details')

@section('content')
<div class="admin-page">

    <div class="admin-page-header d-flex justify-content-between align-items-center mb-4">
        <div class="admin-page-header__left">
            <h4 class="admin-page-header__title">Business Preferences</h4>
            <p class="admin-page-header__subtitle">Viewing submitted marketing and branding preferences for <strong>{{ $business->name }}</strong></p>
        </div>
        <div>
            <a href="{{ route('admin.business-management.index') }}" class="btn btn-outline-secondary btn-sm">
                <i class="mdi mdi-arrow-left me-1"></i> Back to Businesses
            </a>
        </div>
    </div>

    @if(!$business->preferences)
        <div class="card p-5 text-center">
            <div class="text-muted"><i class="mdi mdi-information-outline display-4 mb-2"></i></div>
            <h5>No Preferences Added Yet</h5>
            <p class="text-muted">This business has not submitted any marketing preferences or photos.</p>
        </div>
    @else
        <div class="row g-4">
            {{-- Text/Textarea Preferences --}}
            <div class="col-lg-8">
                <div class="card mb-4">
                    <div class="card-header pb-2"><h5 class="mb-0 fw-bold">Branding & Competitiveness</h5></div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6 mb-3 border-bottom pb-2">
                                <span class="text-muted small d-block">Business Tagline</span>
                                <span class="fw-semibold">{{ $business->preferences->business_tagline ?: '—' }}</span>
                            </div>
                            <div class="col-md-6 mb-3 border-bottom pb-2">
                                <span class="text-muted small d-block">Call To Action (CTA)</span>
                                <span class="fw-semibold text-primary">{{ $business->preferences->cta ?: '—' }}</span>
                            </div>
                            <div class="col-12 mb-3 border-bottom pb-2">
                                <span class="text-muted small d-block">Business Description</span>
                                <p class="mb-0 text-dark">{{ $business->preferences->business_description ?: '—' }}</p>
                            </div>
                            <div class="col-12 mb-3 border-bottom pb-2">
                                <span class="text-muted small d-block">What makes you different than competition?</span>
                                <p class="mb-0 text-dark">{{ $business->preferences->different_than_competition ?: '—' }}</p>
                            </div>
                            <div class="col-12 mb-3">
                                <span class="text-muted small d-block">Why should customers visit us?</span>
                                <p class="mb-0 text-dark">{{ $business->preferences->why_visit_us ?: '—' }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card mb-4">
                    <div class="card-header pb-2"><h5 class="mb-0 fw-bold">Industry Analysis & Guidelines</h5></div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6 mb-3 border-bottom pb-2">
                                <span class="text-muted small d-block">Low Standards of Industry</span>
                                <p class="mb-0 text-dark">{{ $business->preferences->low_standards_of_industry ?: '—' }}</p>
                            </div>
                            <div class="col-md-6 mb-3 border-bottom pb-2">
                                <span class="text-muted small d-block">Our Solutions for Low Standards</span>
                                <p class="mb-0 text-dark">{{ $business->preferences->solutions_for_low_standards ?: '—' }}</p>
                            </div>
                            <div class="col-md-6 mb-3 border-bottom pb-2">
                                <span class="text-muted small d-block">Common Malpractices in Industry</span>
                                <p class="mb-0 text-dark">{{ $business->preferences->malpractices_in_industry ?: '—' }}</p>
                            </div>
                            <div class="col-md-6 mb-3 border-bottom pb-2">
                                <span class="text-muted small d-block">Our Solutions for Malpractices</span>
                                <p class="mb-0 text-dark">{{ $business->preferences->solutions_for_malpractices ?: '—' }}</p>
                            </div>
                            <div class="col-md-6 mb-3">
                                <span class="text-muted small d-block">Common Mistakes by Customers</span>
                                <p class="mb-0 text-dark">{{ $business->preferences->common_mistakes_by_customers ?: '—' }}</p>
                            </div>
                            <div class="col-md-6 mb-3">
                                <span class="text-muted small d-block">Our Guidelines to Customers</span>
                                <p class="mb-0 text-dark">{{ $business->preferences->guidelines_to_customer ?: '—' }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Target Demographic & Photos --}}
            <div class="col-lg-4">
                <div class="card mb-4">
                    <div class="card-header pb-2"><h5 class="mb-0 fw-bold">Target Demographic</h5></div>
                    <div class="card-body">
                        <div class="border-bottom py-2">
                            <span class="text-muted small d-block">Target Gender</span>
                            <strong class="text-dark">{{ $business->preferences->target_gender ?: '—' }}</strong>
                        </div>
                        <div class="border-bottom py-2 mt-2">
                            <span class="text-muted small d-block">Target Age Group</span>
                            <strong class="text-dark">{{ $business->preferences->target_age_group ?: '—' }}</strong>
                        </div>
                        <div class="border-bottom py-2 mt-2">
                            <span class="text-muted small d-block">Target Region / Location</span>
                            <strong class="text-dark">{{ $business->preferences->region ?: '—' }}</strong>
                        </div>
                        <div class="border-bottom py-2 mt-2">
                            <span class="text-muted small d-block">Model Ethnicity</span>
                            <strong class="text-dark">{{ $business->preferences->model_ethnicity ?: '—' }}</strong>
                        </div>
                        <div class="border-bottom py-2 mt-2">
                            <span class="text-muted small d-block">Audience Focus</span>
                            <strong class="text-dark">{{ $business->preferences->audience ?: '—' }}</strong>
                        </div>
                        <div class="py-2 mt-2">
                            <span class="text-muted small d-block">Nearest Landmark</span>
                            <strong class="text-dark">{{ $business->preferences->nearest_landmark ?: '—' }}</strong>
                        </div>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header pb-2"><h5 class="mb-0 fw-bold">Preferences Gallery</h5></div>
                    <div class="card-body">
                        <div class="row g-2">
                            @forelse($business->preferences->images as $image)
                                <div class="col-6 mb-2">
                                    <div class="position-relative border rounded p-1 text-center bg-light">
                                        <a href="{{ asset($image->image) }}" target="_blank">
                                            <img src="{{ asset($image->image) }}" alt="Preference image" class="rounded img-fluid" style="height: 110px; object-fit: cover; width: 100%;">
                                        </a>
                                        <div class="mt-1 small fw-semibold text-truncate">{{ $image->label ?: 'Untitled' }}</div>
                                        <span class="badge bg-label-secondary text-xs text-capitalize">{{ str_replace('_', ' ', $image->type) }}</span>
                                    </div>
                                </div>
                            @empty
                                <div class="col-12 py-3 text-center text-muted small">No photos uploaded.</div>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif

</div>
@endsection
