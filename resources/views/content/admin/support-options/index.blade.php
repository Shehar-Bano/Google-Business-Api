@extends('layouts/contentNavbarLayout')

@section('title', 'Help & Support')

@section('content')
<div class="admin-page support-options-page">

    <div class="admin-page-header">
        <div class="admin-page-header__left">
            <h4 class="admin-page-header__title">Help & Support</h4>
            <p class="admin-page-header__subtitle">Manage support options shown in the app.</p>
        </div>
    </div>

    <div class="support-stats-grid">
        <div class="support-stat-card">
            <div class="support-stat-card__icon support-stat-card__icon--primary"><i class="mdi mdi-lifebuoy"></i></div>
            <div>
                <div class="support-stat-card__label">Total Options</div>
                <div class="support-stat-card__value">{{ number_format($stats['total']) }}</div>
            </div>
        </div>
        <div class="support-stat-card">
            <div class="support-stat-card__icon support-stat-card__icon--success"><i class="mdi mdi-check-circle-outline"></i></div>
            <div>
                <div class="support-stat-card__label">Active</div>
                <div class="support-stat-card__value">{{ number_format($stats['active']) }}</div>
            </div>
        </div>
        <div class="support-stat-card">
            <div class="support-stat-card__icon support-stat-card__icon--secondary"><i class="mdi mdi-pause-circle-outline"></i></div>
            <div>
                <div class="support-stat-card__label">Inactive</div>
                <div class="support-stat-card__value">{{ number_format($stats['inactive']) }}</div>
            </div>
        </div>
    </div>

    @component('admin.components.datatable', [
        'title' => 'Support Options',
        'subtitle' => 'Search by title, type, or support value.',
        'paginator' => $supportOptions,
        'createUrl' => route('admin.support-options.create'),
        'createLabel' => 'Add Option',
        'search' => $search,
        'perPage' => $perPage,
        'sort' => $sort,
        'direction' => $direction,
        'filters' => [
            [
                'name' => 'type',
                'label' => 'Type',
                'value' => $type,
                'options' => [
                    '' => 'All Types',
                    'whatsapp' => 'WhatsApp',
                    'call' => 'Call',
                    'email' => 'Email',
                    'chat' => 'Chat',
                    'website' => 'Website',
                ],
            ],
            [
                'name' => 'status',
                'label' => 'Status',
                'value' => $status,
                'options' => [
                    '' => 'All Statuses',
                    'active' => 'Active',
                    'inactive' => 'Inactive',
                ],
            ],
        ],
        'columns' => [
            ['label' => 'Option', 'field' => 'title', 'sortable' => true],
            ['label' => 'Type', 'field' => 'type', 'sortable' => true],
            ['label' => 'Value', 'sortable' => false],
            ['label' => 'Order', 'field' => 'sort_order', 'sortable' => true],
            ['label' => 'Status', 'field' => 'is_active', 'sortable' => true],
            ['label' => 'Created', 'field' => 'created_at', 'sortable' => true],
            ['label' => 'Actions', 'actions' => true],
        ],
    ])
        @forelse($supportOptions as $option)
            <tr>
                <td>
                    <div class="d-flex align-items-center gap-2">
                        @if($option->image)
                            <img src="{{ str_starts_with($option->image, 'http') ? $option->image : asset('storage/' . $option->image) }}" alt="{{ $option->title }}" class="support-option-icon">
                        @else
                            <div class="support-option-avatar"><i class="mdi mdi-lifebuoy"></i></div>
                        @endif
                        <div>
                            <div class="cell-primary">{{ $option->title }}</div>
                            <div class="cell-muted">Option #{{ $option->id }}</div>
                        </div>
                    </div>
                </td>
                <td>{{ \Illuminate\Support\Str::headline($option->type) }}</td>
                <td class="cell-muted">{{ $option->value }}</td>
                <td>{{ $option->sort_order }}</td>
                <td>
                    <span class="badge {{ $option->is_active ? 'bg-label-success' : 'bg-label-secondary' }}">
                        {{ $option->is_active ? 'Active' : 'Inactive' }}
                    </span>
                </td>
                <td class="cell-muted">{{ $option->created_at?->format('Y-m-d') }}</td>
                <td class="text-end">
                    @include('admin.components.action-buttons', [
                        'type' => 'edit',
                        'href' => route('admin.support-options.edit', $option),
                        'title' => 'Edit Support Option',
                    ])
                    @include('admin.components.action-buttons', [
                        'type' => 'delete',
                        'formAction' => route('admin.support-options.destroy', $option),
                        'confirm' => "Delete support option \"{$option->title}\"?",
                    ])
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="7" class="admin-empty-state">No support options found.</td>
            </tr>
        @endforelse
    @endcomponent

</div>
@endsection

@push('my-styles')
<style>
    .support-stats-grid {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 1rem;
    }

    .support-stat-card {
        background: linear-gradient(180deg, #ffffff 0%, #f9fbfc 100%);
        border: 1px solid #e6ebf2;
        border-radius: 18px;
        padding: 1rem 1.1rem;
        display: flex;
        align-items: center;
        gap: 0.9rem;
        box-shadow: 0 10px 24px rgba(15, 23, 42, 0.05);
    }

    .support-stat-card__icon {
        width: 48px;
        height: 48px;
        border-radius: 14px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 24px;
        flex-shrink: 0;
    }

    .support-stat-card__icon--primary { background: #eef2ff; color: #4f46e5; }
    .support-stat-card__icon--success { background: #ecfdf5; color: #059669; }
    .support-stat-card__icon--secondary { background: #f1f5f9; color: #64748b; }

    .support-stat-card__label {
        font-size: 0.75rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.06em;
        color: #64748b;
        margin-bottom: 0.2rem;
    }

    .support-stat-card__value {
        font-size: 1.5rem;
        font-weight: 800;
        color: #0f172a;
        line-height: 1.1;
    }

    .support-option-icon,
    .support-option-avatar {
        width: 42px;
        height: 42px;
        border-radius: 14px;
        flex-shrink: 0;
    }

    .support-option-icon {
        object-fit: cover;
    }

    .support-option-avatar {
        background: #eef2ff;
        color: #4f46e5;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    @media (max-width: 991px) {
        .support-stats-grid {
            grid-template-columns: 1fr;
        }
    }
</style>
@endpush
