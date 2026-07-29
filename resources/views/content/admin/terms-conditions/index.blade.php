@extends('layouts/contentNavbarLayout')

@section('title', 'Terms & Conditions')

@section('content')
<div class="admin-page terms-conditions-page">

    <div class="admin-page-header d-flex justify-content-between align-items-center mb-4">
        <div class="admin-page-header__left">
            <h4 class="admin-page-header__title">Terms & Conditions</h4>
            <p class="admin-page-header__subtitle">Manage terms, user agreements, and disclaimer pages served to client applications.</p>
        </div>
        <div>
            <a href="{{ route('admin.terms-conditions.create') }}" class="btn btn-primary">
                <i class="mdi mdi-plus me-1"></i> Add Terms Page
            </a>
        </div>
    </div>

    @component('admin.components.datatable', [
        'title'     => 'Terms and Agreements',
        'subtitle'  => 'Configure policies and standard user terms rules.',
        'paginator' => $terms,
        'createUrl' => null,
        'search'    => $search,
        'perPage'   => $perPage,
        'sort'      => $sort,
        'direction' => $direction,
        'columns'   => [
            ['label' => '#',          'field' => 'id',         'sortable' => true],
            ['label' => 'Page Title', 'field' => 'title',      'sortable' => true],
            ['label' => 'URL Slug',   'field' => 'slug',       'sortable' => true],
            ['label' => 'Status',     'field' => 'status',     'sortable' => true],
            ['label' => 'Created At', 'field' => 'created_at', 'sortable' => true],
            ['label' => 'Actions',    'actions' => true],
        ],
    ])
        @forelse($terms as $term)
            <tr>
                <td class="cell-muted">{{ $term->id }}</td>
                <td><strong class="cell-primary">{{ $term->title }}</strong></td>
                <td class="font-monospace text-xs text-secondary">{{ $term->slug }}</td>
                <td>
                    @if($term->status === 'Active')
                        <span class="badge bg-label-success">Active</span>
                    @else
                        <span class="badge bg-label-danger">Inactive</span>
                    @endif
                </td>
                <td class="cell-muted">{{ $term->created_at?->format('Y-m-d H:i') ?? '—' }}</td>
                <td class="text-end">
                    <div class="d-inline-flex align-items-center gap-1">
                        @include('admin.components.action-buttons', [
                            'type'  => 'edit',
                            'href'  => route('admin.terms-conditions.edit', $term),
                            'title' => 'Edit Terms',
                        ])
                        <form action="{{ route('admin.terms-conditions.destroy', $term) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this Terms & Conditions page?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="action-icon-btn action-icon-delete border-0 bg-transparent" title="Delete Terms">
                                <i class="mdi mdi-trash-can-outline text-danger"></i>
                            </button>
                        </form>
                    </div>
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="6" class="admin-empty-state">No Terms & Conditions found.</td>
            </tr>
        @endforelse
    @endcomponent

</div>
@endsection
