@extends('layouts/contentNavbarLayout')

@section('title', $club->club_name . ' — Club Detail')

@section('content')
<div class="d-flex align-items-center gap-2 mb-4">
    <a href="{{ route('admin.clubs.index') }}" class="btn btn-sm btn-outline-secondary">
        <i class="mdi mdi-arrow-left"></i> Back
    </a>
    <h5 class="mb-0">Club Detail</h5>
</div>

<div class="row g-4">

    {{-- Club Info Card --}}
    <div class="col-12 col-lg-4">
        <div class="card h-100">
            <div class="card-body text-center pt-4">
                @if($club->club_logo)
                    <img src="{{ asset('storage/' . $club->club_logo) }}" alt="{{ $club->club_name }}" class="rounded mb-3" style="width:80px;height:80px;object-fit:cover;">
                @else
                    <div class="rounded d-flex align-items-center justify-content-center bg-label-primary mx-auto mb-3" style="width:80px;height:80px;">
                        <i class="mdi mdi-domain" style="font-size:36px;"></i>
                    </div>
                @endif
                <h5 class="mb-1">{{ $club->club_name }}</h5>
                <p class="text-muted small mb-3">{{ $club->email }}</p>

                @php
                    $statusMap = [
                        'active'      => ['bg-label-success', 'Active'],
                        'pending'     => ['bg-label-warning', 'Pending Approval'],
                        'otp_pending' => ['bg-label-secondary', 'OTP Pending'],
                        'rejected'    => ['bg-label-danger', 'Rejected'],
                        'suspended'   => ['bg-label-dark', 'Suspended'],
                    ];
                    [$badgeClass, $badgeLabel] = $statusMap[$club->status] ?? ['bg-label-secondary', ucfirst($club->status)];
                @endphp
                <span class="badge {{ $badgeClass }} mb-3">{{ $badgeLabel }}</span>

                <div class="d-flex justify-content-center gap-2 flex-wrap">
                    <a href="{{ route('admin.clubs.edit', $club) }}" class="btn btn-sm btn-dark">
                        <i class="mdi mdi-pencil-outline"></i> Edit
                    </a>
                    @include('admin.components.action-buttons', [
                        'type' => 'delete',
                        'formAction' => route('admin.clubs.destroy', $club),
                        'confirm' => "Delete club \"{$club->club_name}\"? All courts will also be removed.",
                    ])
                </div>
            </div>
        </div>
    </div>

    {{-- Details --}}
    <div class="col-12 col-lg-8">
        <div class="card mb-4">
            <div class="card-header"><h6 class="mb-0">Club Information</h6></div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-6"><p class="text-muted small mb-1">Owner / Manager</p><p class="fw-semibold mb-0">{{ $club->name }}</p></div>
                    <div class="col-6"><p class="text-muted small mb-1">Phone</p><p class="fw-semibold mb-0">{{ $club->phone ?? '—' }}</p></div>
                    <div class="col-6"><p class="text-muted small mb-1">City</p><p class="fw-semibold mb-0">{{ $club->city ?? '—' }}</p></div>
                    <div class="col-6"><p class="text-muted small mb-1">Address</p><p class="fw-semibold mb-0">{{ $club->address ?? '—' }}</p></div>
                    <div class="col-6"><p class="text-muted small mb-1">Number of Courts</p><p class="fw-semibold mb-0">{{ $club->number_of_courts ?? '—' }}</p></div>
                    <div class="col-6"><p class="text-muted small mb-1">Working Hours</p><p class="fw-semibold mb-0">{{ $club->working_hours ?? '—' }}</p></div>
                    <div class="col-6"><p class="text-muted small mb-1">OTP Verified</p><p class="fw-semibold mb-0">{{ $club->otp_verified ? 'Yes' : 'No' }}</p></div>
                    <div class="col-6"><p class="text-muted small mb-1">Registered</p><p class="fw-semibold mb-0">{{ $club->created_at->format('d M Y') }}</p></div>
                </div>
            </div>
        </div>

        {{-- Update Status --}}
        <div class="card mb-4">
            <div class="card-header"><h6 class="mb-0">Update Status</h6></div>
            <div class="card-body">
                <form action="{{ route('admin.clubs.status', $club) }}" method="POST" class="d-flex align-items-center gap-3 flex-wrap">
                    @csrf @method('PATCH')
                    <select name="status" class="form-select form-select-sm" style="max-width:220px;">
                        @foreach(['otp_pending' => 'OTP Pending', 'pending' => 'Pending Approval', 'active' => 'Active', 'rejected' => 'Rejected', 'suspended' => 'Suspended'] as $val => $label)
                            <option value="{{ $val }}" @selected($club->status === $val)>{{ $label }}</option>
                        @endforeach
                    </select>
                    <button type="submit" class="btn btn-sm btn-dark">Update Status</button>
                </form>
            </div>
        </div>

        {{-- Courts --}}
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h6 class="mb-0">Courts ({{ $club->courts->count() }})</h6>
                <a href="{{ route('admin.clubs.courts.create', $club) }}" class="btn btn-sm btn-dark">
                    <i class="mdi mdi-plus"></i> Add Court
                </a>
            </div>
            <div class="card-body p-0">
                @if($club->courts->isEmpty())
                    <p class="text-muted text-center py-4 mb-0">No courts added yet.</p>
                @else
                    <div class="table-responsive">
                        <table class="table admin-datatable mb-0">
                            <thead>
                                <tr>
                                    <th>Court Name</th>
                                    <th>Type</th>
                                    <th>Price/Hour</th>
                                    <th>Status</th>
                                    <th class="text-end admin-actions-col">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($club->courts as $court)
                                    <tr>
                                        <td class="fw-semibold">{{ $court->name }}</td>
                                        <td>{{ ucfirst($court->type ?? '—') }}</td>
                                        <td>${{ number_format($court->price_per_hour, 2) }}</td>
                                        <td>
                                            @if($court->status === 'active')
                                                <span class="badge bg-label-success">Active</span>
                                            @elseif($court->status === 'maintenance')
                                                <span class="badge bg-label-warning">Maintenance</span>
                                            @else
                                                <span class="badge bg-label-secondary">Inactive</span>
                                            @endif
                                        </td>
                                        <td class="text-end">
                                            @include('admin.components.action-buttons', [
                                                'type' => 'edit',
                                                'href' => route('admin.clubs.courts.edit', [$club, $court]),
                                                'title' => 'Edit Court',
                                            ])
                                            @include('admin.components.action-buttons', [
                                                'type' => 'delete',
                                                'formAction' => route('admin.clubs.courts.destroy', [$club, $court]),
                                                'confirm' => 'Delete this court?',
                                            ])
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
