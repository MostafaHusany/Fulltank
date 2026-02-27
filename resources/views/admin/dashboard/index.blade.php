@extends('layouts.admin.app')

@push('title')
    <h1 class="h2">@lang('dashboard.Title')</h1>
@endpush

@push('custome-css')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<style>
    .stat-card {
        border-radius: 10px;
        transition: transform 0.2s;
    }
    .stat-card:hover {
        transform: translateY(-5px);
    }
    .stat-icon {
        width: 60px;
        height: 60px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 24px;
    }
    .stat-value {
        font-size: 1.8rem;
        font-weight: 700;
    }
    .stat-label {
        font-size: 0.9rem;
        color: #6c757d;
    }
    #dashboardMap {
        height: 500px;
        width: 100%;
        border-radius: 10px;
        z-index: 1;
    }
    .map-container {
        position: relative;
    }
    .map-container.fullscreen {
        position: fixed;
        top: 0;
        left: 0;
        width: 100vw;
        height: 100vh;
        z-index: 9999;
        background: #fff;
        padding: 10px;
    }
    .map-container.fullscreen #dashboardMap {
        height: calc(100vh - 80px);
    }
    .fullscreen-btn {
        position: absolute;
        top: 10px;
        right: 10px;
        z-index: 1000;
    }
    .chart-container {
        position: relative;
        height: 300px;
    }
</style>
@endpush

@section('content')
    <!-- Stat Cards Row -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-3">
            <div class="card stat-card shadow-sm border-0 h-100">
                <div class="card-body d-flex align-items-center">
                    <div class="stat-icon bg-primary bg-opacity-10 text-primary me-3">
                        <i class="fas fa-wallet"></i>
                    </div>
                    <div>
                        <div class="stat-value text-primary" id="stat-client-balances">
                            {{ number_format($statCards['client_balances'], 2) }}
                        </div>
                        <div class="stat-label">@lang('dashboard.Client Balances') (EGP)</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-3">
            <div class="card stat-card shadow-sm border-0 h-100">
                <div class="card-body d-flex align-items-center">
                    <div class="stat-icon bg-success bg-opacity-10 text-success me-3">
                        <i class="fas fa-gas-pump"></i>
                    </div>
                    <div>
                        <div class="stat-value text-success" id="stat-station-unsettled">
                            {{ number_format($statCards['station_unsettled'], 2) }}
                        </div>
                        <div class="stat-label">@lang('dashboard.Station Unsettled') (EGP)</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-3">
            <div class="card stat-card shadow-sm border-0 h-100">
                <div class="card-body d-flex align-items-center">
                    <div class="stat-icon bg-info bg-opacity-10 text-info me-3">
                        <i class="fas fa-tint"></i>
                    </div>
                    <div>
                        <div class="stat-value text-info" id="stat-daily-liters">
                            {{ number_format($statCards['daily_liters'], 2) }}
                        </div>
                        <div class="stat-label">@lang('dashboard.Daily Liters')</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-3">
            <div class="card stat-card shadow-sm border-0 h-100">
                <div class="card-body d-flex align-items-center">
                    <div class="stat-icon bg-warning bg-opacity-10 text-warning me-3">
                        <i class="fas fa-exchange-alt"></i>
                    </div>
                    <div>
                        <div class="stat-value text-warning" id="stat-transactions-today">
                            {{ $statCards['transactions_today'] }}
                        </div>
                        <div class="stat-label">@lang('dashboard.Transactions Today')</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts Row -->
    <div class="row mb-4">
        <div class="col-lg-8 mb-3">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-header bg-white border-0">
                    <h6 class="mb-0 fw-bold">
                        <i class="fas fa-chart-line me-2 text-primary"></i>
                        @lang('dashboard.Weekly Consumption Trend')
                    </h6>
                </div>
                <div class="card-body">
                    <div class="chart-container">
                        <canvas id="weeklyTrendChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4 mb-3">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-header bg-white border-0">
                    <h6 class="mb-0 fw-bold">
                        <i class="fas fa-chart-pie me-2 text-success"></i>
                        @lang('dashboard.Fuel Type Distribution')
                    </h6>
                </div>
                <div class="card-body">
                    <div class="chart-container">
                        <canvas id="fuelDistributionChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Map Row -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white border-0">
                    <div class="row align-items-center">
                        <div class="col-md-6">
                            <h6 class="mb-0 fw-bold">
                                <i class="fas fa-map-marked-alt me-2 text-danger"></i>
                                @lang('dashboard.Stations Map')
                            </h6>
                        </div>
                        <div class="col-md-4">
                            <select id="governorate-filter" class="form-select form-select-sm">
                                <option value="">@lang('dashboard.All Governorates')</option>
                                @foreach($governorates as $gov)
                                    <option value="{{ $gov['id'] }}">{{ $gov['name'] }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2 text-end">
                            <button class="btn btn-sm btn-outline-secondary" id="refresh-map-btn">
                                <i class="fas fa-sync-alt"></i>
                            </button>
                            <button class="btn btn-sm btn-outline-primary" id="fullscreen-map-btn">
                                <i class="fas fa-expand"></i>
                            </button>
                        </div>
                    </div>
                </div>
                <div class="card-body map-container" id="map-container">
                    <button class="btn btn-sm btn-danger fullscreen-btn d-none" id="exit-fullscreen-btn">
                        <i class="fas fa-compress me-1"></i> @lang('dashboard.Exit Fullscreen')
                    </button>
                    <div id="dashboardMap"></div>
                </div>
            </div>
        </div>
    </div>
@endSection

@push('custome-js')
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
$('document').ready(function () {
    const ROUTES = {
        chartData: "{{ route('admin.dashboard.chartData') }}",
        mapData: "{{ route('admin.dashboard.mapData') }}",
        stats: "{{ route('admin.dashboard.stats') }}"
    };

    let map = null;
    let markersLayer = null;
    let weeklyChart = null;
    let pieChart = null;

    // Initialize Map
    function initMap() {
        map = L.map('dashboardMap').setView([26.8206, 30.8025], 6);

        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; OpenStreetMap contributors'
        }).addTo(map);

        markersLayer = L.layerGroup().addTo(map);

        loadMapData();
    }

    // Load Map Data
    function loadMapData(governorateId = null) {
        var params = {};
        if (governorateId) params.governorate_id = governorateId;

        axios.get(ROUTES.mapData, { params: params })
            .then(function (res) {
                if (!res.data.success) throw res.data.msg;

                markersLayer.clearLayers();

                var stations = res.data.stations;
                var bounds = [];

                stations.forEach(function (station) {
                    if (station.lat && station.lng) {
                        var marker = L.marker([station.lat, station.lng]);

                        var popupContent = `
                            <div style="min-width: 200px;">
                                <h6 class="mb-2"><strong>${station.name}</strong></h6>
                                <p class="mb-1"><i class="fas fa-user me-1"></i> <strong>@lang('dashboard.Manager'):</strong> ${station.manager_name}</p>
                                <p class="mb-1"><i class="fas fa-map-marker-alt me-1"></i> ${station.governorate}, ${station.district}</p>
                                <hr class="my-2">
                                <p class="mb-1"><i class="fas fa-exchange-alt me-1"></i> <strong>@lang('dashboard.Today'):</strong> ${station.transactions_today} @lang('dashboard.transactions')</p>
                                <p class="mb-0"><i class="fas fa-coins me-1"></i> <strong>@lang('dashboard.Amount'):</strong> ${station.amount_today} EGP</p>
                            </div>
                        `;

                        marker.bindPopup(popupContent);
                        markersLayer.addLayer(marker);
                        bounds.push([station.lat, station.lng]);
                    }
                });

                if (res.data.center && res.data.center.lat && res.data.center.lng) {
                    map.setView([res.data.center.lat, res.data.center.lng], res.data.center.zoom || 10);
                } else if (bounds.length > 0) {
                    map.fitBounds(bounds, { padding: [20, 20] });
                } else {
                    map.setView([26.8206, 30.8025], 6);
                }

                setTimeout(function() {
                    map.invalidateSize();
                }, 100);
            })
            .catch(function (err) {
                console.error('Error loading map data:', err);
            });
    }

    // Load Chart Data
    function loadChartData() {
        axios.get(ROUTES.chartData)
            .then(function (res) {
                if (!res.data.success) throw res.data.msg;

                renderWeeklyChart(res.data.weekly_trend);
                renderPieChart(res.data.fuel_distribution);
            })
            .catch(function (err) {
                console.error('Error loading chart data:', err);
            });
    }

    // Render Weekly Trend Chart
    function renderWeeklyChart(data) {
        var ctx = document.getElementById('weeklyTrendChart').getContext('2d');

        if (weeklyChart) weeklyChart.destroy();

        weeklyChart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: data.labels,
                datasets: [{
                    label: '@lang("dashboard.Liters")',
                    data: data.data,
                    borderColor: '#4e73df',
                    backgroundColor: 'rgba(78, 115, 223, 0.1)',
                    borderWidth: 2,
                    fill: true,
                    tension: 0.4,
                    pointBackgroundColor: '#4e73df',
                    pointBorderColor: '#fff',
                    pointBorderWidth: 2,
                    pointRadius: 5
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: {
                            color: 'rgba(0,0,0,0.05)'
                        }
                    },
                    x: {
                        grid: {
                            display: false
                        }
                    }
                }
            }
        });
    }

    // Render Pie Chart
    function renderPieChart(data) {
        var ctx = document.getElementById('fuelDistributionChart').getContext('2d');

        if (pieChart) pieChart.destroy();

        if (data.labels.length === 0) {
            ctx.font = '16px Arial';
            ctx.fillStyle = '#6c757d';
            ctx.textAlign = 'center';
            ctx.fillText('@lang("dashboard.No data available")', ctx.canvas.width / 2, ctx.canvas.height / 2);
            return;
        }

        pieChart = new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: data.labels,
                datasets: [{
                    data: data.data,
                    backgroundColor: data.colors,
                    borderWidth: 2,
                    borderColor: '#fff'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            padding: 15,
                            usePointStyle: true
                        }
                    }
                },
                cutout: '60%'
            }
        });
    }

    // Governorate Filter
    $('#governorate-filter').on('change', function () {
        var governorateId = $(this).val();
        loadMapData(governorateId || null);
    });

    // Refresh Map
    $('#refresh-map-btn').on('click', function () {
        var governorateId = $('#governorate-filter').val();
        loadMapData(governorateId || null);
    });

    // Fullscreen Toggle
    $('#fullscreen-map-btn').on('click', function () {
        $('#map-container').addClass('fullscreen');
        $('#exit-fullscreen-btn').removeClass('d-none');
        $(this).addClass('d-none');
        setTimeout(function() { map.invalidateSize(); }, 100);
    });

    $('#exit-fullscreen-btn').on('click', function () {
        $('#map-container').removeClass('fullscreen');
        $(this).addClass('d-none');
        $('#fullscreen-map-btn').removeClass('d-none');
        setTimeout(function() { map.invalidateSize(); }, 100);
    });

    // Escape key to exit fullscreen
    $(document).on('keydown', function (e) {
        if (e.key === 'Escape' && $('#map-container').hasClass('fullscreen')) {
            $('#exit-fullscreen-btn').trigger('click');
        }
    });

    // Initialize
    initMap();
    loadChartData();

    // Auto-refresh stats every 60 seconds
    setInterval(function () {
        axios.get(ROUTES.stats)
            .then(function (res) {
                if (res.data.success) {
                    var s = res.data.stats;
                    $('#stat-client-balances').text(parseFloat(s.client_balances).toLocaleString('en-US', {minimumFractionDigits: 2}));
                    $('#stat-station-unsettled').text(parseFloat(s.station_unsettled).toLocaleString('en-US', {minimumFractionDigits: 2}));
                    $('#stat-daily-liters').text(parseFloat(s.daily_liters).toLocaleString('en-US', {minimumFractionDigits: 2}));
                    $('#stat-transactions-today').text(s.transactions_today);
                }
            });
    }, 60000);
});
</script>
@endpush
