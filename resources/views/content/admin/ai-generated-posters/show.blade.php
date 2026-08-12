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
                        <div class="border-bottom py-2 mt-2">
                            <span class="text-muted small d-block">Approved At</span>
                            <strong class="cell-primary">{{ $aiGeneratedPoster->approved_at?->format('Y-m-d H:i') ?? '—' }}</strong>
                        </div>
                    @elseif($aiGeneratedPoster->status === 'rejected')
                        <div class="border-bottom py-2 mt-2">
                            <span class="text-muted small d-block text-danger">Rejection Reason</span>
                            <strong class="text-danger fw-semibold">{{ $aiGeneratedPoster->rejection_reason ?? 'No reason provided.' }}</strong>
                        </div>
                    @endif

                    {{-- Social Accounts & Pages Publishing Status Section --}}
                    @php
                        $pub = $aiGeneratedPoster->latestSocialPublish;
                        $userSocial = $aiGeneratedPoster->user;
                        $connectedFbPage = $userSocial?->socialPages()->whereNotNull('connected_at')->first() ?? $userSocial?->socialPages()->first();
                        $connectedIg = $userSocial?->instagramAccounts()->first();
                    @endphp
                    <div class="py-3 mt-3 p-3 rounded" style="background-color: #f8fafc; border: 1px solid #e2e8f0;">
                        <h6 class="fw-bold mb-3 text-dark d-flex align-items-center justify-content-between">
                            <span><i class="mdi mdi-share-variant me-1 text-primary"></i> Social Accounts & Pages Publishing Status</span>
                            @if($pub)
                                <span class="badge {{ $pub->status === 'posted' ? 'bg-label-success' : 'bg-label-warning' }} text-capitalize">
                                    {{ $pub->status }}
                                </span>
                            @elseif($aiGeneratedPoster->status === 'approved')
                                <span class="badge bg-label-success">Posted</span>
                            @else
                                <span class="badge bg-label-secondary">Pending User Schedule/Post</span>
                            @endif
                        </h6>

                        {{-- Facebook Page Details --}}
                        <div class="d-flex justify-content-between align-items-center py-2 border-bottom">
                            <div>
                                <i class="mdi mdi-facebook text-primary me-1 fs-5"></i>
                                <strong>Facebook Page:</strong>
                                <span class="text-muted ms-1">{{ $connectedFbPage?->page_name ?? ($aiGeneratedPoster->business?->name ?? 'Not Linked') }}</span>
                            </div>
                            <div>
                                @if($pub && $pub->facebook)
                                    <span class="badge bg-label-success"><i class="mdi mdi-check-circle me-1"></i> Posted</span>
                                @elseif($pub && !$pub->facebook)
                                    <span class="badge bg-label-danger"><i class="mdi mdi-close-circle me-1"></i> Failed</span>
                                @elseif($aiGeneratedPoster->status === 'approved')
                                    <span class="badge bg-label-success"><i class="mdi mdi-check-circle me-1"></i> Posted</span>
                                @else
                                    <span class="badge bg-label-secondary">Pending</span>
                                @endif
                            </div>
                        </div>

                        {{-- Instagram Account Details --}}
                        <div class="d-flex justify-content-between align-items-center py-2 border-bottom">
                            <div>
                                <i class="mdi mdi-instagram text-danger me-1 fs-5"></i>
                                <strong>Instagram Account:</strong>
                                <span class="text-muted ms-1">{{ !empty($connectedIg?->username) ? '@' . $connectedIg->username : ($aiGeneratedPoster->business?->name ?? 'Not Linked') }}</span>
                            </div>
                            <div>
                                @if($pub && $pub->instagram)
                                    <span class="badge bg-label-success"><i class="mdi mdi-check-circle me-1"></i> Posted</span>
                                @elseif($pub && !$pub->instagram)
                                    <span class="badge bg-label-danger"><i class="mdi mdi-close-circle me-1"></i> Failed</span>
                                @else
                                    <span class="badge bg-label-secondary">Pending</span>
                                @endif
                            </div>
                        </div>

                        {{-- Google Business Profile Details --}}
                        <div class="d-flex justify-content-between align-items-center py-2 border-bottom">
                            <div>
                                <i class="mdi mdi-google text-info me-1 fs-5"></i>
                                <strong>Google Business:</strong>
                                <span class="text-muted ms-1">{{ $aiGeneratedPoster->business->name ?? 'Default Profile' }}</span>
                            </div>
                            <div>
                                @if($pub && $pub->google)
                                    <span class="badge bg-label-success"><i class="mdi mdi-check-circle me-1"></i> Posted</span>
                                @else
                                    <span class="badge bg-label-secondary">Off</span>
                                @endif
                            </div>
                        </div>

                        @if($pub && $pub->failed_reason)
                            <div class="alert alert-danger py-2 px-3 mb-2 mt-3" style="font-size: 13px;">
                                <strong><i class="mdi mdi-alert-circle me-1"></i> Publishing Failure Details:</strong>
                                <div class="mt-1">{{ $pub->failed_reason }}</div>
                            </div>
                        @endif

                        @if($pub && $pub->published_at)
                            <div class="text-muted small mt-2">
                                <i class="mdi mdi-clock-outline me-1"></i> Published At: <strong>{{ $pub->published_at->format('Y-m-d H:i:s') }}</strong>
                            </div>
                        @elseif($aiGeneratedPoster->published_at)
                            <div class="text-muted small mt-2">
                                <i class="mdi mdi-clock-outline me-1"></i> Published At: <strong>{{ $aiGeneratedPoster->published_at->format('Y-m-d H:i:s') }}</strong>
                            </div>
                        @elseif($aiGeneratedPoster->scheduled_at)
                            <div class="text-info small mt-2">
                                <i class="mdi mdi-calendar-clock me-1"></i> Scheduled For: <strong>{{ $aiGeneratedPoster->scheduled_at->format('Y-m-d H:i:s') }}</strong>
                            </div>
                        @endif
                    </div>


                </div>
            </div>
        </div>
    </div>

</div>
@endsection
