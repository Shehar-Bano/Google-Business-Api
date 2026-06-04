@extends('layouts/contentNavbarLayout')

@section('title', 'Players')

@section('content')
@component('admin.components.datatable', [
    'title'       => 'Players',
    'subtitle'    => 'Manage all registered players',
    'paginator'   => $players,
    'createUrl'   => route('admin.players.create'),
    'createLabel' => 'Add Player',
    'search'      => $search,
    'perPage'     => $perPage,
    'sort'        => $sort,
    'direction'   => $direction,
    'filters'     => [
        [
            'name'    => 'status',
            'label'   => 'Status',
            'value'   => $status,
            'options' => [
                ''                   => 'All Statuses',
                'otp_pending'        => 'OTP Pending',
                'profile_incomplete' => 'Profile Incomplete',
                'active'             => 'Active',
                'suspended'          => 'Suspended',
            ],
        ],
        [
            'name'    => 'playing_level',
            'label'   => 'Level',
            'value'   => $playingLevel,
            'options' => [
                ''             => 'All Levels',
                'beginner'     => 'Beginner',
                'intermediate' => 'Intermediate',
                'advanced'     => 'Advanced',
                'professional' => 'Professional',
            ],
        ],
    ],
    'columns' => [
        ['label' => 'Player',        'field' => 'name',         'sortable' => true],
        ['label' => 'Email',         'field' => 'email',        'sortable' => true],
        ['label' => 'City',          'field' => 'city',         'sortable' => true],
        ['label' => 'Level',         'field' => 'playing_level','sortable' => true],
        ['label' => 'Hand',          'sortable' => false],
        ['label' => 'Status',        'sortable' => false],
        ['label' => 'Registered',    'field' => 'created_at',   'sortable' => true],
        ['label' => 'Actions',       'actions' => true],
    ],
])
    @forelse($players as $player)
        <tr>
            <td>
                <div class="d-flex align-items-center gap-2">
                    @if($player->profile_image)
                        <img src="{{ asset('storage/' . $player->profile_image) }}" alt="{{ $player->name }}"
                             class="rounded-circle" style="width:36px;height:36px;object-fit:cover;">
                    @else
                        <div class="rounded-circle d-flex align-items-center justify-content-center bg-label-success"
                             style="width:36px;height:36px;flex-shrink:0;">
                            <i class="mdi mdi-account" style="font-size:18px;"></i>
                        </div>
                    @endif
                    <span class="fw-semibold">{{ $player->name }}</span>
                </div>
            </td>
            <td>{{ $player->email }}</td>
            <td>{{ $player->city ?? '—' }}</td>
            <td>{{ $player->playing_level ? ucfirst($player->playing_level) : '—' }}</td>
            <td>{{ $player->primary_hand ? ucfirst($player->primary_hand) : '—' }}</td>
            <td>
                @php
                    $statusMap = [
                        'active'             => ['bg-label-success',   'Active'],
                        'profile_incomplete' => ['bg-label-warning',   'Incomplete'],
                        'otp_pending'        => ['bg-label-secondary', 'OTP Pending'],
                        'suspended'          => ['bg-label-dark',      'Suspended'],
                    ];
                    [$badgeClass, $badgeLabel] = $statusMap[$player->status] ?? ['bg-label-secondary', ucfirst($player->status)];
                @endphp
                <span class="badge {{ $badgeClass }}">{{ $badgeLabel }}</span>
            </td>
            <td>{{ $player->created_at->format('Y-m-d') }}</td>
            <td class="text-end">
                @include('admin.components.action-buttons', [
                    'type'  => 'view',
                    'href'  => route('admin.players.show', $player),
                    'title' => 'View Player',
                ])
                @include('admin.components.action-buttons', [
                    'type'  => 'edit',
                    'href'  => route('admin.players.edit', $player),
                    'title' => 'Edit Player',
                ])
                @include('admin.components.action-buttons', [
                    'type'       => 'delete',
                    'formAction' => route('admin.players.destroy', $player),
                    'confirm'    => "Delete player \"{$player->name}\"? This cannot be undone.",
                ])
            </td>
        </tr>
    @empty
        <tr>
            <td colspan="8" class="admin-empty-state">No players found.</td>
        </tr>
    @endforelse
@endcomponent
@endsection
