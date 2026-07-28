@extends('layouts/contentNavbarLayout')

@section('title', 'Dashboard')

@section('content')

    {{-- ===== Row 1: User Statistics ===== --}}
    <div class="mb-2">
        <h5 class="dash-section-title"><i class="mdi mdi-account-group-outline me-2 text-primary"></i>User Statistics</h5>
    </div>
    <div class="dash-stats-grid mb-4">
        <div class="dash-stat-card dash-stat-card--primary">
            <div class="dash-stat-card__icon"><i class="mdi mdi-account-multiple"></i></div>
            <div>
                <div class="dash-stat-card__label">Total Users</div>
                <div class="dash-stat-card__value">{{ number_format($userStats['total']) }}</div>
            </div>
        </div>
        @foreach (\App\Models\User::ADMIN_STATUSES as $s)
            <div class="dash-stat-card dash-stat-card--user-{{ $s }}">
                <div class="dash-stat-card__icon"><i class="mdi {{ \App\Models\User::ADMIN_STATUS_ICONS[$s] }}"></i></div>
                <div>
                    <div class="dash-stat-card__label">{{ \App\Models\User::ADMIN_STATUS_LABELS[$s] }}</div>
                    <div class="dash-stat-card__value">{{ number_format($userStats[$s] ?? 0) }}</div>
                </div>
            </div>
        @endforeach
    </div>

    {{-- ===== Row 2: Business Statistics ===== --}}
    {{-- <div class="mb-2">
        <h5 class="dash-section-title"><i class="mdi mdi-briefcase-outline me-2 text-primary"></i>Business Statistics</h5>
    </div>
    <div class="dash-stats-grid mb-4">
        <div class="dash-stat-card dash-stat-card--primary">
            <div class="dash-stat-card__icon"><i class="mdi mdi-briefcase"></i></div>
            <div>
                <div class="dash-stat-card__label">Total Businesses</div>
                <div class="dash-stat-card__value">{{ number_format($businessStats['total']) }}</div>
            </div>
        </div>
        <div class="dash-stat-card dash-stat-card--business-with">
            <div class="dash-stat-card__icon"><i class="mdi mdi-check-decagram"></i></div>
            <div>
            <div class="dash-stat-card__label">With Offerings</div>
            <div class="dash-stat-card__value">{{ number_format($businessStats['with_offerings']) }}</div>
        </div>
        </div>
        <div class="dash-stat-card dash-stat-card--business-without">
        <div class="dash-stat-card__icon"><i class="mdi mdi-alert-circle-outline"></i></div>
        <div>
            <div class="dash-stat-card__label">Without Offerings</div>
            <div class="dash-stat-card__value">{{ number_format($businessStats['without_offerings']) }}</div>
        </div>
    </div>
        <div class="dash-stat-card dash-stat-card--business-locations">
            <div class="dash-stat-card__icon"><i class="mdi mdi-map-marker-radius"></i></div>
            <div>
                <div class="dash-stat-card__label">Unique Locations</div>
                <div class="dash-stat-card__value">{{ number_format($businessStats['unique_locations']) }}</div>
            </div>
        </div>
    </div> --}}

    {{-- ===== Row 3: Latest Businesses & User Registrations ===== --}}
    <div class="row g-4 mb-4">
        {{-- Left Column: Latest Businesses Table --}}
        <div class="col-lg-8">
            <div class="mb-2">
                <h5 class="dash-section-title"><i class="mdi mdi-history me-2 text-primary"></i>Latest Businesses</h5>
            </div>
            <div class="card h-100">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="mb-0">Latest 10 Businesses</h5>
                    </div>
                    <a href="{{ route('admin.business-management.index') }}" class="btn btn-sm admin-add-btn">
                        <i class="mdi mdi-arrow-right me-1"></i> View All
                    </a>
                </div>
                <div class="table-responsive">
                    <table class="table admin-datatable mb-0">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Business Name</th>
                                <th>Location</th>
                                <th>Created Date</th>
                                <th class="text-end admin-actions-col">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($latestBusinesses as $business)
                                <tr>
                                    <td class="fw-semibold">{{ $business->id }}</td>
                                    <td>
                                        <div class="d-flex align-items-center gap-2">
                                            <div class="um-user-avatar bg-label-primary text-primary"
                                                style="width:30px; height:30px; display:flex; align-items:center; justify-content:center; border-radius:8px; font-weight:700; font-size:0.8rem;">
                                                {{ strtoupper(substr($business->name, 0, 1)) }}
                                            </div>
                                            <div class="cell-primary fw-semibold">{{ $business->name }}</div>
                                        </div>
                                    </td>
                                    <td class="cell-primary">{{ $business->location }}</td>
                                    <td class="cell-muted">{{ $business->created_at?->format('Y-m-d') }}</td>
                                    <td class="text-end">
                                        <a href="{{ route('admin.business-management.show', $business) }}"
                                            class="action-icon-btn action-icon-view" data-bs-toggle="tooltip" title="View Business">
                                            <i class="mdi mdi-eye-outline"></i>
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center text-muted py-4">No businesses yet.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- Right Column: User Registrations Chart --}}
        <div class="col-lg-4">
            <div class="mb-2">
                <h5 class="dash-section-title"><i class="mdi mdi-trending-up me-2 text-primary"></i>User Registrations</h5>
            </div>
            <div class="card h-100">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Registration Stats</h5>
                    <div class="dropdown">
                        <button class="btn btn-xs btn-outline-primary dropdown-toggle" type="button" id="chartFilterDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                            Daily
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="chartFilterDropdown">
                            <li><a class="dropdown-item active" href="javascript:void(0);" onclick="switchChartFilter('daily', this)">Daily</a></li>
                            <li><a class="dropdown-item" href="javascript:void(0);" onclick="switchChartFilter('weekly', this)">Weekly</a></li>
                            <li><a class="dropdown-item" href="javascript:void(0);" onclick="switchChartFilter('monthly', this)">Monthly</a></li>
                            <li><a class="dropdown-item" href="javascript:void(0);" onclick="switchChartFilter('yearly', this)">Yearly</a></li>
                        </ul>
                    </div>
                </div>
                <div class="card-body d-flex flex-column justify-content-between" style="min-height: 300px;">
                    <div style="position: relative; height: 260px; width: 100%;">
                        <canvas id="userRegistrationsChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

@endsection

@push('my-styles')
    <style>
        .dash-section-title {
            font-size: 1rem;
            font-weight: 700;
            color: #0f172a;
            letter-spacing: -0.01em;
        }

        .dash-stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(175px, 1fr));
            gap: 1rem;
        }

        .dash-stat-card {
            background: linear-gradient(180deg, #ffffff 0%, #f9fbfc 100%);
            border: 1px solid #e6ebf2;
            border-radius: 18px;
            padding: 1rem 1.1rem;
            display: flex;
            align-items: center;
            gap: 0.9rem;
            box-shadow: 0 10px 24px rgba(15, 23, 42, 0.05);
        }

        .dash-stat-card__icon {
            width: 48px;
            height: 48px;
            border-radius: 14px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            flex-shrink: 0;
        }

        .dash-stat-card__label {
            font-size: 0.7rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.06em;
            color: #64748b;
            margin-bottom: 0.2rem;
            white-space: nowrap;
        }

        .dash-stat-card__value {
            font-size: 1.5rem;
            font-weight: 800;
            color: #0f172a;
            line-height: 1.1;
        }

        /* User stat colours */
        .dash-stat-card--primary .dash-stat-card__icon {
            background: #eef2ff;
            color: #4f46e5;
        }

        .dash-stat-card--user-active .dash-stat-card__icon {
            background: #ecfdf5;
            color: #059669;
        }

        .dash-stat-card--user-pending .dash-stat-card__icon {
            background: #fffbeb;
            color: #d97706;
        }

        .dash-stat-card--user-otp_pending .dash-stat-card__icon {
            background: #f1f5f9;
            color: #64748b;
        }

        .dash-stat-card--user-profile_incomplete .dash-stat-card__icon {
            background: #f1f5f9;
            color: #64748b;
        }

        .dash-stat-card--user-rejected .dash-stat-card__icon {
            background: #fef2f2;
            color: #dc2626;
        }

        .dash-stat-card--user-suspended .dash-stat-card__icon {
            background: #1e293b;
            color: #f1f5f9;
        }

        /* Business stat colours */
        .dash-stat-card--business-with .dash-stat-card__icon {
            background: #ecfdf5;
            color: #059669;
        }

        .dash-stat-card--business-without .dash-stat-card__icon {
            background: #fef2f2;
            color: #dc2626;
        }

        .dash-stat-card--business-locations .dash-stat-card__icon {
            background: #fff3e0;
            color: #ef6c00;
        }

        @media (max-width: 767px) {
            .dash-stats-grid {
                grid-template-columns: 1fr 1fr;
            }
        }
    </style>
@endpush

@push('my-script')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        const chartData = @json($chartData);
        let myChart = null;

        document.addEventListener('DOMContentLoaded', function() {
            initChart('daily');
        });

        function initChart(filter) {
            const ctx = document.getElementById('userRegistrationsChart').getContext('2d');
            const dataObj = chartData[filter];
            const labels = Object.keys(dataObj);
            const dataValues = Object.values(dataObj);

            if (myChart) {
                myChart.destroy();
            }

            myChart = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: labels,
                    datasets: [{
                        label: 'Registered Users',
                        data: dataValues,
                        backgroundColor: 'rgba(79, 70, 229, 0.15)',
                        borderColor: 'rgb(79, 70, 229)',
                        borderWidth: 1.5,
                        borderRadius: 4,
                        hoverBackgroundColor: 'rgba(79, 70, 229, 0.3)'
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { display: false }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: { 
                                stepSize: 1,
                                color: '#64748b'
                            },
                            grid: {
                                color: '#f1f5f9'
                            }
                        },
                        x: {
                            ticks: {
                                color: '#64748b'
                            },
                            grid: {
                                display: false
                            }
                        }
                    }
                }
            });
        }

        function switchChartFilter(filter, el) {
            document.querySelectorAll('#chartFilterDropdown + .dropdown-menu .dropdown-item').forEach(item => {
                item.classList.remove('active');
            });
            el.classList.add('active');
            document.getElementById('chartFilterDropdown').innerText = el.innerText;
            initChart(filter);
        }
    </script>
@endpush
