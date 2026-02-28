@extends('layouts.station.app')

@push('title')
    <h4 class="h4">@lang('station.transactions.title')</h4>
@endpush

@push('custome-plugin')
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap5.min.css">
<script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap5.min.js"></script>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<style>
    .stats-card {
        border-radius: 12px;
        border: none;
        transition: transform 0.2s;
    }
    .stats-card:hover {
        transform: translateY(-2px);
    }
    .stats-icon {
        width: 50px;
        height: 50px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    .proof-image {
        max-width: 100%;
        max-height: 400px;
        border-radius: 8px;
    }
    .filter-card {
        border-radius: 12px;
        border: none;
    }
</style>
@endpush

@section('content')
<div class="container-fluid">
    {{-- Stats Widgets --}}
    <div class="row mb-4">
        <div class="col-md-4 mb-3">
            <div class="card stats-card shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="stats-icon bg-primary bg-opacity-10 me-3">
                            <i class="fas fa-gas-pump fa-lg text-primary"></i>
                        </div>
                        <div>
                            <div class="text-muted small">@lang('station.transactions.total_liters_today')</div>
                            <div class="fs-4 fw-bold text-primary" id="statTotalLiters">
                                {{ number_format($todayStats['total_liters'], 2) }} L
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4 mb-3">
            <div class="card stats-card shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="stats-icon bg-success bg-opacity-10 me-3">
                            <i class="fas fa-coins fa-lg text-success"></i>
                        </div>
                        <div>
                            <div class="text-muted small">@lang('station.transactions.total_sales_today')</div>
                            <div class="fs-4 fw-bold text-success" id="statTotalSales">
                                {{ number_format($todayStats['total_amount'], 2) }} @lang('station.currency')
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4 mb-3">
            <div class="card stats-card shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="stats-icon bg-info bg-opacity-10 me-3">
                            <i class="fas fa-car fa-lg text-info"></i>
                        </div>
                        <div>
                            <div class="text-muted small">@lang('station.transactions.vehicles_served_today')</div>
                            <div class="fs-4 fw-bold text-info" id="statVehicles">
                                {{ $todayStats['vehicles_count'] }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Filter Card --}}
    <div class="card filter-card shadow-sm mb-4">
        <div class="card-body">
            <form id="filterForm" class="row g-3 align-items-end">
                <div class="col-md-2">
                    <label class="form-label small">@lang('station.transactions.date_from')</label>
                    <input type="text" class="form-control form-control-sm" id="dateFrom" name="date_from" placeholder="@lang('station.transactions.select_date')">
                </div>
                <div class="col-md-2">
                    <label class="form-label small">@lang('station.transactions.date_to')</label>
                    <input type="text" class="form-control form-control-sm" id="dateTo" name="date_to" placeholder="@lang('station.transactions.select_date')">
                </div>
                <div class="col-md-2">
                    <label class="form-label small">@lang('station.transactions.worker')</label>
                    <select class="form-select form-select-sm" id="workerId" name="worker_id">
                        <option value="">@lang('station.transactions.all_workers')</option>
                        @foreach($workers as $worker)
                            <option value="{{ $worker->id }}">{{ $worker->full_name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label small">@lang('station.transactions.fuel_type')</label>
                    <select class="form-select form-select-sm" id="fuelTypeId" name="fuel_type_id">
                        <option value="">@lang('station.transactions.all_fuel_types')</option>
                        @foreach($fuelTypes as $fuelType)
                            <option value="{{ $fuelType->id }}">{{ $fuelType->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label small">@lang('station.transactions.status')</label>
                    <select class="form-select form-select-sm" id="statusFilter" name="status">
                        <option value="">@lang('station.transactions.all_statuses')</option>
                        <option value="completed">@lang('station.transactions.status_completed')</option>
                        <option value="pending">@lang('station.transactions.status_pending')</option>
                        <option value="refunded">@lang('station.transactions.status_refunded')</option>
                        <option value="cancelled">@lang('station.transactions.status_cancelled')</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <div class="btn-group w-100">
                        <button type="submit" class="btn btn-station btn-sm">
                            <i class="fas fa-search me-1"></i>@lang('station.transactions.filter')
                        </button>
                        <button type="button" class="btn btn-outline-secondary btn-sm" id="resetFilters">
                            <i class="fas fa-undo"></i>
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- Transactions Table --}}
    <div class="card shadow-sm">
        <div class="card-header bg-white d-flex justify-content-between align-items-center py-3">
            <h5 class="mb-0">
                <i class="fas fa-list me-2 text-station"></i>@lang('station.transactions.transaction_list')
            </h5>
            <div>
                <button type="button" class="btn btn-outline-station btn-sm me-2" id="refreshBtn">
                    <i class="fas fa-sync-alt"></i>
                </button>
                <a href="#" class="btn btn-success btn-sm" id="exportBtn">
                    <i class="fas fa-file-excel me-1"></i>@lang('station.transactions.export')
                </a>
            </div>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0" id="transactionsTable">
                    <thead class="table-light">
                        <tr>
                            <th>@lang('station.transactions.time')</th>
                            <th>@lang('station.transactions.reference')</th>
                            <th>@lang('station.transactions.vehicle')</th>
                            <th>@lang('station.transactions.client')</th>
                            <th>@lang('station.transactions.worker')</th>
                            <th>@lang('station.transactions.fuel_type')</th>
                            <th class="text-end">@lang('station.transactions.liters')</th>
                            <th class="text-end">@lang('station.transactions.amount')</th>
                            <th class="text-center">@lang('station.transactions.status')</th>
                            <th class="text-center">@lang('station.transactions.actions')</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
    </div>
</div>

{{-- Proof Modal --}}
<div class="modal fade" id="proofModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-station text-white">
                <h5 class="modal-title">
                    <i class="fas fa-receipt me-2"></i>@lang('station.transactions.transaction_details')
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-4">
                            <h6 class="text-station border-bottom pb-2 mb-3">
                                <i class="fas fa-info-circle me-2"></i>@lang('station.transactions.details')
                            </h6>
                            <table class="table table-sm table-borderless">
                                <tr>
                                    <td class="text-muted">@lang('station.transactions.reference'):</td>
                                    <td class="fw-bold" id="proofReference">-</td>
                                </tr>
                                <tr>
                                    <td class="text-muted">@lang('station.transactions.time'):</td>
                                    <td id="proofTime">-</td>
                                </tr>
                                <tr>
                                    <td class="text-muted">@lang('station.transactions.vehicle'):</td>
                                    <td id="proofVehicle">-</td>
                                </tr>
                                <tr>
                                    <td class="text-muted">@lang('station.transactions.client'):</td>
                                    <td id="proofClient">-</td>
                                </tr>
                                <tr>
                                    <td class="text-muted">@lang('station.transactions.driver'):</td>
                                    <td id="proofDriver">-</td>
                                </tr>
                                <tr>
                                    <td class="text-muted">@lang('station.transactions.worker'):</td>
                                    <td id="proofWorker">-</td>
                                </tr>
                            </table>
                        </div>
                        <div>
                            <h6 class="text-station border-bottom pb-2 mb-3">
                                <i class="fas fa-calculator me-2"></i>@lang('station.transactions.amounts')
                            </h6>
                            <table class="table table-sm table-borderless">
                                <tr>
                                    <td class="text-muted">@lang('station.transactions.fuel_type'):</td>
                                    <td id="proofFuelType">-</td>
                                </tr>
                                <tr>
                                    <td class="text-muted">@lang('station.transactions.price_per_liter'):</td>
                                    <td id="proofPricePerLiter">-</td>
                                </tr>
                                <tr>
                                    <td class="text-muted">@lang('station.transactions.liters'):</td>
                                    <td class="fw-bold text-primary" id="proofLiters">-</td>
                                </tr>
                                <tr>
                                    <td class="text-muted">@lang('station.transactions.total_amount'):</td>
                                    <td class="fw-bold text-success fs-5" id="proofAmount">-</td>
                                </tr>
                            </table>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <h6 class="text-station border-bottom pb-2 mb-3">
                            <i class="fas fa-camera me-2"></i>@lang('station.transactions.meter_photo')
                        </h6>
                        <div class="text-center" id="proofImageContainer">
                            <div class="text-muted py-5" id="noImagePlaceholder">
                                <i class="fas fa-image fa-4x mb-3 text-muted"></i>
                                <p>@lang('station.transactions.no_image')</p>
                            </div>
                            <img src="" alt="Meter Photo" class="proof-image d-none" id="proofImage">
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    @lang('layouts.close')
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('custome-js')
<script>
$(document).ready(function() {
    var currency = '@lang("station.currency")';
    
    flatpickr('#dateFrom', {
        dateFormat: 'Y-m-d',
        maxDate: 'today'
    });
    
    flatpickr('#dateTo', {
        dateFormat: 'Y-m-d',
        maxDate: 'today'
    });

    var transactionsTable = $('#transactionsTable').DataTable({
        processing: true,
        serverSide: false,
        ajax: {
            url: '{{ route("station.transactions.data") }}',
            data: function(d) {
                d.date_from = $('#dateFrom').val();
                d.date_to = $('#dateTo').val();
                d.worker_id = $('#workerId').val();
                d.fuel_type_id = $('#fuelTypeId').val();
                d.status = $('#statusFilter').val();
            },
            dataSrc: function(json) {
                updateStats(json.stats);
                return json.data;
            }
        },
        columns: [
            { data: 'time' },
            { 
                data: 'reference_no',
                render: function(data) {
                    return '<code class="small">' + data + '</code>';
                }
            },
            { 
                data: 'vehicle',
                render: function(data) {
                    return '<span class="badge bg-light text-dark">' + data + '</span>';
                }
            },
            { data: 'client' },
            { data: 'worker' },
            { data: 'fuel_type' },
            { 
                data: 'liters',
                className: 'text-end',
                render: function(data) {
                    return parseFloat(data).toFixed(2) + ' L';
                }
            },
            { 
                data: 'amount',
                className: 'text-end',
                render: function(data) {
                    return '<span class="fw-bold text-success">' + parseFloat(data).toFixed(2) + '</span>';
                }
            },
            {
                data: 'status',
                className: 'text-center',
                render: function(data) {
                    var badges = {
                        'completed': 'success',
                        'pending': 'warning',
                        'refunded': 'info',
                        'cancelled': 'danger'
                    };
                    var labels = {
                        'completed': '@lang("station.transactions.status_completed")',
                        'pending': '@lang("station.transactions.status_pending")',
                        'refunded': '@lang("station.transactions.status_refunded")',
                        'cancelled': '@lang("station.transactions.status_cancelled")'
                    };
                    return '<span class="badge bg-' + (badges[data] || 'secondary') + '">' + (labels[data] || data) + '</span>';
                }
            },
            {
                data: 'id',
                className: 'text-center',
                orderable: false,
                render: function(data, type, row) {
                    var btn = '<button type="button" class="btn btn-sm btn-outline-station btn-view-proof" data-id="' + data + '"';
                    if (!row.has_image) {
                        btn += ' title="@lang("station.transactions.view_details")">';
                        btn += '<i class="fas fa-info-circle"></i>';
                    } else {
                        btn += ' title="@lang("station.transactions.view_proof")">';
                        btn += '<i class="fas fa-camera"></i>';
                    }
                    btn += '</button>';
                    return btn;
                }
            }
        ],
        order: [[0, 'desc']],
        language: {
            url: window.is_ar ? '//cdn.datatables.net/plug-ins/1.13.4/i18n/ar.json' : ''
        },
        pageLength: 25
    });

    function updateStats(stats) {
        if (stats) {
            $('#statTotalLiters').text(parseFloat(stats.total_liters || 0).toFixed(2) + ' L');
            $('#statTotalSales').text(parseFloat(stats.total_amount || 0).toFixed(2) + ' ' + currency);
            $('#statVehicles').text(stats.vehicles_count || 0);
        }
    }

    $('#filterForm').on('submit', function(e) {
        e.preventDefault();
        transactionsTable.ajax.reload();
    });

    $('#resetFilters').on('click', function() {
        $('#filterForm')[0].reset();
        $('#dateFrom').val('');
        $('#dateTo').val('');
        transactionsTable.ajax.reload();
    });

    $('#refreshBtn').on('click', function() {
        var btn = $(this);
        btn.find('i').addClass('fa-spin');
        transactionsTable.ajax.reload(function() {
            btn.find('i').removeClass('fa-spin');
        }, false);
    });

    $('#exportBtn').on('click', function(e) {
        e.preventDefault();
        var params = new URLSearchParams({
            date_from: $('#dateFrom').val(),
            date_to: $('#dateTo').val(),
            worker_id: $('#workerId').val(),
            fuel_type_id: $('#fuelTypeId').val(),
            status: $('#statusFilter').val()
        });
        window.location.href = '{{ route("station.transactions.export") }}?' + params.toString();
    });

    $(document).on('click', '.btn-view-proof', function() {
        var id = $(this).data('id');
        
        $('#proofReference').text('-');
        $('#proofTime').text('-');
        $('#proofVehicle').text('-');
        $('#proofClient').text('-');
        $('#proofDriver').text('-');
        $('#proofWorker').text('-');
        $('#proofFuelType').text('-');
        $('#proofPricePerLiter').text('-');
        $('#proofLiters').text('-');
        $('#proofAmount').text('-');
        $('#proofImage').addClass('d-none').attr('src', '');
        $('#noImagePlaceholder').removeClass('d-none');
        
        $.ajax({
            url: '{{ route("station.transactions.proof", ":id") }}'.replace(':id', id),
            method: 'GET',
            success: function(res) {
                if (res.success) {
                    var data = res.data;
                    $('#proofReference').text(data.reference_no);
                    $('#proofTime').text(data.time);
                    $('#proofVehicle').text(data.vehicle);
                    $('#proofClient').text(data.client);
                    $('#proofDriver').text(data.driver);
                    $('#proofWorker').text(data.worker);
                    $('#proofFuelType').text(data.fuel_type);
                    $('#proofPricePerLiter').text(parseFloat(data.price_per_liter).toFixed(2) + ' ' + currency);
                    $('#proofLiters').text(parseFloat(data.liters).toFixed(2) + ' L');
                    $('#proofAmount').text(parseFloat(data.amount).toFixed(2) + ' ' + currency);
                    
                    if (data.has_image && data.image_url) {
                        $('#proofImage').attr('src', data.image_url).removeClass('d-none');
                        $('#noImagePlaceholder').addClass('d-none');
                    }
                    
                    $('#proofModal').modal('show');
                }
            },
            error: function() {
                Swal.fire({
                    icon: 'error',
                    title: '@lang("layouts.error")',
                    text: '@lang("station.transactions.load_error")'
                });
            }
        });
    });
});
</script>
@endpush
