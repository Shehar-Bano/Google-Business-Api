@extends('layouts/contentNavbarLayout')

@section('title', 'Business Management')

@section('content')
    <div class="admin-page business-management-page">

        <div class="admin-page-header d-flex justify-content-between align-items-center mb-4">
            <div class="admin-page-header__left">
                <h4 class="admin-page-header__title">Business Management</h4>
                <p class="admin-page-header__subtitle">View and manage registered businesses, catalog items, and top selling
                    products.</p>
            </div>
        </div>

        {{-- Stats Grid --}}
        <div class="um-stats-grid mb-4">
            <div class="um-stat-card">
                <div class="um-stat-card__icon um-stat-card__icon--primary"><i class="mdi mdi-briefcase"></i></div>
                <div>
                    <div class="um-stat-card__label">Total Businesses</div>
                    <div class="um-stat-card__value">{{ number_format($stats['total']) }}</div>
                </div>
            </div>
            {{-- <div class="um-stat-card">
            <div class="um-stat-card__icon um-stat-card__icon--success"><i class="mdi mdi-check-decagram"></i></div>
            <div>
                <div class="um-stat-card__label">With Offerings</div>
                <div class="um-stat-card__value">{{ number_format($stats['with_offerings']) }}</div>
            </div>
        </div> --}}
            <div class="um-stat-card">
                <div class="um-stat-card__icon um-stat-card__icon--warning"><i class="mdi mdi-map-marker-radius"></i></div>
                <div>
                    <div class="um-stat-card__label">Unique Locations</div>
                    <div class="um-stat-card__value">{{ number_format($stats['locations_count']) }}</div>
                </div>
            </div>
        </div>

        @component('admin.components.datatable', [
            'title' => 'Businesses',
            'subtitle' => 'Search by name or location.',
            'paginator' => $businesses,
            'createUrl' => null,
            'search' => $search,
            'perPage' => $perPage,
            'sort' => $sort,
            'direction' => $direction,
            'columns' => [
                ['label' => '#', 'field' => 'id', 'sortable' => false],
                ['label' => 'Business Name', 'field' => 'name', 'sortable' => true],
                ['label' => 'Location', 'field' => 'location', 'sortable' => true],
                ['label' => 'Status', 'field' => 'status', 'sortable' => true],
                ['label' => 'Created At', 'field' => 'created_at', 'sortable' => true],
                ['label' => 'Actions', 'actions' => true],
            ],
        ])
            @forelse($businesses as $business)
                <tr>
                    <td class="cell-muted">{{ $business->id }}</td>
                    <td>
                        <div class="d-flex align-items-center gap-2">
                            <div class="um-user-avatar bg-label-primary">
                                {{ strtoupper(substr($business->name, 0, 1)) }}
                            </div>
                            <div>
                                <div class="cell-primary fw-semibold">{{ $business->name }}</div>
                            </div>
                        </div>
                    </td>
                    <td class="cell-primary">{{ $business->location }}</td>
                    {{-- <td>
                        @forelse($business->topSellingItems as $item)
                            <span class="badge bg-label-info mb-1">{{ $item->item_name }}</span>
                        @empty
                            <span class="text-muted">—</span>
                        @endforelse
                    </td> --}}
                    <td>
                        @if (($business->status ?? 'approved') === 'suspended')
                            <span class="badge bg-label-danger">Suspended</span>
                        @else
                            <span class="badge bg-label-success">Approved</span>
                        @endif
                    </td>
                    <td class="cell-muted">{{ $business->created_at?->format('Y-m-d') }}</td>
                    <td class="text-end">
                        <div class="d-inline-flex align-items-center gap-1">
                            @include('admin.components.action-buttons', [
                                'type' => 'view',
                                'href' => route('admin.business-management.show', $business),
                                'title' => 'View Business Details',
                            ])
                            @include('admin.components.action-buttons', [
                                'type' => 'edit',
                                'href' => route('admin.business-management.edit', $business),
                                'title' => 'Edit Business Status',
                            ])
                            <form action="{{ route('admin.business-management.destroy', $business) }}" method="POST"
                                class="d-inline" onsubmit="return confirm('Are you sure you want to delete this business?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="action-icon-btn action-icon-delete border-0 bg-transparent"
                                    title="Delete Business">
                                    <i class="mdi mdi-trash-can-outline text-danger"></i>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="admin-empty-state">No businesses found.</td>
                </tr>
            @endforelse
        @endcomponent

    </div>
@endsection

@push('my-styles')
    <style>
        .um-stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
            gap: 1rem;
        }

        .um-stat-card {
            background: linear-gradient(180deg, #ffffff 0%, #f9fbfc 100%);
            border: 1px solid #e6ebf2;
            border-radius: 18px;
            padding: 1rem 1.1rem;
            display: flex;
            align-items: center;
            gap: 0.9rem;
            box-shadow: 0 10px 24px rgba(15, 23, 42, 0.05);
        }

        .um-stat-card__icon {
            width: 48px;
            height: 48px;
            border-radius: 14px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            flex-shrink: 0;
        }

        .um-stat-card__icon--primary {
            background: #eef2ff;
            color: #4f46e5;
        }

        .um-stat-card__icon--success {
            background: #e8f5e9;
            color: #2e7d32;
        }

        .um-stat-card__icon--warning {
            background: #fff3e0;
            color: #ef6c00;
        }

        .um-stat-card__label {
            font-size: 0.72rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.06em;
            color: #64748b;
            margin-bottom: 0.2rem;
        }

        .um-stat-card__value {
            font-size: 1.5rem;
            font-weight: 800;
            color: #0f172a;
            line-height: 1.1;
        }

        .um-user-avatar {
            width: 38px;
            height: 38px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            font-size: 0.9rem;
            flex-shrink: 0;
        }

        .action-icon-btn {
            padding: 0.35rem;
            border-radius: 8px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            transition: all 0.2s;
        }

        .action-icon-btn:hover {
            background: #f1f5f9;
        }

        @media (max-width: 767px) {
            .um-stats-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
@endpush
