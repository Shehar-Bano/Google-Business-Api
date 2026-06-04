@extends('layouts/contentNavbarLayout')

@section('title', 'Users')

@section('content')
@component('admin.components.datatable', [
    'title' => 'Users',
    'subtitle' => 'Role assignment and user access control',
    'paginator' => $users,
    'createUrl' => null,
    'search' => $search,
    'perPage' => $perPage,
    'sort' => $sort,
    'direction' => $direction,
    'columns' => [
        ['label' => 'Name', 'field' => 'name', 'sortable' => true],
        ['label' => 'Email', 'field' => 'email', 'sortable' => true],
        ['label' => 'Roles', 'sortable' => false],
        ['label' => 'Status', 'sortable' => false],
        ['label' => 'Joined Date', 'field' => 'created_at', 'sortable' => true],
        ['label' => 'Actions', 'actions' => true],
    ],
])
    @forelse($users as $user)
        <tr>
            <td class="fw-semibold">{{ $user->name }}</td>
            <td>{{ $user->email }}</td>
            <td>{{ $user->roles->pluck('name')->join(', ') ?: 'No role assigned' }}</td>
            <td>
                @if($user->email_verified_at)
                    <span class="badge bg-label-success">Active</span>
                @else
                    <span class="badge bg-label-warning">Pending</span>
                @endif
            </td>
            <td>{{ optional($user->created_at)->format('Y-m-d') ?? '-' }}</td>
            <td class="text-end">
                
                    @include('admin.components.action-buttons', [
                        'type' => 'view',
                        'href' => route('admin.users.roles.edit', $user),
                        'title' => 'View User',
                    ])
                

                
                    @include('admin.components.action-buttons', [
                        'type' => 'permission',
                        'href' => route('admin.users.roles.edit', $user),
                        'title' => 'Assign Roles',
                    ])
                
            </td>
        </tr>
    @empty
        <tr>
            <td colspan="6" class="admin-empty-state">No users found.</td>
        </tr>
    @endforelse
@endcomponent
@endsection
