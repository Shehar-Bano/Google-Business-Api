@extends('layouts/contentNavbarLayout')

@section('title', 'Subscription Plans')

@section('content')
<div class="admin-page subscription-plans-page">

    <div class="admin-page-header d-flex justify-content-between align-items-center mb-4">
        <div class="admin-page-header__left">
            <h4 class="admin-page-header__title">Subscription Plans</h4>
            <p class="admin-page-header__subtitle">Manage subscription pricing plans, features, and active packages.</p>
        </div>
        <div>
            <a href="{{ route('admin.subscription-plans.create') }}" class="btn btn-primary">
                <i class="mdi mdi-plus me-1"></i> Add Pricing Plan
            </a>
        </div>
    </div>

    @component('admin.components.datatable', [
        'title'     => 'Pricing Plans',
        'subtitle'  => 'Configure tiers, features list, and billing models.',
        'paginator' => $plans,
        'createUrl' => null,
        'search'    => $search,
        'perPage'   => $perPage,
        'columns'   => [
            ['label' => '#',               'field' => 'id',         'sortable' => false],
            ['label' => 'Plan Title',       'field' => 'title',      'sortable' => false],
            ['label' => 'Price',            'sortable' => false],
            ['label' => 'Billing Period',   'sortable' => false],
            ['label' => 'Included Features','sortable' => false],
            ['label' => 'Status',           'sortable' => false],
            ['label' => 'Actions',          'actions' => true],
        ],
    ])
        @forelse($plans as $plan)
            <tr>
                <td class="cell-muted">{{ $plan->id }}</td>
                <td><strong class="cell-primary">{{ $plan->title }}</strong></td>
                <td>
                    <span class="fw-bold text-success">Rs. {{ number_format($plan->price, 2) }}</span>
                </td>
                <td class="cell-primary text-capitalize">{{ $plan->billing_period }}</td>
                <td>
                    @if(is_array($plan->features) && count($plan->features) > 0)
                        <ul class="list-unstyled mb-0 text-start">
                            @foreach($plan->features as $feature)
                                <li class="small text-secondary"><i class="mdi mdi-check-circle-outline text-success me-1"></i>{{ $feature }}</li>
                            @endforeach
                        </ul>
                    @else
                        <span class="text-muted small">No features added.</span>
                    @endif
                </td>
                <td>
                    @if($plan->status === 'active')
                        <span class="badge bg-label-success">Active</span>
                    @else
                        <span class="badge bg-label-danger">Inactive</span>
                    @endif
                </td>
                <td class="text-end">
                    <div class="d-inline-flex align-items-center gap-1">
                        @include('admin.components.action-buttons', [
                            'type'  => 'edit',
                            'href'  => route('admin.subscription-plans.edit', $plan),
                            'title' => 'Edit Plan',
                        ])
                        <form action="{{ route('admin.subscription-plans.destroy', $plan) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this subscription plan?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="action-icon-btn action-icon-delete border-0 bg-transparent" title="Delete Plan">
                                <i class="mdi mdi-trash-can-outline text-danger"></i>
                            </button>
                        </form>
                    </div>
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="7" class="admin-empty-state">No subscription plans found.</td>
            </tr>
        @endforelse
    @endcomponent

</div>
@endsection
