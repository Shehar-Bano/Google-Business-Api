@extends('layouts/contentNavbarLayout')

@section('title', 'AI Generated Poster Details')

@section('content')
<div class="admin-page">

    <div class="admin-page-header d-flex justify-content-between align-items-center mb-4">
        <div class="admin-page-header__left">
            <h4 class="admin-page-header__title">AI Poster Details</h4>
            <p class="admin-page-header__subtitle">Viewing metadata and status for generated poster #{{ $aiGeneratedPoster->id }}</p>
        </div>
        <div>
            <a href="{{ route('admin.ai-generated-posters.index') }}" class="btn btn-outline-secondary btn-sm">
                <i class="mdi mdi-arrow-left me-1"></i> Back to Listing
            </a>
        </div>
    </div>

    <div class="row g-4">
        {{-- Generated Design --}}
        <div class="col-md-5">
            <div class="card h-100">
                <div class="card-header border-bottom"><h6 class="mb-0 fw-bold">Generated Output Banner</h6></div>
                <div class="card-body text-center pt-4">
                    @if($aiGeneratedPoster->generated_image)
                        <img src="{{ $aiGeneratedPoster->generated_image }}" alt="Generated Banner" class="img-fluid rounded" style="max-height: 450px; object-fit: contain; border: 1px solid #e2e8f0; width: 100%;">
                    @else
                        <div class="py-5 text-muted">
                            <i class="mdi mdi-image-off mdi-48px mb-2 d-block"></i>
                            No image generated.
                        </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- Details --}}
        <div class="col-md-7">
            <div class="card h-100">
                <div class="card-header border-bottom"><h6 class="mb-0 fw-bold">Metadata & Review Control</h6></div>
                <div class="card-body pt-3">
                    <div class="border-bottom py-2">
                        <span class="text-muted small d-block">Requesting User</span>
                        <strong class="cell-primary">{{ $aiGeneratedPoster->user->name ?? '—' }} ({{ $aiGeneratedPoster->user->email ?? 'N/A' }})</strong>
                    </div>

                    <div class="border-bottom py-2 mt-2">
                        <span class="text-muted small d-block">Business Profile context</span>
                        <strong class="cell-primary">{{ $aiGeneratedPoster->business->name ?? ($aiGeneratedPoster->user->club_name ?? '—') }}</strong>
                    </div>

                    <div class="border-bottom py-2 mt-2">
                        <span class="text-muted small d-block">Template Used</span>
                        <strong class="cell-primary">
                            @if($aiGeneratedPoster->poster)
                                {{ $aiGeneratedPoster->poster->title }} (#{{ $aiGeneratedPoster->poster->id }})
                            @else
                                <span class="badge bg-label-secondary">Direct Prompt Generation</span>
                            @endif
                        </strong>
                    </div>

                    <div class="border-bottom py-2 mt-2">
                        <span class="text-muted small d-block">User AI Prompt</span>
                        <p class="mb-0 cell-primary fw-medium">{{ $aiGeneratedPoster->prompt }}</p>
                    </div>

                    <div class="border-bottom py-2 mt-2">
                        <span class="text-muted small d-block">Generated Title</span>
                        <p class="mb-0 cell-primary fw-bold text-success">{{ $aiGeneratedPoster->generated_title ?? '—' }}</p>
                    </div>

                    <div class="border-bottom py-2 mt-2">
                        <span class="text-muted small d-block">Generated Caption</span>
                        <p class="mb-0 cell-primary" style="white-space: pre-line;">{{ $aiGeneratedPoster->generated_caption ?? '—' }}</p>
                    </div>

                    <div class="border-bottom py-2 mt-2">
                        <span class="text-muted small d-block">Status</span>
                        @php
                            $statusBadge = match($aiGeneratedPoster->status) {
                                'approved' => 'bg-label-success',
                                'rejected' => 'bg-label-danger',
                                default    => 'bg-label-warning',
                            };
                        @endphp
                        <span class="badge {{ $statusBadge }} text-capitalize mt-1">
                            {{ $aiGeneratedPoster->status }}
                        </span>
                    </div>

                    @if($aiGeneratedPoster->status === 'approved')
                        <div class="border-bottom py-2 mt-2">
                            <span class="text-muted small d-block">Approved By</span>
                            <strong class="cell-primary">{{ $aiGeneratedPoster->approver->name ?? 'Admin' }}</strong>
                        </div>
                        <div class="py-2 mt-2">
                            <span class="text-muted small d-block">Approved At</span>
                            <strong class="cell-primary">{{ $aiGeneratedPoster->approved_at?->format('Y-m-d H:i') ?? '—' }}</strong>
                        </div>
                    @elseif($aiGeneratedPoster->status === 'rejected')
                        <div class="py-2 mt-2">
                            <span class="text-muted small d-block text-danger">Rejection Reason</span>
                            <strong class="text-danger fw-semibold">{{ $aiGeneratedPoster->rejection_reason ?? 'No reason provided.' }}</strong>
                        </div>
                    @endif


                </div>
            </div>
        </div>
    </div>

</div>
@endsection
