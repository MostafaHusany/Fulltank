@extends('layouts.admin.app')

@push('title')
    <h1 class="h2">@lang('reports.Title')</h1>
@endpush

@push('custome-css')
<style>
    .report-tab { cursor: pointer; }
    .report-tab.active { background-color: #0d6efd !important; color: white !important; }
    .stat-card { border-radius: 10px; transition: transform 0.2s; }
    .stat-card:hover { transform: translateY(-3px); }
    .stat-value { font-size: 1.5rem; font-weight: 700; }
    .report-table th { white-space: nowrap; background-color: #f8f9fa; }
    .report-table td { vertical-align: middle; }
    .running-balance { font-weight: 600; }
    .running-balance.positive { color: #198754; }
    .running-balance.negative { color: #dc3545; }
    .settled-row { background-color: #e8f5e9 !important; }
    .unsettled-row { background-color: #fff3e0 !important; }
    .pagination { justify-content: center; margin-top: 1rem; }
</style>
@endpush

@section('content')
<div class="card">
    <div class="card-header">
        <div class="row">
            <div class="col-6 pt-1">
                @lang('reports.Title Administration')
            </div>
            <div class="col-6 text-end">
                <button class="btn btn-sm btn-outline-dark" id="refresh-report">
                    <i class="fas fa-sync-alt"></i>
                </button>
                <button class="btn btn-sm btn-outline-success" id="export-excel" disabled>
                    <i class="fas fa-file-excel"></i> Excel
                </button>
                <button class="btn btn-sm btn-outline-danger" id="export-pdf" disabled>
                    <i class="fas fa-file-pdf"></i> PDF
                </button>
                <button class="btn btn-sm btn-outline-secondary" id="print-report" disabled>
                    <i class="fas fa-print"></i> @lang('reports.Print')
                </button>
            </div>
        </div>
    </div>

    <div class="card-body">
        <!-- Report Type Tabs -->
        <ul class="nav nav-pills mb-4" id="reportTabs">
            <li class="nav-item">
                <button class="nav-link report-tab active" data-report-type="client">
                    <i class="fas fa-building me-1"></i> @lang('reports.Client Statement')
                </button>
            </li>
            <li class="nav-item">
                <button class="nav-link report-tab" data-report-type="station">
                    <i class="fas fa-gas-pump me-1"></i> @lang('reports.Station Report')
                </button>
            </li>
            <li class="nav-item">
                <button class="nav-link report-tab" data-report-type="vehicle">
                    <i class="fas fa-car me-1"></i> @lang('reports.Vehicle Consumption')
                </button>
            </li>
            <li class="nav-item">
                <button class="nav-link report-tab" data-report-type="summary">
                    <i class="fas fa-chart-pie me-1"></i> @lang('reports.Overall Summary')
                </button>
            </li>
        </ul>

        @include('admin.reports.incs._filters')

        <!-- Stats Cards -->
        <div id="stats-container" class="row mb-4" style="display: none;"></div>

        <!-- Report Results -->
        <div id="report-results" style="display: none;">
            <div class="table-responsive">
                <table id="reportTable" class="table table-bordered table-hover report-table text-center">
                    <thead></thead>
                    <tbody></tbody>
                </table>
            </div>
            <nav id="pagination-container"></nav>
        </div>

        <!-- Loading Indicator -->
        <div id="report-loading" class="text-center py-5" style="display: none;">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
            <p class="mt-2">@lang('reports.Loading report data...')</p>
        </div>

        <!-- No Data Message -->
        <div id="no-data-message" class="text-center py-5" style="display: none;">
            <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
            <p class="text-muted">@lang('reports.No data found')</p>
        </div>

        <!-- Initial Message -->
        <div id="initial-message" class="text-center py-5">
            <i class="fas fa-chart-bar fa-3x text-primary mb-3"></i>
            <p class="text-muted">@lang('reports.Select filters and generate report')</p>
        </div>
    </div>
</div>

@include('admin.reports.incs._vehicle_detail_modal')

@endSection

@push('custome-js')
<script>
$(document).ready(function() {
    const ROUTES = {
        clientStatement   : "{{ route('admin.reports.clientStatement') }}",
        stationReport     : "{{ route('admin.reports.stationReport') }}",
        vehicleConsumption: "{{ route('admin.reports.vehicleConsumption') }}",
        vehicleDetail     : "{{ route('admin.reports.vehicleDetail') }}",
        overallSummary    : "{{ route('admin.reports.overallSummary') }}",
        exportPdf         : "{{ route('admin.reports.exportPdf') }}",
        searchClients     : "{{ route('admin.search.users') }}",
        searchStations    : "{{ route('admin.search.stations') }}",
        searchVehicles    : "{{ route('admin.search.vehicles') }}"
    };

    let currentReportType = 'client';
    let currentData = null;
    let currentPage = 1;

    // Initialize Select2
    initializeSelect2();

    // Tab Click
    $('.report-tab').on('click', function() {
        $('.report-tab').removeClass('active');
        $(this).addClass('active');
        currentReportType = $(this).data('report-type');
        updateFilterVisibility();
        resetReport();
    });

    // Generate Report Button
    $('#generate-report').on('click', function() {
        currentPage = 1;
        generateReport();
    });

    // Refresh Report
    $('#refresh-report').on('click', function() {
        if (currentData) {
            generateReport();
        }
    });

    // Export PDF
    $('#export-pdf').on('click', function() {
        if (!currentData) return;

        let params = {
            report_type: currentReportType === 'vehicle' ? 'vehicle' : currentReportType,
            date_from: $('#filter-date-from').val(),
            date_to: $('#filter-date-to').val()
        };

        if (currentReportType === 'client') {
            params.id = $('#filter-client').val();
        } else if (currentReportType === 'station') {
            params.id = $('#filter-station').val();
        }

        window.location.href = ROUTES.exportPdf + '?' + $.param(params);
    });

    // Print
    $('#print-report').on('click', function() {
        window.print();
    });

    function initializeSelect2() {
        $('#filter-client').select2({
            allowClear: true,
            width: '100%',
            placeholder: '@lang("reports.Select Client")',
            ajax: {
                url: ROUTES.searchClients,
                dataType: 'json',
                delay: 150,
                data: function(params) {
                    return { q: params.term, category: 'client' };
                },
                processResults: function(data) {
                    return {
                        results: $.map(data, function(item) {
                            return { text: item.company_name || item.name, id: item.id };
                        })
                    };
                },
                cache: true
            }
        });

        $('#filter-station').select2({
            allowClear: true,
            width: '100%',
            placeholder: '@lang("reports.Select Station")',
            ajax: {
                url: ROUTES.searchStations,
                dataType: 'json',
                delay: 150,
                data: function(params) {
                    return { q: params.term };
                },
                processResults: function(data) {
                    return {
                        results: $.map(data, function(item) {
                            return { text: item.name, id: item.id };
                        })
                    };
                },
                cache: true
            }
        });

        $('#filter-governorate').select2({
            allowClear: true,
            width: '100%',
            placeholder: '@lang("reports.Select Governorate")'
        });
    }

    function updateFilterVisibility() {
        $('.filter-group').hide();
        $('.filter-date').show();

        if (currentReportType === 'client' || currentReportType === 'vehicle') {
            $('.filter-client').show();
        } else if (currentReportType === 'station') {
            $('.filter-station').show();
            $('.filter-governorate').show();
        }
    }

    function resetReport() {
        currentData = null;
        $('#stats-container').hide().html('');
        $('#report-results').hide();
        $('#no-data-message').hide();
        $('#initial-message').show();
        $('#export-excel, #export-pdf, #print-report').attr('disabled', true);
    }

    function generateReport() {
        let url, params = {
            date_from: $('#filter-date-from').val(),
            date_to: $('#filter-date-to').val(),
            page: currentPage
        };

        switch (currentReportType) {
            case 'client':
                if (!$('#filter-client').val()) {
                    window.failerToast('@lang("reports.Please select a client")');
                    return;
                }
                url = ROUTES.clientStatement;
                params.client_id = $('#filter-client').val();
                break;

            case 'station':
                if (!$('#filter-station').val()) {
                    window.failerToast('@lang("reports.Please select a station")');
                    return;
                }
                url = ROUTES.stationReport;
                params.station_id = $('#filter-station').val();
                break;

            case 'vehicle':
                if (!$('#filter-client').val()) {
                    window.failerToast('@lang("reports.Please select a client")');
                    return;
                }
                url = ROUTES.vehicleConsumption;
                params.client_id = $('#filter-client').val();
                break;

            case 'summary':
                url = ROUTES.overallSummary;
                break;
        }

        $('#initial-message').hide();
        $('#no-data-message').hide();
        $('#report-results').hide();
        $('#report-loading').show();

        axios.get(url, { params })
            .then(function(response) {
                $('#report-loading').hide();

                if (!response.data.success) {
                    window.failerToast(response.data.msg);
                    $('#no-data-message').show();
                    return;
                }

                currentData = response.data.data;
                renderReport();
                $('#export-excel, #export-pdf, #print-report').removeAttr('disabled');
            })
            .catch(function(error) {
                $('#report-loading').hide();
                $('#no-data-message').show();
                window.failerToast('@lang("reports.Error loading report")');
            });
    }

    function renderReport() {
        switch (currentReportType) {
            case 'client':
                renderClientStatement();
                break;
            case 'station':
                renderStationReport();
                break;
            case 'vehicle':
                renderVehicleConsumption();
                break;
            case 'summary':
                renderOverallSummary();
                break;
        }
    }

    function renderClientStatement() {
        let data = currentData;

        let statsHtml = `
            <div class="col-md-3">
                <div class="card stat-card border-primary">
                    <div class="card-body text-center">
                        <i class="fas fa-wallet fa-2x text-primary mb-2"></i>
                        <p class="mb-0 text-muted">@lang('reports.Opening Balance')</p>
                        <p class="stat-value text-primary">${formatNumber(data.opening_balance)} EGP</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card stat-card border-success">
                    <div class="card-body text-center">
                        <i class="fas fa-arrow-down fa-2x text-success mb-2"></i>
                        <p class="mb-0 text-muted">@lang('reports.Total Credits')</p>
                        <p class="stat-value text-success">${formatNumber(data.total_credits)} EGP</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card stat-card border-danger">
                    <div class="card-body text-center">
                        <i class="fas fa-arrow-up fa-2x text-danger mb-2"></i>
                        <p class="mb-0 text-muted">@lang('reports.Total Debits')</p>
                        <p class="stat-value text-danger">${formatNumber(data.total_debits)} EGP</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card stat-card border-info">
                    <div class="card-body text-center">
                        <i class="fas fa-balance-scale fa-2x text-info mb-2"></i>
                        <p class="mb-0 text-muted">@lang('reports.Closing Balance')</p>
                        <p class="stat-value text-info">${formatNumber(data.closing_balance)} EGP</p>
                    </div>
                </div>
            </div>
        `;

        $('#stats-container').html(statsHtml).show();

        let thead = `
            <tr>
                <th>#</th>
                <th>@lang('reports.Date')</th>
                <th>@lang('reports.Type')</th>
                <th>@lang('reports.Amount')</th>
                <th>@lang('reports.Running Balance')</th>
                <th>@lang('reports.Notes')</th>
            </tr>
        `;

        let tbody = '';
        let transactions = data.transactions.data || [];

        if (transactions.length === 0) {
            tbody = '<tr><td colspan="6" class="text-center text-muted">@lang("reports.No transactions found")</td></tr>';
        } else {
            transactions.forEach((txn, idx) => {
                let amountClass = txn.amount >= 0 ? 'text-success' : 'text-danger';
                let balanceClass = txn.after_balance >= 0 ? 'positive' : 'negative';
                tbody += `
                    <tr>
                        <td>${idx + 1}</td>
                        <td>${formatDate(txn.created_at)}</td>
                        <td><span class="badge bg-secondary">${txn.type}</span></td>
                        <td class="${amountClass}">${formatNumber(txn.amount)} EGP</td>
                        <td class="running-balance ${balanceClass}">${formatNumber(txn.after_balance)} EGP</td>
                        <td>${txn.notes || '---'}</td>
                    </tr>
                `;
            });
        }

        $('#reportTable thead').html(thead);
        $('#reportTable tbody').html(tbody);
        renderPagination(data.transactions);
        $('#report-results').show();
    }

    function renderStationReport() {
        let data = currentData;

        let statsHtml = `
            <div class="col-md-3">
                <div class="card stat-card border-primary">
                    <div class="card-body text-center">
                        <i class="fas fa-receipt fa-2x text-primary mb-2"></i>
                        <p class="mb-0 text-muted">@lang('reports.Transactions')</p>
                        <p class="stat-value text-primary">${data.stats.transaction_count}</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card stat-card border-success">
                    <div class="card-body text-center">
                        <i class="fas fa-gas-pump fa-2x text-success mb-2"></i>
                        <p class="mb-0 text-muted">@lang('reports.Total Liters')</p>
                        <p class="stat-value text-success">${formatNumber(data.stats.total_liters)} L</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card stat-card border-info">
                    <div class="card-body text-center">
                        <i class="fas fa-coins fa-2x text-info mb-2"></i>
                        <p class="mb-0 text-muted">@lang('reports.Total Revenue')</p>
                        <p class="stat-value text-info">${formatNumber(data.stats.total_amount)} EGP</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card stat-card border-warning">
                    <div class="card-body text-center">
                        <i class="fas fa-wallet fa-2x text-warning mb-2"></i>
                        <p class="mb-0 text-muted">@lang('reports.Unsettled Balance')</p>
                        <p class="stat-value text-warning">${formatNumber(data.stats.current_balance)} EGP</p>
                    </div>
                </div>
            </div>
        `;

        $('#stats-container').html(statsHtml).show();

        let thead = `
            <tr>
                <th>#</th>
                <th>@lang('reports.Date')</th>
                <th>@lang('reports.Reference')</th>
                <th>@lang('reports.Client')</th>
                <th>@lang('reports.Vehicle')</th>
                <th>@lang('reports.Fuel Type')</th>
                <th>@lang('reports.Liters')</th>
                <th>@lang('reports.Amount')</th>
                <th>@lang('reports.Status')</th>
            </tr>
        `;

        let tbody = '';
        let transactions = data.transactions.data || [];

        if (transactions.length === 0) {
            tbody = '<tr><td colspan="9" class="text-center text-muted">@lang("reports.No transactions found")</td></tr>';
        } else {
            transactions.forEach((txn, idx) => {
                let statusBadge = getStatusBadge(txn.status);
                let rowClass = txn.status === 'completed' ? 'settled-row' : '';
                tbody += `
                    <tr class="${rowClass}">
                        <td>${idx + 1}</td>
                        <td>${formatDate(txn.created_at)}</td>
                        <td><code>${txn.reference_no}</code></td>
                        <td>${txn.client?.company_name || txn.client?.name || '---'}</td>
                        <td>${txn.vehicle?.plate_number || '---'}</td>
                        <td>${txn.fuel_type?.name || '---'}</td>
                        <td>${formatNumber(txn.actual_liters)} L</td>
                        <td>${formatNumber(txn.total_amount)} EGP</td>
                        <td>${statusBadge}</td>
                    </tr>
                `;
            });
        }

        $('#reportTable thead').html(thead);
        $('#reportTable tbody').html(tbody);
        renderPagination(data.transactions);
        $('#report-results').show();
    }

    function renderVehicleConsumption() {
        let data = currentData;

        let statsHtml = `
            <div class="col-md-4">
                <div class="card stat-card border-primary">
                    <div class="card-body text-center">
                        <i class="fas fa-receipt fa-2x text-primary mb-2"></i>
                        <p class="mb-0 text-muted">@lang('reports.Total Transactions')</p>
                        <p class="stat-value text-primary">${data.overall_stats.transaction_count}</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card stat-card border-success">
                    <div class="card-body text-center">
                        <i class="fas fa-gas-pump fa-2x text-success mb-2"></i>
                        <p class="mb-0 text-muted">@lang('reports.Total Liters')</p>
                        <p class="stat-value text-success">${formatNumber(data.overall_stats.total_liters)} L</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card stat-card border-info">
                    <div class="card-body text-center">
                        <i class="fas fa-coins fa-2x text-info mb-2"></i>
                        <p class="mb-0 text-muted">@lang('reports.Total Amount')</p>
                        <p class="stat-value text-info">${formatNumber(data.overall_stats.total_amount)} EGP</p>
                    </div>
                </div>
            </div>
        `;

        $('#stats-container').html(statsHtml).show();

        let thead = `
            <tr>
                <th>#</th>
                <th>@lang('reports.Plate Number')</th>
                <th>@lang('reports.Model')</th>
                <th>@lang('reports.Transactions')</th>
                <th>@lang('reports.Total Liters')</th>
                <th>@lang('reports.Total Amount')</th>
                <th>@lang('reports.Actions')</th>
            </tr>
        `;

        let tbody = '';
        let vehicles = data.vehicles.data || [];

        if (vehicles.length === 0) {
            tbody = '<tr><td colspan="7" class="text-center text-muted">@lang("reports.No vehicles found")</td></tr>';
        } else {
            vehicles.forEach((v, idx) => {
                tbody += `
                    <tr>
                        <td>${idx + 1}</td>
                        <td><strong>${v.plate_number}</strong></td>
                        <td>${v.model || '---'}</td>
                        <td>${v.transaction_count || 0}</td>
                        <td>${formatNumber(v.total_liters || 0)} L</td>
                        <td>${formatNumber(v.total_amount || 0)} EGP</td>
                        <td>
                            <button class="btn btn-sm btn-outline-primary view-vehicle-detail" 
                                    data-vehicle-id="${v.id}"
                                    data-vehicle-plate="${v.plate_number}">
                                <i class="fas fa-eye"></i>
                            </button>
                        </td>
                    </tr>
                `;
            });
        }

        $('#reportTable thead').html(thead);
        $('#reportTable tbody').html(tbody);
        renderPagination(data.vehicles);
        $('#report-results').show();
    }

    function renderOverallSummary() {
        let data = currentData;

        let statsHtml = `
            <div class="col-md-2">
                <div class="card stat-card border-primary">
                    <div class="card-body text-center">
                        <i class="fas fa-receipt fa-2x text-primary mb-2"></i>
                        <p class="mb-0 text-muted">@lang('reports.Transactions')</p>
                        <p class="stat-value text-primary">${data.transactions.count}</p>
                    </div>
                </div>
            </div>
            <div class="col-md-2">
                <div class="card stat-card border-success">
                    <div class="card-body text-center">
                        <i class="fas fa-gas-pump fa-2x text-success mb-2"></i>
                        <p class="mb-0 text-muted">@lang('reports.Total Liters')</p>
                        <p class="stat-value text-success">${formatNumber(data.transactions.total_liters)} L</p>
                    </div>
                </div>
            </div>
            <div class="col-md-2">
                <div class="card stat-card border-info">
                    <div class="card-body text-center">
                        <i class="fas fa-coins fa-2x text-info mb-2"></i>
                        <p class="mb-0 text-muted">@lang('reports.Revenue')</p>
                        <p class="stat-value text-info">${formatNumber(data.transactions.total_amount)} EGP</p>
                    </div>
                </div>
            </div>
            <div class="col-md-2">
                <div class="card stat-card border-warning">
                    <div class="card-body text-center">
                        <i class="fas fa-hand-holding-usd fa-2x text-warning mb-2"></i>
                        <p class="mb-0 text-muted">@lang('reports.Settlements')</p>
                        <p class="stat-value text-warning">${formatNumber(data.settlements_total)} EGP</p>
                    </div>
                </div>
            </div>
            <div class="col-md-2">
                <div class="card stat-card border-secondary">
                    <div class="card-body text-center">
                        <i class="fas fa-users fa-2x text-secondary mb-2"></i>
                        <p class="mb-0 text-muted">@lang('reports.Active Clients')</p>
                        <p class="stat-value text-secondary">${data.active_clients}</p>
                    </div>
                </div>
            </div>
            <div class="col-md-2">
                <div class="card stat-card border-dark">
                    <div class="card-body text-center">
                        <i class="fas fa-gas-pump fa-2x text-dark mb-2"></i>
                        <p class="mb-0 text-muted">@lang('reports.Active Stations')</p>
                        <p class="stat-value text-dark">${data.active_stations}</p>
                    </div>
                </div>
            </div>
        `;

        $('#stats-container').html(statsHtml).show();
        $('#reportTable thead, #reportTable tbody').html('');
        $('#pagination-container').html('');
        $('#report-results').show();
    }

    function renderPagination(paginationData) {
        if (!paginationData || paginationData.last_page <= 1) {
            $('#pagination-container').html('');
            return;
        }

        let html = '<ul class="pagination">';

        if (paginationData.current_page > 1) {
            html += `<li class="page-item"><a class="page-link" href="#" data-page="${paginationData.current_page - 1}">&laquo;</a></li>`;
        }

        for (let i = 1; i <= paginationData.last_page; i++) {
            let active = i === paginationData.current_page ? 'active' : '';
            html += `<li class="page-item ${active}"><a class="page-link" href="#" data-page="${i}">${i}</a></li>`;
        }

        if (paginationData.current_page < paginationData.last_page) {
            html += `<li class="page-item"><a class="page-link" href="#" data-page="${paginationData.current_page + 1}">&raquo;</a></li>`;
        }

        html += '</ul>';
        $('#pagination-container').html(html);
    }

    $(document).on('click', '#pagination-container .page-link', function(e) {
        e.preventDefault();
        currentPage = $(this).data('page');
        generateReport();
    });

    $(document).on('click', '.view-vehicle-detail', function() {
        let vehicleId = $(this).data('vehicle-id');
        let vehiclePlate = $(this).data('vehicle-plate');

        $('#vehicleDetailModalLabel').text('@lang("reports.Vehicle Details"): ' + vehiclePlate);
        $('#vehicle-detail-loading').show();
        $('#vehicle-detail-content').hide();
        $('#vehicleDetailModal').modal('show');

        axios.get(ROUTES.vehicleDetail, {
            params: {
                vehicle_id: vehicleId,
                date_from: $('#filter-date-from').val(),
                date_to: $('#filter-date-to').val()
            }
        }).then(function(response) {
            $('#vehicle-detail-loading').hide();

            if (!response.data.success) {
                window.failerToast(response.data.msg);
                return;
            }

            let data = response.data.data;
            let tbody = '';

            let transactions = data.transactions.data || [];
            if (transactions.length === 0) {
                tbody = '<tr><td colspan="6" class="text-center text-muted">@lang("reports.No transactions found")</td></tr>';
            } else {
                transactions.forEach((txn, idx) => {
                    let statusBadge = getStatusBadge(txn.status);
                    tbody += `
                        <tr>
                            <td>${idx + 1}</td>
                            <td>${formatDate(txn.created_at)}</td>
                            <td>${txn.station?.name || '---'}</td>
                            <td>${txn.fuel_type?.name || '---'}</td>
                            <td>${formatNumber(txn.actual_liters)} L</td>
                            <td>${formatNumber(txn.total_amount)} EGP</td>
                        </tr>
                    `;
                });
            }

            $('#vehicle-detail-stats').html(`
                <strong>@lang('reports.Total'):</strong> ${data.stats.transaction_count} @lang('reports.transactions'), 
                ${formatNumber(data.stats.total_liters)} L, 
                ${formatNumber(data.stats.total_amount)} EGP
            `);
            $('#vehicle-detail-table tbody').html(tbody);
            $('#vehicle-detail-content').show();
        });
    });

    function formatNumber(num) {
        return parseFloat(num || 0).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    }

    function formatDate(dateStr) {
        if (!dateStr) return '---';
        let d = new Date(dateStr);
        return d.toLocaleDateString('en-GB') + ' ' + d.toLocaleTimeString('en-GB', { hour: '2-digit', minute: '2-digit' });
    }

    function getStatusBadge(status) {
        let badges = {
            'pending'   : '<span class="badge bg-warning">@lang("fuel_transactions.pending")</span>',
            'completed' : '<span class="badge bg-success">@lang("fuel_transactions.completed")</span>',
            'refunded'  : '<span class="badge bg-info">@lang("fuel_transactions.refunded")</span>',
            'cancelled' : '<span class="badge bg-danger">@lang("fuel_transactions.cancelled")</span>'
        };
        return badges[status] || '<span class="badge bg-secondary">' + status + '</span>';
    }

    updateFilterVisibility();
});
</script>
@endpush
