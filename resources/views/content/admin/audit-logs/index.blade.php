@extends('layouts/contentNavbarLayout')

@section('title', 'Admin Audit Logs')

@section('content')
<div class="admin-page audit-logs-page">

    <div class="admin-page-header d-flex justify-content-between align-items-center mb-4">
        <div class="admin-page-header__left">
            <h4 class="admin-page-header__title">Admin Audit Logs</h4>
            <p class="admin-page-header__subtitle">Track all administrative actions, logins, updates, and permission changes.</p>
        </div>
    </div>

    @component('admin.components.datatable', [
        'title'     => 'Audit History',
        'subtitle'  => 'Search by action name, user, or description text.',
        'paginator' => $logs,
        'createUrl' => null,
        'search'    => $search,
        'perPage'   => $perPage,
        'sort'      => $sort,
        'direction' => $direction,
        'columns'   => [
            ['label' => '#',          'field' => 'id',         'sortable' => true],
            ['label' => 'Admin User', 'field' => 'user',       'sortable' => true],
            ['label' => 'Action Type','field' => 'action',     'sortable' => true],
            ['label' => 'Target',     'field' => 'target_type', 'sortable' => true],
            ['label' => 'Description','field' => 'description', 'sortable' => true],
            ['label' => 'IP Address', 'field' => 'ip_address', 'sortable' => true],
            ['label' => 'Timestamp',  'field' => 'created_at', 'sortable' => true],
        ],
    ])
        @forelse($logs as $log)
            <tr>
                <td class="cell-muted">{{ $log->id }}</td>
                <td>
                    @if($log->user)
                        <div class="d-flex align-items-center gap-2">
                            <div class="um-user-avatar bg-label-primary" style="width: 30px; height: 30px; font-size: 0.85rem;">
                                {{ strtoupper(substr($log->user->name, 0, 1)) }}
                            </div>
                            <div>
                                <div class="cell-primary fw-semibold">{{ $log->user->name }}</div>
                                <small class="text-muted">{{ $log->user->email }}</small>
                            </div>
                        </div>
                    @else
                        <span class="text-muted">— System / Deleted User —</span>
                    @endif
                </td>
                <td>
                    <span class="badge bg-label-info text-capitalize">{{ str_replace('_', ' ', $log->action) }}</span>
                </td>
                <td>
                    @if($log->target_type)
                        <span class="text-secondary fw-semibold">{{ $log->target_type }}</span>
                        @if($log->target_id)
                            <small class="text-muted">(ID: {{ $log->target_id }})</small>
                        @endif
                    @else
                        <span class="text-muted">—</span>
                    @endif
                </td>
                <td style="max-width: 320px; white-space: normal;" class="cell-primary">
                    {{ $log->description }}
                </td>
                <td class="cell-muted font-monospace">{{ $log->ip_address }}</td>
                <td class="cell-muted">{{ $log->created_at?->format('Y-m-d H:i:s') }}</td>
            </tr>
        @empty
            <tr>
                <td colspan="7" class="admin-empty-state">No audit logs found.</td>
            </tr>
        @endforelse
    @endcomponent

</div>
@endsection

@push('my-styles')
<style>
    .font-monospace {
        font-family: SFMono-Regular, Menlo, Monaco, Consolas, "Liberation Mono", "Courier New", monospace;
    }
</style>
@endpush
