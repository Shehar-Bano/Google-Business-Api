@extends('layouts/contentNavbarLayout')

@section('title', 'Clubs')

@section('content')
@component('admin.components.datatable', [
    'title'     => 'Clubs',
    'subtitle'  => 'Manage all registered squash clubs',
    'paginator' => $clubs,
    'createUrl' => route('admin.clubs.create'),
    'createLabel' => 'Add Club',
    'search'    => $search,
    'perPage'   => $perPage,
    'sort'      => $sort,
    'direction' => $direction,
    'filters'   => [
        [
            'name'    => 'status',
            'label'   => 'Status',
            'value'   => $status,
            'options' => [
                ''            => 'All Statuses',
                'otp_pending' => 'OTP Pending',
                'pending'     => 'Pending Approval',
                'active'      => 'Active',
                'rejected'    => 'Rejected',
                'suspended'   => 'Suspended',
            ],
        ],
    ],
    'columns' => [
        ['label' => 'Club',            'field' => 'club_name',   'sortable' => true],
        ['label' => 'Owner / Manager', 'field' => 'name',        'sortable' => true],
        ['label' => 'Email',           'field' => 'email',       'sortable' => true],
        ['label' => 'City',            'field' => 'city',        'sortable' => true],
        ['label' => 'Courts',          'sortable' => false],
        ['label' => 'Status',          'sortable' => false],
        ['label' => 'Registered',      'field' => 'created_at',  'sortable' => true],
        ['label' => 'Actions',         'actions' => true],
    ],
])
    @forelse($clubs as $club)
        <tr>
            <td>
                <div class="d-flex align-items-center gap-2">
                    @if($club->club_logo)
                        <img src="{{ asset('storage/' . $club->club_logo) }}" alt="{{ $club->club_name }}"
                             class="rounded" style="width:36px;height:36px;object-fit:cover;">
                    @else
                        <div class="rounded d-flex align-items-center justify-content-center bg-label-primary"
                             style="width:36px;height:36px;flex-shrink:0;">
                            <i class="mdi mdi-domain" style="font-size:18px;"></i>
                        </div>
                    @endif
                    <span class="fw-semibold">{{ $club->club_name ?? '—' }}</span>
                </div>
            </td>
            <td>{{ $club->name }}</td>
            <td>{{ $club->email }}</td>
            <td>{{ $club->city ?? '—' }}</td>
            <td>{{ $club->number_of_courts ?? 0 }}</td>
            <td>
                @php
                    $statusMap = [
                        'active'      => ['bg-label-success',   'Active'],
                        'pending'     => ['bg-label-warning',   'Pending'],
                        'otp_pending' => ['bg-label-secondary', 'OTP Pending'],
                        'rejected'    => ['bg-label-danger',    'Rejected'],
                        'suspended'   => ['bg-label-dark',      'Suspended'],
                    ];
                    [$badgeClass, $badgeLabel] = $statusMap[$club->status] ?? ['bg-label-secondary', ucfirst($club->status)];
                @endphp
                <span class="badge {{ $badgeClass }}">{{ $badgeLabel }}</span>
            </td>
            <td>{{ $club->created_at->format('Y-m-d') }}</td>
            <td class="text-end">
                @include('admin.components.action-buttons', [
                    'type'  => 'view',
                    'href'  => route('admin.clubs.show', $club),
                    'title' => 'View Club',
                ])
                @include('admin.components.action-buttons', [
                    'type'  => 'edit',
                    'href'  => route('admin.clubs.edit', $club),
                    'title' => 'Edit Club',
                ])
                @include('admin.components.action-buttons', [
                    'type'       => 'delete',
                    'formAction' => route('admin.clubs.destroy', $club),
                    'confirm'    => "Delete club \"{$club->club_name}\"? All courts will also be removed.",
                ])
            </td>
        </tr>
    @empty
        <tr>
            <td colspan="8" class="admin-empty-state">No clubs found.</td>
        </tr>
    @endforelse
@endcomponent
@endsection
