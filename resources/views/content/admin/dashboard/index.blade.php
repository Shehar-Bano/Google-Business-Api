@extends('layouts/contentNavbarLayout')

@section('title', 'Dashboard - Squash Pro')

@section('vendor-style')
<link rel="stylesheet" href="{{asset('assets/vendor/libs/apex-charts/apex-charts.css')}}">
@endsection

@section('page-style')
<link rel="stylesheet" href="{{asset('assets/css/dashboard.css')}}">
@endsection

@section('content')
<div class="dashboard-page">
    
    {{-- Page Header --}}
    <div class="app-page-header">
        <div class="app-page-header__left">
            <h4 class="app-page-header__title">Dashboard</h4>
            <p class="app-page-header__subtitle">Squash Pro platform overview and key metrics</p>
        </div>
        <div class="app-page-header__actions">
            <button type="button" class="app-btn-secondary" onclick="window.print()">
                <i class="mdi mdi-download"></i> Export Report
            </button>
            <button type="button" class="app-btn-primary" onclick="window.location.reload()">
                <i class="mdi mdi-refresh"></i> Refresh Data
            </button>
        </div>
    </div>

    {{-- Dashboard Metric Cards Grid --}}
    <div class="dashboard-stats-grid">
        @foreach($dashboardStats as $stat)
        <div class="dashboard-stat-card {{ isset($stat['link']) ? 'clickable' : '' }}" @if(isset($stat['link'])) onclick="window.location='{{ $stat['link'] }}'" @endif>
            <div class="stat-card-icon {{ $stat['iconClass'] }}">
                <i class="{{ $stat['icon'] }}"></i>
            </div>
            <div class="stat-card-content">
                <div class="stat-card-title">{{ $stat['title'] }}</div>
                <div class="stat-card-value">{{ $stat['value'] }}</div>
            </div>
            @if(isset($stat['link']))
            <div class="stat-card-chevron">
                <i class="mdi mdi-chevron-right"></i>
            </div>
            @endif
        </div>
        @endforeach
    </div>

    {{-- Platform Overview Chart --}}
    <div class="dashboard-chart-card">
        <div class="chart-card-header">
            <div>
                <h5 class="chart-card-title">Platform Overview</h5>
                <p class="chart-card-subtitle">Platform activity across the selected period</p>
            </div>
            <div class="chart-card-actions">
                <select class="form-select form-select-sm" id="chartPeriod">
                    <option value="7" @selected(($period ?? 30) === 7)>Last 7 Days</option>
                    <option value="30" @selected(($period ?? 30) === 30)>Last 30 Days</option>
                    <option value="90" @selected(($period ?? 30) === 90)>Last 90 Days</option>
                    <option value="365" @selected(($period ?? 30) === 365)>Last Year</option>
                </select>
            </div>
        </div>
        <div class="chart-card-body">
            <div id="platformOverviewChart"></div>
        </div>
    </div>

    {{-- Quick Actions Section --}}
    <div class="dashboard-quick-actions">
        <h5 class="quick-actions-title">Quick Actions</h5>
        <div class="quick-actions-grid">
            <a href="{{ route('admin.clubs.index') }}" class="quick-action-card">
                <div class="quick-action-icon">
                    <i class="mdi mdi-domain"></i>
                </div>
                <div class="quick-action-content">
                    <h6>Manage Clubs</h6>
                    <p>View and manage all clubs</p>
                </div>
            </a>
            <a href="{{ route('admin.players.index') }}" class="quick-action-card">
                <div class="quick-action-icon">
                    <i class="mdi mdi-account-group"></i>
                </div>
                <div class="quick-action-content">
                    <h6>Manage Players</h6>
                    <p>View all registered players</p>
                </div>
            </a>
            <a href="{{ route('admin.bookings.index') }}" class="quick-action-card">
                <div class="quick-action-icon">
                    <i class="mdi mdi-calendar-check"></i>
                </div>
                <div class="quick-action-content">
                    <h6>View Bookings</h6>
                    <p>Manage court bookings</p>
                </div>
            </a>
            <a href="{{ route('admin.tournaments.index') }}" class="quick-action-card">
                <div class="quick-action-icon">
                    <i class="mdi mdi-trophy"></i>
                </div>
                <div class="quick-action-content">
                    <h6>Tournaments</h6>
                    <p>Manage tournaments</p>
                </div>
            </a>
        </div>
    </div>

</div>
@endsection

@section('vendor-script')
<script src="{{asset('assets/vendor/libs/apex-charts/apexcharts.js')}}"></script>
@endsection

@section('page-script')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Platform Overview Chart
    const chartData = {!! json_encode($chartData) !!};
    
    const options = {
        series: [
            {
                name: 'Bookings',
                data: chartData.bookings
            },
            {
                name: 'Revenue (K)',
                data: chartData.revenue
            },
            {
                name: 'Players',
                data: chartData.players
            },
            {
                name: 'Clubs',
                data: chartData.clubs
            }
        ],
        chart: {
            height: 380,
            type: 'area',
            toolbar: {
                show: true,
                tools: {
                    download: true,
                    selection: false,
                    zoom: false,
                    zoomin: false,
                    zoomout: false,
                    pan: false,
                    reset: false
                }
            },
            fontFamily: 'Inter, sans-serif'
        },
        colors: ['#B5F23C', '#121212', '#F87216', '#6366F1'],
        dataLabels: {
            enabled: false
        },
        stroke: {
            curve: 'smooth',
            width: 3
        },
        fill: {
            type: 'gradient',
            gradient: {
                shadeIntensity: 1,
                opacityFrom: 0.4,
                opacityTo: 0.1,
                stops: [0, 90, 100]
            }
        },
        xaxis: {
            categories: chartData.labels,
            labels: {
                style: {
                    colors: '#6B7280',
                    fontSize: '12px'
                }
            },
            axisBorder: {
                show: false
            },
            axisTicks: {
                show: false
            }
        },
        yaxis: {
            labels: {
                style: {
                    colors: '#6B7280',
                    fontSize: '12px'
                },
                formatter: function(value) {
                    return Math.round(value);
                }
            }
        },
        grid: {
            borderColor: '#E5E7EB',
            strokeDashArray: 4,
            xaxis: {
                lines: {
                    show: false
                }
            },
            yaxis: {
                lines: {
                    show: true
                }
            },
            padding: {
                top: 0,
                right: 0,
                bottom: 0,
                left: 10
            }
        },
        legend: {
            position: 'top',
            horizontalAlign: 'right',
            fontSize: '13px',
            fontWeight: 500,
            labels: {
                colors: '#374151'
            },
            markers: {
                width: 10,
                height: 10,
                radius: 10
            },
            itemMargin: {
                horizontal: 12,
                vertical: 0
            }
        },
        tooltip: {
            theme: 'light',
            x: {
                show: true
            },
            y: {
                formatter: function(value, { seriesIndex }) {
                    if (seriesIndex === 1) {
                        return 'PKR ' + value + 'K';
                    }
                    return value;
                }
            }
        },
        responsive: [{
            breakpoint: 768,
            options: {
                chart: {
                    height: 320
                },
                legend: {
                    position: 'bottom',
                    horizontalAlign: 'center'
                }
            }
        }]
    };

    const chart = new ApexCharts(document.querySelector("#platformOverviewChart"), options);
    chart.render();

    // Chart period change handler
    document.getElementById('chartPeriod').addEventListener('change', function() {
        const url = new URL(window.location.href);
        url.searchParams.set('period', this.value);
        window.location.href = url.toString();
    });
});
</script>
@endsection
