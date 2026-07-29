@extends('layouts/contentNavbarLayout')

@section('title', 'WhatsApp Review Requests')

@section('content')
<div class="admin-page whatsapp-review-requests-page">

    <div class="admin-page-header d-flex justify-content-between align-items-center mb-4">
        <div class="admin-page-header__left">
            <h4 class="admin-page-header__title">WhatsApp Review Requests</h4>
            <p class="admin-page-header__subtitle">Monitor Google Business review invitation messages sent to customers via WhatsApp.</p>
        </div>
    </div>

    @component('admin.components.datatable', [
        'title'     => 'WhatsApp & SMS Review Invites',
        'subtitle'  => 'Track sending status, user views, and review completions.',
        'paginator' => $requests,
        'createUrl' => null,
        'search'    => $search,
        'perPage'   => $perPage,
        'sort'      => $sort,
        'direction' => $direction,
        'columns'   => [
            ['label' => '#',               'field' => 'id',           'sortable' => true],
            ['label' => 'Business Profile', 'field' => 'business',     'sortable' => true],
            ['label' => 'Sender Admin/User','field' => 'sender',       'sortable' => true],
            ['label' => 'Sent To Customer', 'field' => 'sent_to_user', 'sortable' => true],
            ['label' => 'Phone Number',    'field' => 'phone_number', 'sortable' => true],
            ['label' => 'Channel',         'field' => 'channel',      'sortable' => true],
            ['label' => 'Status',          'field' => 'status',       'sortable' => true],
            ['label' => 'Sent At',         'field' => 'created_at',   'sortable' => true],
        ],
    ])
        @forelse($requests as $req)
            @php
                $statusBadge = match($req->status) {
                    'sent'     => 'bg-label-info',
                    'clicked'  => 'bg-label-warning',
                    'reviewed' => 'bg-label-success',
                    'failed'   => 'bg-label-danger',
                    default    => 'bg-label-secondary',
                };
            @endphp
            <tr>
                <td class="cell-muted">{{ $req->id }}</td>
                <td>
                    @if($req->business)
                        <div class="cell-primary fw-semibold">{{ $req->business->name }}</div>
                        <small class="text-muted">{{ $req->business->location }}</small>
                    @else
                        <span class="text-muted">— Business Deleted —</span>
                    @endif
                </td>
                <td>
                    @if($req->sender)
                        <div class="cell-primary fw-medium">{{ $req->sender->name }}</div>
                        <small class="text-muted">{{ $req->sender->email }}</small>
                    @else
                        <span class="text-muted">— System Auto —</span>
                    @endif
                </td>
                <td>
                    @if($req->sentToUser)
                        <div class="cell-primary fw-medium">{{ $req->sentToUser->name }}</div>
                    @else
                        <span class="text-secondary fw-semibold">— Guest Customer —</span>
                    @endif
                </td>
                <td class="font-monospace text-xs text-dark">{{ $req->phone_number }}</td>
                <td>
                    <span class="badge bg-label-secondary text-uppercase d-inline-flex align-items-center gap-1">
                        @if($req->channel === 'whatsapp' || str_contains($req->channel, 'whatsapp'))
                            <i class="mdi mdi-whatsapp text-success"></i> WhatsApp
                        @else
                            <i class="mdi mdi-cellphone-text text-primary"></i> SMS / Other
                        @endif
                    </span>
                </td>
                <td>
                    <div class="d-inline-block">
                        <span class="badge {{ $statusBadge }} text-capitalize d-block mb-1">{{ $req->status }}</span>
                        @if($req->status === 'failed' && $req->failure_reason)
                            <small class="text-danger d-block" style="max-width: 150px; font-size: 0.7rem; white-space: normal;" title="{{ $req->failure_reason }}">
                                {{ $req->failure_reason }}
                            </small>
                        @endif
                    </div>
                </td>
                <td class="cell-muted">{{ $req->created_at?->format('Y-m-d H:i') ?? '—' }}</td>
            </tr>
        @empty
            <tr>
                <td colspan="8" class="admin-empty-state">No WhatsApp review requests found.</td>
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
