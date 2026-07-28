@extends('layouts/contentNavbarLayout')

@section('title', 'Poster Management')

@section('content')
<div class="admin-page">

    <div class="admin-page-header d-flex justify-content-between align-items-center mb-4">
        <div class="admin-page-header__left">
            <h4 class="admin-page-header__title">Poster Templates</h4>
            <p class="admin-page-header__subtitle">Manage base designs and templates used by users to generate AI marketing posters.</p>
        </div>
        <div>
            <a href="{{ route('admin.posters.create') }}" class="btn btn-primary">
                <i class="mdi mdi-plus me-1"></i> Add Template
            </a>
        </div>
    </div>

    @component('admin.components.datatable', [
        'title'     => 'Poster Templates',
        'subtitle'  => 'Search templates by title.',
        'paginator' => $posters,
        'createUrl' => null,
        'search'    => $search,
        'perPage'   => $perPage,
        'sort'      => $sort,
        'direction' => $direction,
        'columns'   => [
            ['label' => '#',       'field' => 'id',      'sortable' => false],
            ['label' => 'Template Image', 'sortable' => false],
            ['label' => 'Title',   'field' => 'title',   'sortable' => true],
            ['label' => 'Status',  'field' => 'status',  'sortable' => true],
            ['label' => 'Created At', 'field' => 'created_at', 'sortable' => true],
            ['label' => 'Actions', 'actions' => true],
        ],
    ])
        @forelse($posters as $poster)
            <tr>
                <td class="cell-muted">{{ $poster->id }}</td>
                <td>
                    <img src="{{ asset($poster->image) }}" alt="{{ $poster->title }}" class="rounded" style="width: 50px; height: 50px; object-fit: cover; border: 1px solid #e2e8f0;">
                </td>
                <td class="cell-primary fw-semibold">{{ $poster->title }}</td>
                <td>
                    <span class="badge {{ $poster->status === 'Active' ? 'bg-label-success' : 'bg-label-danger' }}">
                        {{ $poster->status }}
                    </span>
                </td>
                <td class="cell-muted">{{ $poster->created_at?->format('Y-m-d') }}</td>
                <td class="text-end">
                    <div class="d-inline-flex align-items-center gap-1">
                        @include('admin.components.action-buttons', [
                            'type'  => 'view',
                            'href'  => route('admin.posters.show', $poster),
                            'title' => 'View Template',
                        ])
                        @include('admin.components.action-buttons', [
                            'type'  => 'edit',
                            'href'  => route('admin.posters.edit', $poster),
                            'title' => 'Edit Template',
                        ])
                        <form action="{{ route('admin.posters.destroy', $poster) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this template?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="action-icon-btn action-icon-delete border-0 bg-transparent" title="Delete Template">
                                <i class="mdi mdi-trash-can-outline text-danger"></i>
                            </button>
                        </form>
                    </div>
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="6" class="admin-empty-state">No poster templates found.</td>
            </tr>
        @endforelse
    @endcomponent

</div>
@endsection
