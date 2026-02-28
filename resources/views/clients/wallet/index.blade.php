@extends('layouts.clients.app')

@push('title')
    <h1 class="h2">@lang('client.wallet.title')</h1>
@endpush

@push('custome-plugin')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
    @if(app()->getLocale() === 'ar')
    <script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/l10n/ar.js"></script>
    @endif
    <style>
        .stat-card {
            border-radius: 16px;
            transition: all 0.3s ease;
            border: none;
            overflow: hidden;
        }
        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 12px 30px rgba(0,0,0,0.12);
        }
        .stat-card .stat-icon {
            width: 56px;
            height: 56px;
            border-radius: 14px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 22px;
        }
        .stat-card .stat-value {
            font-size: 1.6rem;
            font-weight: 700;
            line-height: 1.2;
        }
        .stat-card .stat-label {
            font-size: 0.8rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .chart-card {
            border-radius: 16px;
            border: none;
        }
        .chart-card .card-header {
            background: transparent;
            border-bottom: 1px solid #eee;
            padding: 1rem 1.25rem;
        }
        .chart-card .card-header h6 {
            font-weight: 600;
            margin: 0;
        }
        .chart-loading {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(255,255,255,0.9);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 10;
            border-radius: 16px;
        }
        .filter-section {
            background: linear-gradient(135deg, #f8f9fa 0%, #fff 100%);
            border-radius: 16px;
            border: 1px solid #eee;
        }
        .nav-tabs .nav-link {
            border: none;
            padding: 0.85rem 1.5rem;
            font-weight: 500;
            color: #6c757d;
            border-radius: 0;
        }
        .nav-tabs .nav-link.active {
            background: transparent;
            border-bottom: 3px solid #198754;
            color: #198754;
        }
        .summary-badge {
            padding: 0.5rem 1rem;
            border-radius: 10px;
            font-weight: 600;
        }
    </style>
@endpush

@section('content')
<div class="container-fluid px-0">

    {{-- Top Stats Widgets --}}
    <div class="row mb-4 g-3">
        {{-- Current Balance --}}
        <div class="col-6 col-lg-3">
            <div class="card stat-card shadow-sm h-100" style="background: linear-gradient(135deg, #198754 0%, #20c997 100%);">
                <div class="card-body text-white">
                    <div class="d-flex align-items-center mb-3">
                        <div class="stat-icon bg-white bg-opacity-25">
                            <i class="fas fa-wallet text-white"></i>
                        </div>
                        @if($wallet && !$wallet->is_active)
                            <span class="badge bg-danger ms-auto">@lang('client.wallet.frozen')</span>
                        @endif
                    </div>
                    <p class="stat-label text-white-50 mb-1">@lang('client.wallet.current_balance')</p>
                    <h3 class="stat-value mb-0">{{ number_format($balance ?? 0, 2) }}</h3>
                    <small class="text-white-50">@lang('client.currency')</small>
                </div>
            </div>
        </div>

        {{-- Spent This Month --}}
        <div class="col-6 col-lg-3">
            <div class="card stat-card shadow-sm h-100" style="background: linear-gradient(135deg, #dc3545 0%, #fd7e14 100%);">
                <div class="card-body text-white">
                    <div class="d-flex align-items-center mb-3">
                        <div class="stat-icon bg-white bg-opacity-25">
                            <i class="fas fa-fire-flame-curved text-white"></i>
                        </div>
                    </div>
                    <p class="stat-label text-white-50 mb-1">@lang('client.wallet.monthly_spent')</p>
                    <h3 class="stat-value mb-0" id="widgetMonthlySpent">{{ number_format($monthlySpent ?? 0, 2) }}</h3>
                    <small class="text-white-50">@lang('client.wallet.this_month')</small>
                </div>
            </div>
        </div>

        {{-- Total Transactions --}}
        <div class="col-6 col-lg-3">
            <div class="card stat-card shadow-sm h-100" style="background: linear-gradient(135deg, #0d6efd 0%, #6610f2 100%);">
                <div class="card-body text-white">
                    <div class="d-flex align-items-center mb-3">
                        <div class="stat-icon bg-white bg-opacity-25">
                            <i class="fas fa-receipt text-white"></i>
                        </div>
                    </div>
                    <p class="stat-label text-white-50 mb-1">@lang('client.wallet.total_transactions')</p>
                    <h3 class="stat-value mb-0" id="widgetTotalTransactions">{{ number_format($totalTransactions ?? 0) }}</h3>
                    <small class="text-white-50">@lang('client.wallet.transactions_unit')</small>
                </div>
            </div>
        </div>

        {{-- Average Fill-up Cost --}}
        <div class="col-6 col-lg-3">
            <div class="card stat-card shadow-sm h-100" style="background: linear-gradient(135deg, #17a2b8 0%, #20c997 100%);">
                <div class="card-body text-white">
                    <div class="d-flex align-items-center mb-3">
                        <div class="stat-icon bg-white bg-opacity-25">
                            <i class="fas fa-calculator text-white"></i>
                        </div>
                    </div>
                    <p class="stat-label text-white-50 mb-1">@lang('client.wallet.avg_fillup')</p>
                    <h3 class="stat-value mb-0" id="widgetAvgFillup">{{ number_format($avgFillUp ?? 0, 2) }}</h3>
                    <small class="text-white-50">@lang('client.currency') / @lang('client.wallet.per_transaction')</small>
                </div>
            </div>
        </div>
    </div>

    {{-- Charts Row --}}
    @if($wallet)
    <div class="row mb-4 g-3">
        {{-- Daily Consumption Chart --}}
        <div class="col-12 col-lg-8">
            <div class="card chart-card shadow-sm h-100">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h6><i class="fas fa-chart-line me-2 text-primary"></i>@lang('client.wallet.daily_consumption')</h6>
                    <div class="d-flex gap-2">
                        <span class="badge bg-primary summary-badge" id="chartTotalAmount">0</span>
                        <span class="badge bg-info summary-badge" id="chartTotalLiters">0 L</span>
                    </div>
                </div>
                <div class="card-body position-relative">
                    <div id="dailyChartLoader" class="chart-loading" style="display: none;">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                    </div>
                    <div id="dailyChart" style="height: 320px;"></div>
                </div>
            </div>
        </div>

        {{-- Fuel Distribution Chart --}}
        <div class="col-12 col-lg-4">
            <div class="card chart-card shadow-sm h-100">
                <div class="card-header">
                    <h6><i class="fas fa-chart-pie me-2 text-success"></i>@lang('client.wallet.fuel_distribution')</h6>
                </div>
                <div class="card-body position-relative">
                    <div id="fuelChartLoader" class="chart-loading" style="display: none;">
                        <div class="spinner-border text-success" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                    </div>
                    <div id="fuelChart" style="height: 320px;"></div>
                </div>
            </div>
        </div>
    </div>

    {{-- Top Vehicles Chart --}}
    <div class="row mb-4 g-3">
        <div class="col-12">
            <div class="card chart-card shadow-sm">
                <div class="card-header">
                    <h6><i class="fas fa-trophy me-2 text-warning"></i>@lang('client.wallet.top_vehicles')</h6>
                </div>
                <div class="card-body position-relative">
                    <div id="vehiclesChartLoader" class="chart-loading" style="display: none;">
                        <div class="spinner-border text-warning" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                    </div>
                    <div id="vehiclesChart" style="height: 280px;"></div>
                </div>
            </div>
        </div>
    </div>
    @endif

    {{-- Filter & Transactions Section --}}
    <div class="card border-0 shadow-sm" style="border-radius: 16px;">
        {{-- Filter Bar --}}
        <div class="card-header filter-section border-0 p-3">
            <div class="row g-2 align-items-end">
                <div class="col-12 col-sm-6 col-md-2">
                    <label class="form-label small mb-1 fw-semibold">@lang('client.wallet.date_from')</label>
                    <input type="text" class="form-control form-control-sm datepicker" id="f-date_from" placeholder="@lang('client.wallet.select_date')">
                </div>
                <div class="col-12 col-sm-6 col-md-2">
                    <label class="form-label small mb-1 fw-semibold">@lang('client.wallet.date_to')</label>
                    <input type="text" class="form-control form-control-sm datepicker" id="f-date_to" placeholder="@lang('client.wallet.select_date')">
                </div>
                <div class="col-12 col-sm-6 col-md-2">
                    <label class="form-label small mb-1 fw-semibold">@lang('client.wallet.vehicle')</label>
                    <select class="form-select form-select-sm" id="f-vehicle_id">
                        <option value="">@lang('layouts.all')</option>
                        @foreach($vehicles as $vehicle)
                            <option value="{{ $vehicle->id }}">{{ $vehicle->plate_number }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-12 col-sm-6 col-md-2">
                    <label class="form-label small mb-1 fw-semibold">@lang('client.wallet.driver')</label>
                    <select class="form-select form-select-sm" id="f-driver_id">
                        <option value="">@lang('layouts.all')</option>
                        @foreach($drivers as $driver)
                            <option value="{{ $driver->id }}">{{ $driver->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-12 col-sm-6 col-md-2">
                    <label class="form-label small mb-1 fw-semibold">@lang('client.wallet.fuel_type')</label>
                    <select class="form-select form-select-sm" id="f-fuel_type_id">
                        <option value="">@lang('layouts.all')</option>
                        @foreach($fuelTypes as $fuelType)
                            <option value="{{ $fuelType->id }}">{{ $fuelType->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-12 col-sm-6 col-md-2">
                    <div class="d-flex gap-2">
                        <button type="button" class="btn btn-primary btn-sm flex-fill" id="applyFilters">
                            <i class="fas fa-filter me-1"></i>@lang('client.wallet.filter')
                        </button>
                        <button type="button" class="btn btn-outline-secondary btn-sm" id="resetFilters" title="@lang('layouts.clear')">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        {{-- Tabs --}}
        <div class="card-header bg-white border-bottom-0 pt-3 pb-0">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                <ul class="nav nav-tabs border-0" id="walletTabs" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" id="fuel-tab" data-bs-toggle="tab" data-bs-target="#fuel-transactions" type="button" role="tab">
                            <i class="fas fa-gas-pump me-2"></i>@lang('client.wallet.fuel_transactions')
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="wallet-tab" data-bs-toggle="tab" data-bs-target="#wallet-transactions" type="button" role="tab">
                            <i class="fas fa-exchange-alt me-2"></i>@lang('client.wallet.wallet_transactions')
                        </button>
                    </li>
                </ul>
                <div class="d-flex gap-2">
                    <a href="#" id="exportBtn" class="btn btn-outline-success btn-sm">
                        <i class="fas fa-file-excel me-1"></i>@lang('client.wallet.export_excel')
                    </a>
                </div>
            </div>
        </div>

        <div class="tab-content" id="walletTabsContent">
            {{-- Fuel Transactions Tab --}}
            <div class="tab-pane fade show active" id="fuel-transactions" role="tabpanel">
                <div class="card-body">
                    @if($wallet)
                    <div class="table-responsive">
                        <table id="fuel-transactions-table" class="table table-hover align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>#</th>
                                    <th>@lang('client.wallet.date')</th>
                                    <th>@lang('client.wallet.reference')</th>
                                    <th>@lang('client.wallet.vehicle')</th>
                                    <th>@lang('client.wallet.driver')</th>
                                    <th>@lang('client.wallet.station')</th>
                                    <th>@lang('client.wallet.fuel_type')</th>
                                    <th>@lang('client.wallet.liters')</th>
                                    <th>@lang('client.wallet.unit_price')</th>
                                    <th>@lang('client.wallet.total_amount')</th>
                                    <th>@lang('client.wallet.status')</th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>
                    @else
                    <div class="text-center text-muted py-5">
                        <i class="fas fa-wallet fa-4x mb-3 text-muted opacity-50"></i>
                        <p class="mb-0">@lang('client.wallet.no_wallet')</p>
                    </div>
                    @endif
                </div>
            </div>

            {{-- Wallet Transactions Tab --}}
            <div class="tab-pane fade" id="wallet-transactions" role="tabpanel">
                <div class="card-body">
                    @if($wallet)
                    <div class="table-responsive">
                        <table id="wallet-transactions-table" class="table table-hover align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>#</th>
                                    <th>@lang('client.wallet.type')</th>
                                    <th>@lang('client.wallet.amount')</th>
                                    <th>@lang('client.wallet.before_balance')</th>
                                    <th>@lang('client.wallet.after_balance')</th>
                                    <th>@lang('client.wallet.notes')</th>
                                    <th>@lang('client.wallet.date')</th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>
                    @else
                    <div class="text-center text-muted py-5">
                        <i class="fas fa-wallet fa-4x mb-3 text-muted opacity-50"></i>
                        <p class="mb-0">@lang('client.wallet.no_wallet')</p>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

</div>
@endsection

@push('custome-js')
<script>
(function() {
    var isAr = {{ app()->getLocale() === 'ar' ? 'true' : 'false' }};
    var currency = '@lang('client.currency')';

    flatpickr('.datepicker', {
        dateFormat: 'Y-m-d',
        locale: isAr ? 'ar' : 'default',
        allowInput: true
    });

    @if($wallet)

    var dailyChart, fuelChart, vehiclesChart;

    function initCharts() {
        var chartOptions = {
            chart: {
                fontFamily: 'inherit',
                toolbar: { show: false },
                locales: [{
                    name: 'ar',
                    options: {
                        months: ['يناير', 'فبراير', 'مارس', 'أبريل', 'مايو', 'يونيو', 'يوليو', 'أغسطس', 'سبتمبر', 'أكتوبر', 'نوفمبر', 'ديسمبر'],
                        shortMonths: ['يناير', 'فبراير', 'مارس', 'أبريل', 'مايو', 'يونيو', 'يوليو', 'أغسطس', 'سبتمبر', 'أكتوبر', 'نوفمبر', 'ديسمبر'],
                        days: ['الأحد', 'الإثنين', 'الثلاثاء', 'الأربعاء', 'الخميس', 'الجمعة', 'السبت'],
                        shortDays: ['أحد', 'إثن', 'ثلا', 'أرب', 'خمي', 'جمع', 'سبت']
                    }
                }],
                defaultLocale: isAr ? 'ar' : 'en'
            }
        };

        dailyChart = new ApexCharts(document.querySelector("#dailyChart"), {
            ...chartOptions,
            chart: {
                ...chartOptions.chart,
                type: 'area',
                height: 320,
                animations: { enabled: true, speed: 500 }
            },
            series: [{
                name: '@lang('client.wallet.amount')',
                data: []
            }, {
                name: '@lang('client.wallet.liters')',
                data: []
            }],
            colors: ['#198754', '#0d6efd'],
            fill: {
                type: 'gradient',
                gradient: {
                    shadeIntensity: 1,
                    opacityFrom: 0.4,
                    opacityTo: 0.1
                }
            },
            stroke: { curve: 'smooth', width: 3 },
            xaxis: { type: 'datetime', labels: { datetimeUTC: false } },
            yaxis: [
                { title: { text: '@lang('client.wallet.amount') (' + currency + ')' }, labels: { formatter: v => v.toFixed(0) } },
                { opposite: true, title: { text: '@lang('client.wallet.liters')' }, labels: { formatter: v => v.toFixed(0) } }
            ],
            tooltip: {
                x: { format: 'dd MMM yyyy' },
                y: { formatter: v => v ? v.toFixed(2) : '0' }
            },
            noData: { text: '@lang('client.wallet.no_data')' }
        });
        dailyChart.render();

        fuelChart = new ApexCharts(document.querySelector("#fuelChart"), {
            ...chartOptions,
            chart: {
                ...chartOptions.chart,
                type: 'donut',
                height: 320
            },
            series: [],
            labels: [],
            colors: ['#198754', '#fd7e14', '#0d6efd', '#6f42c1', '#dc3545'],
            plotOptions: {
                pie: {
                    donut: {
                        size: '65%',
                        labels: {
                            show: true,
                            total: {
                                show: true,
                                label: '@lang('client.wallet.total')',
                                formatter: function(w) {
                                    return w.globals.seriesTotals.reduce((a, b) => a + b, 0).toFixed(0);
                                }
                            }
                        }
                    }
                }
            },
            legend: { position: 'bottom' },
            noData: { text: '@lang('client.wallet.no_data')' }
        });
        fuelChart.render();

        vehiclesChart = new ApexCharts(document.querySelector("#vehiclesChart"), {
            ...chartOptions,
            chart: {
                ...chartOptions.chart,
                type: 'bar',
                height: 280
            },
            series: [{
                name: '@lang('client.wallet.amount')',
                data: []
            }],
            colors: ['#ffc107'],
            plotOptions: {
                bar: {
                    horizontal: true,
                    borderRadius: 6,
                    dataLabels: { position: 'top' }
                }
            },
            dataLabels: {
                enabled: true,
                formatter: v => v.toFixed(2) + ' ' + currency,
                offsetX: 30,
                style: { fontSize: '12px', colors: ['#333'] }
            },
            xaxis: {
                categories: [],
                labels: { formatter: v => v.toFixed(0) }
            },
            noData: { text: '@lang('client.wallet.no_data')' }
        });
        vehiclesChart.render();
    }

    function getFilterParams() {
        return {
            date_from: $('#f-date_from').val(),
            date_to: $('#f-date_to').val(),
            vehicle_id: $('#f-vehicle_id').val(),
            driver_id: $('#f-driver_id').val(),
            fuel_type_id: $('#f-fuel_type_id').val()
        };
    }

    function showChartLoaders() {
        $('#dailyChartLoader, #fuelChartLoader, #vehiclesChartLoader').show();
    }

    function hideChartLoaders() {
        $('#dailyChartLoader, #fuelChartLoader, #vehiclesChartLoader').hide();
    }

    function loadChartData() {
        showChartLoaders();

        $.ajax({
            url: '{{ route("client.wallet.chartData") }}',
            data: getFilterParams(),
            success: function(res) {
                var dailyDates = res.daily.map(d => d.date);
                var dailyAmounts = res.daily.map(d => parseFloat(d.total));
                var dailyLiters = res.daily.map(d => parseFloat(d.liters));

                dailyChart.updateOptions({
                    xaxis: { categories: dailyDates }
                });
                dailyChart.updateSeries([
                    { name: '@lang('client.wallet.amount')', data: dailyAmounts },
                    { name: '@lang('client.wallet.liters')', data: dailyLiters }
                ]);

                var fuelLabels = res.fuel_dist.map(f => f.fuel_type);
                var fuelValues = res.fuel_dist.map(f => parseFloat(f.total));
                fuelChart.updateOptions({ labels: fuelLabels });
                fuelChart.updateSeries(fuelValues);

                var vehLabels = res.top_vehicles.map(v => v.vehicle);
                var vehValues = res.top_vehicles.map(v => parseFloat(v.total));
                vehiclesChart.updateOptions({
                    xaxis: { categories: vehLabels }
                });
                vehiclesChart.updateSeries([{ data: vehValues }]);

                var totalAmount = parseFloat(res.summary.total_amount) || 0;
                var totalLiters = parseFloat(res.summary.total_liters) || 0;
                var transactionsCount = parseInt(res.summary.transactions_count) || 0;
                var avgFillup = parseFloat(res.summary.avg_fillup) || 0;

                $('#chartTotalAmount').text(totalAmount.toFixed(2) + ' ' + currency);
                $('#chartTotalLiters').text(totalLiters.toFixed(2) + ' L');
                $('#widgetTotalTransactions').text(transactionsCount.toLocaleString());
                $('#widgetAvgFillup').text(avgFillup.toFixed(2));

                hideChartLoaders();
            },
            error: function() {
                hideChartLoaders();
            }
        });
    }

    var fuelTable = $('#fuel-transactions-table').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: '{{ route("client.wallet.fuelTransactions") }}',
            data: function(d) {
                var params = getFilterParams();
                d.date_from = params.date_from;
                d.date_to = params.date_to;
                d.vehicle_id = params.vehicle_id;
                d.driver_id = params.driver_id;
                d.fuel_type_id = params.fuel_type_id;
            }
        },
        columns: [
            { data: 'id', name: 'id' },
            { data: 'formatted_date', name: 'created_at' },
            { data: 'reference_no', name: 'reference_no' },
            { data: 'vehicle_plate', name: 'vehicle_plate', orderable: false },
            { data: 'driver_name', name: 'driver_name', orderable: false },
            { data: 'station_name', name: 'station_name', orderable: false },
            { data: 'fuel_type_name', name: 'fuel_type_name', orderable: false },
            { data: 'liters_display', name: 'actual_liters' },
            { data: 'price_display', name: 'price_per_liter' },
            { data: 'total_display', name: 'total_amount' },
            { data: 'status_badge', name: 'status', orderable: false }
        ],
        order: [[0, 'desc']],
        language: {
            url: isAr ? '//cdn.datatables.net/plug-ins/1.13.4/i18n/ar.json' : ''
        }
    });

    var walletTable = $('#wallet-transactions-table').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: '{{ route("client.wallet.transactions") }}',
            data: function(d) {
                d.date_from = $('#f-date_from').val();
                d.date_to = $('#f-date_to').val();
            }
        },
        columns: [
            { data: 'id', name: 'id' },
            { data: 'type_badge', name: 'type', orderable: false },
            { data: 'amount_display', name: 'amount' },
            {
                data: 'before_balance',
                name: 'before_balance',
                render: function(data) { return parseFloat(data).toFixed(2); }
            },
            {
                data: 'after_balance',
                name: 'after_balance',
                render: function(data) { return parseFloat(data).toFixed(2); }
            },
            { data: 'notes', name: 'notes' },
            { data: 'formatted_date', name: 'created_at' }
        ],
        order: [[0, 'desc']],
        language: {
            url: isAr ? '//cdn.datatables.net/plug-ins/1.13.4/i18n/ar.json' : ''
        }
    });

    $('#applyFilters').on('click', function() {
        fuelTable.ajax.reload();
        walletTable.ajax.reload();
        loadChartData();
    });

    $('#resetFilters').on('click', function() {
        $('#f-date_from, #f-date_to').val('');
        $('#f-vehicle_id, #f-driver_id, #f-fuel_type_id').val('');
        fuelTable.ajax.reload();
        walletTable.ajax.reload();
        loadChartData();
    });

    $('#exportBtn').on('click', function(e) {
        e.preventDefault();
        var params = new URLSearchParams(getFilterParams());
        window.location.href = '{{ route("client.wallet.export") }}?' + params.toString();
    });

    initCharts();
    loadChartData();

    $('button[data-bs-toggle="tab"]').on('shown.bs.tab', function(e) {
        if ($(e.target).attr('id') === 'wallet-tab') {
            walletTable.columns.adjust().draw(false);
        } else if ($(e.target).attr('id') === 'fuel-tab') {
            fuelTable.columns.adjust().draw(false);
        }
    });

    @endif
})();
</script>
@endpush
