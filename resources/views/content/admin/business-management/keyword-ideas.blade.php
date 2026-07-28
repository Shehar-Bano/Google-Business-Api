@extends('layouts/contentNavbarLayout')

@section('title', 'Google Ads Keyword Ideas')

@section('content')
<div class="admin-page keyword-ideas-page">

    <div class="admin-page-header d-flex justify-content-between align-items-center mb-4">
        <div class="admin-page-header__left">
            <h4 class="admin-page-header__title">Google Ads Keyword Ideas</h4>
            <p class="admin-page-header__subtitle">Viewing stored keyword suggestions and bids analysis for <strong>{{ $business->name }}</strong></p>
        </div>
        <div>
            <a href="{{ route('admin.google-business-connections.index') }}" class="btn btn-outline-secondary btn-sm">
                <i class="mdi mdi-arrow-left me-1"></i> Back to Connections
            </a>
        </div>
    </div>

    <div class="card">
        <div class="card-header pb-2"><h5 class="mb-0 fw-bold">Stored Keyword Suggestions</h5></div>
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Search Query</th>
                        <th>Keyword Idea</th>
                        <th>Avg Searches / mo</th>
                        <th>Competition Density</th>
                        <th>Bids Range (Low - High)</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($business->keywordIdeas as $idea)
                        <tr>
                            <td><span class="badge bg-label-secondary">{{ $idea->search_query }}</span></td>
                            <td><strong class="cell-primary">{{ $idea->keyword }}</strong></td>
                            <td class="fw-semibold text-dark">{{ $idea->avg_monthly_searches ? number_format($idea->avg_monthly_searches) : '—' }}</td>
                            <td>
                                @if($idea->competition === 'HIGH')
                                    <span class="badge bg-label-danger">High</span>
                                @elseif($idea->competition === 'MEDIUM')
                                    <span class="badge bg-label-warning">Medium</span>
                                @elseif($idea->competition === 'LOW')
                                    <span class="badge bg-label-success">Low</span>
                                @else
                                    <span class="badge bg-label-secondary">{{ $idea->competition ?: '—' }}</span>
                                @endif
                            </td>
                            <td>
                                @if($idea->low_range_bid || $idea->high_range_bid)
                                    <span class="text-success fw-semibold">
                                        Rs. {{ number_format($idea->low_range_bid ?? 0.0, 2) }} - Rs. {{ number_format($idea->high_range_bid ?? 0.0, 2) }}
                                    </span>
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center text-muted py-4">No keyword ideas generated or stored for this business.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>
@endsection
