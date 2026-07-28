@extends('layouts/contentNavbarLayout')

@section('title', 'Google Business Integration')

@section('content')
<div class="admin-page google-business-connections-page">

    <div class="admin-page-header d-flex justify-content-between align-items-center mb-4">
        <div class="admin-page-header__left">
            <h4 class="admin-page-header__title">Google Business Integration</h4>
            <p class="admin-page-header__subtitle">Monitor active Google Business account connections, API status, and place integrations.</p>
        </div>
    </div>

    @component('admin.components.datatable', [
        'title'     => 'Google Business Account Connections',
        'subtitle'  => 'Review place registrations, connected social channels, and keyword ideas availability.',
        'paginator' => $connections,
        'createUrl' => null,
        'search'    => $search,
        'perPage'   => $perPage,
        'sort'      => $sort,
        'direction' => $direction,
        'columns'   => [
            ['label' => '#',               'field' => 'id',         'sortable' => false],
            ['label' => 'Business Profile', 'field' => 'name',       'sortable' => true],
            ['label' => 'Google Place ID',  'field' => 'google_place_id', 'sortable' => true],
            ['label' => 'Social Connections','sortable' => false],
            ['label' => 'Verification',     'field' => 'isVerified', 'sortable' => true],
            ['label' => 'Stored Keywords',  'sortable' => false],
            ['label' => 'Actions',          'actions' => true],
        ],
    ])
        @forelse($connections as $connection)
            <tr>
                <td class="cell-muted">{{ $connection->id }}</td>
                <td>
                    <div class="d-flex align-items-center gap-2">
                        @if($connection->brand_logo)
                            <img src="{{ asset($connection->brand_logo) }}" alt="Logo" class="rounded" style="width: 32px; height: 32px; object-fit: cover;">
                        @else
                            <div class="um-user-avatar bg-label-primary" style="width: 32px; height: 32px; font-size: 0.8rem;">
                                {{ strtoupper(substr($connection->name, 0, 1)) }}
                            </div>
                        @endif
                        <div>
                            <div class="cell-primary fw-semibold">{{ $connection->name }}</div>
                            <small class="text-muted">{{ $connection->location }}</small>
                        </div>
                    </div>
                </td>
                <td class="font-monospace text-xs text-secondary">
                    {{ $connection->google_place_id ?: '— Not Linked —' }}
                </td>
                <td>
                    @if($connection->user && $connection->user->socialAccounts->count() > 0)
                        <div class="d-flex flex-wrap gap-1">
                            @foreach($connection->user->socialAccounts as $account)
                                @php
                                    $badgeClass = 'bg-label-secondary';
                                    if (strtolower($account->provider) === 'google') $badgeClass = 'bg-label-danger';
                                    elseif (strtolower($account->provider) === 'facebook') $badgeClass = 'bg-label-primary';
                                    elseif (strtolower($account->provider) === 'instagram') $badgeClass = 'bg-label-warning';
                                @endphp
                                <span class="badge {{ $badgeClass }} text-capitalize d-inline-flex align-items-center gap-1">
                                    <i class="mdi mdi-link-variant"></i> {{ $account->provider }}
                                </span>
                            @endforeach
                        </div>
                    @else
                        <span class="badge bg-label-secondary d-inline-flex align-items-center gap-1">
                            <i class="mdi mdi-alert-circle-outline"></i> Disconnected
                        </span>
                    @endif
                </td>
                <td>
                    @if($connection->isVerified)
                        <span class="badge bg-label-primary">Verified</span>
                    @else
                        <span class="badge bg-label-warning">Unverified</span>
                    @endif
                </td>
                <td>
                    <span class="badge bg-label-info fw-bold">
                        {{ $connection->keywordIdeas->count() }} Keywords
                    </span>
                </td>
                <td class="text-end">
                    <div class="d-inline-flex align-items-center gap-1">
                        <a href="{{ route('admin.business-management.keyword-ideas', $connection) }}" class="btn btn-outline-primary btn-xs d-inline-flex align-items-center gap-1" title="View Keywords">
                            <i class="mdi mdi-google-ads"></i> View Keywords
                        </a>
                    </div>
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="7" class="admin-empty-state">No business connections found.</td>
            </tr>
        @endforelse
    @endcomponent

</div>
@endsection

@push('my-styles')
<style>
    .font-monospace {
        font-family: SFMono-Regular, Menlo, Monaco, Consolas, monospace;
    }
</style>
@endpush
