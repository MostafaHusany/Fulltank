@extends('layouts.station.app')

@push('title')
    <h4 class="h4">@lang('station.dashboard.title')</h4>
@endpush

@push('custome-plugin')
<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
<style>
    .stats-card {
        border-radius: 16px;
        border: none;
        transition: all 0.3s ease;
    }
    .stats-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 30px rgba(0,0,0,0.1) !important;
    }
    .stats-icon {
        width: 60px;
        height: 60px;
        border-radius: 14px;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    .chart-card {
        border-radius: 16px;
        border: none;
    }
    .period-btn {
        border-radius: 20px;
        padding: 0.4rem 1rem;
        font-size: 0.85rem;
    }
    .period-btn.active {
        background-color: var(--station-primary) !important;
        border-color: var(--station-primary) !important;
        color: #fff !important;
    }
    .worker-rank {
        width: 28px;
        height: 28px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: bold;
        font-size: 0.85rem;
    }
    .rank-1 { background: linear-gradient(135deg, #FFD700, #FFA500); color: #fff; }
    .rank-2 { background: linear-gradient(135deg, #C0C0C0, #A9A9A9); color: #fff; }
    .rank-3 { background: linear-gradient(135deg, #CD7F32, #8B4513); color: #fff; }
    .rank-default { background: #e9ecef; color: #6c757d; }
    .latest-tx-time {
        font-size: 0.75rem;
        color: #6c757d;
    }
    .no-station-card {
        background: linear-gradient(135deg, #f8f9fa, #e9ecef);
        border-radius: 20px;
    }
</style>
@endpush

@section('content')
<div class="container-fluid">
    @if(!$hasStation)
        {{-- No Station Assigned --}}
        <div class="row justify-content-center mt-5">
            <div class="col-md-6">
                <div class="card no-station-card border-0 shadow">
                    <div class="card-body text-center py-5">
                        <i class="fas fa-gas-pump fa-4x text-muted mb-4"></i>
                        <h4 class="text-muted">@lang('station.dashboard.no_station')</h4>
                        <p class="text-muted">@lang('station.dashboard.no_station_message')</p>
                    </div>
                </div>
            </div>
        </div>
    @else
        {{-- Period Filter --}}
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div class="btn-group" role="group">
                <button type="button" class="btn btn-outline-secondary period-btn {{ $currentPeriod == 'today' ? 'active' : '' }}" data-period="today">
                    @lang('station.dashboard.today')
                </button>
                <button type="button" class="btn btn-outline-secondary period-btn {{ $currentPeriod == 'week' ? 'active' : '' }}" data-period="week">
                    @lang('station.dashboard.this_week')
                </button>
                <button type="button" class="btn btn-outline-secondary period-btn {{ $currentPeriod == 'month' ? 'active' : '' }}" data-period="month">
                    @lang('station.dashboard.this_month')
                </button>
            </div>
            <button type="button" class="btn btn-outline-station btn-sm" id="refreshDashboard">
                <i class="fas fa-sync-alt me-1"></i>@lang('station.dashboard.refresh')
            </button>
        </div>

        {{-- Stats Cards --}}
        <div class="row mb-4">
            <div class="col-md-3 mb-3">
                <div class="card stats-card shadow-sm h-100">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="stats-icon bg-success bg-opacity-10 me-3">
                                <i class="fas fa-hand-holding-usd fa-lg text-success"></i>
                            </div>
                            <div>
                                <div class="text-muted small">@lang('station.dashboard.outstanding_balance')</div>
                                <div class="fs-4 fw-bold text-success" id="statBalance">
                                    {{ $stats['formatted_balance'] }}
                                </div>
                                <small class="text-muted">@lang('station.currency')</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="card stats-card shadow-sm h-100">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="stats-icon bg-primary bg-opacity-10 me-3">
                                <i class="fas fa-gas-pump fa-lg text-primary"></i>
                            </div>
                            <div>
                                <div class="text-muted small">@lang('station.dashboard.period_liters')</div>
                                <div class="fs-4 fw-bold text-primary" id="statLiters">
                                    {{ number_format($stats['period_liters'], 2) }}
                                </div>
                                <small class="text-muted">@lang('station.dashboard.liters')</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="card stats-card shadow-sm h-100">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="stats-icon bg-info bg-opacity-10 me-3">
                                <i class="fas fa-receipt fa-lg text-info"></i>
                            </div>
                            <div>
                                <div class="text-muted small">@lang('station.dashboard.transactions')</div>
                                <div class="fs-4 fw-bold text-info" id="statTransactions">
                                    {{ $stats['period_transactions'] }}
                                </div>
                                <small class="text-muted">@lang('station.dashboard.operations')</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="card stats-card shadow-sm h-100">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="stats-icon bg-warning bg-opacity-10 me-3">
                                <i class="fas fa-users fa-lg text-warning"></i>
                            </div>
                            <div>
                                <div class="text-muted small">@lang('station.dashboard.active_workers')</div>
                                <div class="fs-4 fw-bold text-warning" id="statWorkers">
                                    {{ $stats['active_workers'] }} / {{ $stats['total_workers'] }}
                                </div>
                                <small class="text-muted">@lang('station.dashboard.workers')</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Charts Row --}}
        <div class="row mb-4">
            <div class="col-md-8 mb-3">
                <div class="card chart-card shadow-sm h-100">
                    <div class="card-header bg-white border-0 pt-3">
                        <h6 class="mb-0">
                            <i class="fas fa-chart-line text-station me-2"></i>@lang('station.dashboard.sales_trend')
                        </h6>
                    </div>
                    <div class="card-body">
                        <div id="salesTrendChart" style="height: 300px;"></div>
                    </div>
                </div>
            </div>
            <div class="col-md-4 mb-3">
                <div class="card chart-card shadow-sm h-100">
                    <div class="card-header bg-white border-0 pt-3">
                        <h6 class="mb-0">
                            <i class="fas fa-chart-pie text-station me-2"></i>@lang('station.dashboard.fuel_consumption')
                        </h6>
                    </div>
                    <div class="card-body">
                        <div id="fuelTypeChart" style="height: 300px;"></div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Tables Row --}}
        <div class="row">
            <div class="col-md-5 mb-3">
                <div class="card chart-card shadow-sm h-100">
                    <div class="card-header bg-white border-0 pt-3 d-flex justify-content-between align-items-center">
                        <h6 class="mb-0">
                            <i class="fas fa-trophy text-station me-2"></i>@lang('station.dashboard.top_workers')
                        </h6>
                        <small class="text-muted">@lang('station.dashboard.by_transactions')</small>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>#</th>
                                        <th>@lang('station.dashboard.worker')</th>
                                        <th class="text-center">@lang('station.dashboard.tx_count')</th>
                                        <th class="text-end">@lang('station.dashboard.amount')</th>
                                    </tr>
                                </thead>
                                <tbody id="topWorkersBody">
                                    <tr>
                                        <td colspan="4" class="text-center py-4">
                                            <div class="spinner-border spinner-border-sm text-station"></div>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-7 mb-3">
                <div class="card chart-card shadow-sm h-100">
                    <div class="card-header bg-white border-0 pt-3 d-flex justify-content-between align-items-center">
                        <h6 class="mb-0">
                            <i class="fas fa-clock text-station me-2"></i>@lang('station.dashboard.latest_transactions')
                        </h6>
                        <a href="{{ route('station.transactions.index') }}" class="btn btn-sm btn-outline-station">
                            @lang('station.dashboard.view_all')
                        </a>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>@lang('station.dashboard.time')</th>
                                        <th>@lang('station.dashboard.vehicle')</th>
                                        <th>@lang('station.dashboard.worker')</th>
                                        <th>@lang('station.dashboard.fuel_type')</th>
                                        <th class="text-end">@lang('station.dashboard.liters')</th>
                                        <th class="text-end">@lang('station.dashboard.amount')</th>
                                    </tr>
                                </thead>
                                <tbody id="latestTxBody">
                                    <tr>
                                        <td colspan="6" class="text-center py-4">
                                            <div class="spinner-border spinner-border-sm text-station"></div>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
@endsection

@if($hasStation ?? false)
@push('custome-js')
<script>
$(document).ready(function() {
    var currency = '@lang("station.currency")';
    var currentPeriod = '{{ $currentPeriod }}';
    var salesTrendChart, fuelTypeChart;

    var chartColors = ['#e65100', '#ff9800', '#4caf50', '#2196f3', '#9c27b0', '#f44336', '#00bcd4'];

    function initCharts() {
        salesTrendChart = new ApexCharts(document.querySelector("#salesTrendChart"), {
            series: [{
                name: '@lang("station.dashboard.revenue")',
                data: []
            }],
            chart: {
                type: 'area',
                height: 300,
                toolbar: { show: false },
                fontFamily: 'inherit'
            },
            colors: ['#e65100'],
            fill: {
                type: 'gradient',
                gradient: {
                    shadeIntensity: 1,
                    opacityFrom: 0.4,
                    opacityTo: 0.1,
                    stops: [0, 90, 100]
                }
            },
            stroke: { curve: 'smooth', width: 3 },
            dataLabels: { enabled: false },
            xaxis: { categories: [], labels: { rotate: -45 } },
            yaxis: {
                labels: {
                    formatter: function(val) {
                        return val.toFixed(0);
                    }
                }
            },
            tooltip: {
                y: {
                    formatter: function(val) {
                        return val.toFixed(2) + ' ' + currency;
                    }
                }
            }
        });
        salesTrendChart.render();

        fuelTypeChart = new ApexCharts(document.querySelector("#fuelTypeChart"), {
            series: [],
            chart: {
                type: 'donut',
                height: 300,
                fontFamily: 'inherit'
            },
            colors: chartColors,
            labels: [],
            legend: {
                position: 'bottom',
                horizontalAlign: 'center'
            },
            plotOptions: {
                pie: {
                    donut: {
                        size: '60%',
                        labels: {
                            show: true,
                            total: {
                                show: true,
                                label: '@lang("station.dashboard.total")',
                                formatter: function(w) {
                                    return w.globals.seriesTotals.reduce((a, b) => a + b, 0).toFixed(0) + ' L';
                                }
                            }
                        }
                    }
                }
            },
            tooltip: {
                y: {
                    formatter: function(val) {
                        return val.toFixed(2) + ' L';
                    }
                }
            }
        });
        fuelTypeChart.render();
    }

    function loadDashboardData() {
        $.ajax({
            url: '{{ route("station.dashboard.analytics") }}',
            data: { period: currentPeriod },
            success: function(res) {
                if (res.success) {
                    updateStats(res.stats);
                    updateSalesTrendChart(res.sales_trend);
                    updateFuelTypeChart(res.fuel_consumption);
                    updateTopWorkers(res.top_workers);
                    updateLatestTransactions(res.latest_transactions);
                }
            }
        });
    }

    function updateStats(stats) {
        $('#statLiters').text(parseFloat(stats.total_liters || 0).toFixed(2));
        $('#statTransactions').text(stats.transactions_count || 0);
        $('#statWorkers').text((stats.active_workers || 0) + ' / ' + {{ $stats['total_workers'] }});
    }

    function updateSalesTrendChart(data) {
        var categories = data.map(function(d) { return d.label; });
        var amounts = data.map(function(d) { return d.amount; });

        salesTrendChart.updateOptions({
            xaxis: { categories: categories }
        });
        salesTrendChart.updateSeries([{
            name: '@lang("station.dashboard.revenue")',
            data: amounts
        }]);
    }

    function updateFuelTypeChart(data) {
        if (data.length === 0) {
            fuelTypeChart.updateOptions({
                labels: ['@lang("station.dashboard.no_data")']
            });
            fuelTypeChart.updateSeries([1]);
            return;
        }

        var labels = data.map(function(d) { return d.name; });
        var series = data.map(function(d) { return d.liters; });

        fuelTypeChart.updateOptions({
            labels: labels
        });
        fuelTypeChart.updateSeries(series);
    }

    function updateTopWorkers(workers) {
        var html = '';
        if (workers.length === 0) {
            html = '<tr><td colspan="4" class="text-center text-muted py-4">@lang("station.dashboard.no_workers_data")</td></tr>';
        } else {
            workers.forEach(function(w) {
                var rankClass = w.rank <= 3 ? 'rank-' + w.rank : 'rank-default';
                html += '<tr>';
                html += '<td><span class="worker-rank ' + rankClass + '">' + w.rank + '</span></td>';
                html += '<td class="fw-semibold">' + w.name + '</td>';
                html += '<td class="text-center"><span class="badge bg-info">' + w.transactions + '</span></td>';
                html += '<td class="text-end text-success fw-bold">' + parseFloat(w.amount).toFixed(2) + '</td>';
                html += '</tr>';
            });
        }
        $('#topWorkersBody').html(html);
    }

    function updateLatestTransactions(transactions) {
        var html = '';
        if (transactions.length === 0) {
            html = '<tr><td colspan="6" class="text-center text-muted py-4">@lang("station.dashboard.no_transactions")</td></tr>';
        } else {
            transactions.forEach(function(tx) {
                html += '<tr>';
                html += '<td><span class="latest-tx-time">' + tx.time + '</span><br><small class="text-muted">' + tx.date + '</small></td>';
                html += '<td><span class="badge bg-light text-dark">' + tx.vehicle + '</span></td>';
                html += '<td>' + tx.worker + '</td>';
                html += '<td><span class="badge bg-secondary">' + tx.fuel_type + '</span></td>';
                html += '<td class="text-end">' + parseFloat(tx.liters).toFixed(2) + ' L</td>';
                html += '<td class="text-end fw-bold text-success">' + parseFloat(tx.amount).toFixed(2) + '</td>';
                html += '</tr>';
            });
        }
        $('#latestTxBody').html(html);
    }

    $('.period-btn').on('click', function() {
        var period = $(this).data('period');
        currentPeriod = period;

        $('.period-btn').removeClass('active');
        $(this).addClass('active');

        loadDashboardData();
    });

    $('#refreshDashboard').on('click', function() {
        var btn = $(this);
        btn.find('i').addClass('fa-spin');
        loadDashboardData();
        setTimeout(function() {
            btn.find('i').removeClass('fa-spin');
        }, 1000);
    });

    initCharts();
    loadDashboardData();
});
</script>
@endpush
@endif
