@extends('layouts/contentNavbarLayout')

@section('title', 'Permissions')

@section('content')
@component('admin.components.datatable', [
    'title' => 'Permissions',
    'subtitle' => 'Permission inventory grouped by modules and actions',
    'paginator' => $permissions,
    'createUrl' => route('admin.permissions.create'),
    'createLabel' => 'Create Permission',
    'search' => $search,
    'perPage' => $perPage,
    'sort' => $sort,
    'direction' => $direction,
    'columns' => [
        ['label' => 'Permission Name', 'field' => 'name', 'sortable' => true],
        ['label' => 'Guard', 'field' => 'guard_name', 'sortable' => true],
        ['label' => 'Module', 'sortable' => false],
        ['label' => 'Created At', 'field' => 'created_at', 'sortable' => true],
        ['label' => 'Actions', 'actions' => true],
    ],
])
    @forelse($permissions as $permission)
        @php
            $parts = explode('_', $permission->name);
            $module = count($parts) > 1 ? implode(' ', array_slice($parts, 1)) : $permission->name;
        @endphp
        <tr>
            <td class="fw-semibold">{{ $permission->name }}</td>
            <td>{{ $permission->guard_name }}</td>
            <td class="text-capitalize">{{ str_replace('_', ' ', $module) }}</td>
            <td>{{ optional($permission->created_at)->format('Y-m-d H:i') ?? '-' }}</td>
            <td class="text-end">
                
                    @include('admin.components.action-buttons', [
                        'type' => 'edit',
                        'href' => route('admin.permissions.edit', $permission),
                    ])
                

                
                    @include('admin.components.action-buttons', [
                        'type' => 'delete',
                        'formAction' => route('admin.permissions.destroy', $permission),
                        'confirm' => 'Delete this permission?',
                    ])
                
            </td>
        </tr>
    @empty
        <tr>
            <td colspan="5" class="admin-empty-state">No permissions found.</td>
        </tr>
    @endforelse
@endcomponent
@endsection
