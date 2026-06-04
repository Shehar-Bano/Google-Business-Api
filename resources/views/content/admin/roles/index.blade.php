@extends('layouts/contentNavbarLayout')

@section('title', 'Roles')

@section('content')
@php
    $protectedRoles = ['super_admin', 'club', 'player'];
@endphp

@component('admin.components.datatable', [
    'title' => 'Roles',
    'subtitle' => 'Manage roles and permission boundaries',
    'paginator' => $roles,
    'createUrl' => route('admin.roles.create'),
    'createLabel' => 'Create Role',
    'search' => $search,
    'perPage' => $perPage,
    'sort' => $sort,
    'direction' => $direction,
    'columns' => [
        ['label' => 'Role Name', 'field' => 'name', 'sortable' => true],
        ['label' => 'Permissions Count', 'field' => 'permissions_count', 'sortable' => true],
        ['label' => 'Created At', 'field' => 'created_at', 'sortable' => true],
        ['label' => 'Actions', 'actions' => true],
    ],
])
    @forelse($roles as $role)
        <tr>
            <td class="fw-semibold">{{ $role->name }}</td>
            <td>{{ $role->permissions_count }}</td>
            <td>{{ optional($role->created_at)->format('Y-m-d H:i') ?? '-' }}</td>
            <td class="text-end">
                
                    @include('admin.components.action-buttons', [
                        'type' => 'permission',
                        'href' => route('admin.roles.edit', $role),
                        'title' => 'Assign Permissions',
                    ])
                

                
                    @include('admin.components.action-buttons', [
                        'type' => 'edit',
                        'href' => route('admin.roles.edit', $role),
                    ])
                

                
                    @if(!in_array($role->name, $protectedRoles, true))
                        @include('admin.components.action-buttons', [
                            'type' => 'delete',
                            'formAction' => route('admin.roles.destroy', $role),
                            'confirm' => 'Delete this role?',
                        ])
                    @endif
                
            </td>
        </tr>
    @empty
        <tr>
            <td colspan="4" class="admin-empty-state">No roles found.</td>
        </tr>
    @endforelse
@endcomponent
@endsection
