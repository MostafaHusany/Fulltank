@extends('layouts.station.app')

@push('title')
    <h4 class="h4">@lang('station.financials.title')</h4>
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
    .statement-card {
        border-radius: 16px;
        border: none;
        overflow: hidden;
    }
    .balance-highlight {
        background: linear-gradient(135deg, var(--station-primary), var(--station-primary-dark));
        color: #fff;
    }
    .balance-highlight .balance-amount {
        font-size: 2.5rem;
        font-weight: 700;
    }
    .summary-widget {
        border-radius: 12px;
        border: none;
        transition: transform 0.2s;
    }
    .summary-widget:hover {
        transform: translateY(-3px);
    }
    .widget-icon {
        width: 60px;
        height: 60px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    .payment-badge {
        font-size: 0.85rem;
        padding: 0.4em 0.8em;
    }
    .receipt-image {
        max-width: 100%;
        max-height: 500px;
        border-radius: 8px;
    }
    .tab-content {
        padding-top: 1.5rem;
    }
    .month-summary {
        background: #f8f9fa;
        border-radius: 12px;
        padding: 1rem;
    }
</style>
@endpush

@section('content')
<div class="container-fluid">
    {{-- Statement Header --}}
    <div class="card statement-card shadow mb-4">
        <div class="card-body balance-highlight py-4">
            <div class="row align-items-center">
                <div class="col-md-4 text-center border-end">
                    <div class="text-white-50 small mb-1">@lang('station.financials.total_earnings')</div>
                    <div class="fs-3 fw-bold">{{ number_format($summary['total_revenue'], 2) }}</div>
                    <div class="small">@lang('station.currency')</div>
                </div>
                <div class="col-md-4 text-center border-end">
                    <div class="text-white-50 small mb-1">@lang('station.financials.total_received')</div>
                    <div class="fs-3 fw-bold text-success-emphasis">{{ number_format($summary['settled_amount'], 2) }}</div>
                    <div class="small">@lang('station.currency')</div>
                </div>
                <div class="col-md-4 text-center">
                    <div class="text-white-50 small mb-1">@lang('station.financials.outstanding_balance')</div>
                    <div class="balance-amount">{{ number_format($summary['outstanding_balance'], 2) }}</div>
                    <div class="small">@lang('station.currency')</div>
                </div>
            </div>
        </div>
    </div>

    {{-- Month Summary & Stats --}}
    <div class="row mb-4">
        <div class="col-md-3 mb-3">
            <div class="card summary-widget shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="widget-icon bg-primary bg-opacity-10 me-3">
                            <i class="fas fa-calendar-alt fa-lg text-primary"></i>
                        </div>
                        <div>
                            <div class="text-muted small">@lang('station.financials.this_month_revenue')</div>
                            <div class="fs-5 fw-bold text-primary">{{ number_format($summary['this_month']['revenue'], 2) }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="card summary-widget shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="widget-icon bg-success bg-opacity-10 me-3">
                            <i class="fas fa-hand-holding-usd fa-lg text-success"></i>
                        </div>
                        <div>
                            <div class="text-muted small">@lang('station.financials.this_month_settled')</div>
                            <div class="fs-5 fw-bold text-success">{{ number_format($summary['this_month']['settled'], 2) }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="card summary-widget shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="widget-icon bg-info bg-opacity-10 me-3">
                            <i class="fas fa-receipt fa-lg text-info"></i>
                        </div>
                        <div>
                            <div class="text-muted small">@lang('station.financials.total_transactions')</div>
                            <div class="fs-5 fw-bold text-info">{{ number_format($summary['counts']['transactions']) }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="card summary-widget shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="widget-icon bg-warning bg-opacity-10 me-3">
                            <i class="fas fa-file-invoice-dollar fa-lg text-warning"></i>
                        </div>
                        <div>
                            <div class="text-muted small">@lang('station.financials.last_settlement')</div>
                            @if($summary['last_settlement'])
                                <div class="fs-6 fw-bold">{{ number_format($summary['last_settlement']['amount'], 2) }}</div>
                                <div class="text-muted small">{{ $summary['last_settlement']['date'] }}</div>
                            @else
                                <div class="text-muted">@lang('station.financials.no_settlements')</div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Tabs --}}
    <div class="card shadow-sm">
        <div class="card-header bg-white">
            <ul class="nav nav-tabs card-header-tabs" role="tablist">
                <li class="nav-item">
                    <a class="nav-link active" data-bs-toggle="tab" href="#settlementsTab" role="tab">
                        <i class="fas fa-money-check-alt me-2"></i>@lang('station.financials.settlements_history')
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" data-bs-toggle="tab" href="#transactionsTab" role="tab">
                        <i class="fas fa-exchange-alt me-2"></i>@lang('station.financials.transactions')
                    </a>
                </li>
            </ul>
        </div>
        <div class="card-body">
            <div class="tab-content">
                {{-- Settlements Tab --}}
                <div class="tab-pane fade show active" id="settlementsTab" role="tabpanel">
                    <div class="row mb-3">
                        <div class="col-md-3">
                            <input type="text" class="form-control form-control-sm" id="settlementDateFrom" placeholder="@lang('station.financials.date_from')">
                        </div>
                        <div class="col-md-3">
                            <input type="text" class="form-control form-control-sm" id="settlementDateTo" placeholder="@lang('station.financials.date_to')">
                        </div>
                        <div class="col-md-2">
                            <button type="button" class="btn btn-station btn-sm" id="filterSettlements">
                                <i class="fas fa-search"></i>
                            </button>
                            <button type="button" class="btn btn-outline-secondary btn-sm" id="resetSettlementFilters">
                                <i class="fas fa-undo"></i>
                            </button>
                        </div>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-hover" id="settlementsTable">
                            <thead class="table-light">
                                <tr>
                                    <th>@lang('station.financials.settlement_date')</th>
                                    <th>@lang('station.financials.reference')</th>
                                    <th class="text-end">@lang('station.financials.amount')</th>
                                    <th>@lang('station.financials.payment_method')</th>
                                    <th>@lang('station.financials.settled_by')</th>
                                    <th class="text-center">@lang('station.financials.receipt')</th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>
                </div>

                {{-- Transactions Tab --}}
                <div class="tab-pane fade" id="transactionsTab" role="tabpanel">
                    <div class="row mb-3">
                        <div class="col-md-3">
                            <input type="text" class="form-control form-control-sm" id="txDateFrom" placeholder="@lang('station.financials.date_from')">
                        </div>
                        <div class="col-md-3">
                            <input type="text" class="form-control form-control-sm" id="txDateTo" placeholder="@lang('station.financials.date_to')">
                        </div>
                        <div class="col-md-2">
                            <button type="button" class="btn btn-station btn-sm" id="filterTransactions">
                                <i class="fas fa-search"></i>
                            </button>
                            <button type="button" class="btn btn-outline-secondary btn-sm" id="resetTxFilters">
                                <i class="fas fa-undo"></i>
                            </button>
                        </div>
                        <div class="col-md-4 text-end">
                            <div class="d-inline-block month-summary px-3 py-2">
                                <span class="text-muted small">@lang('station.financials.filtered_total'):</span>
                                <span class="fw-bold text-success" id="filteredTxTotal">0.00</span>
                                <span class="text-muted small">@lang('station.currency')</span>
                            </div>
                        </div>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-hover" id="transactionsTable">
                            <thead class="table-light">
                                <tr>
                                    <th>@lang('station.financials.date')</th>
                                    <th>@lang('station.financials.reference')</th>
                                    <th>@lang('station.financials.vehicle')</th>
                                    <th>@lang('station.financials.client')</th>
                                    <th>@lang('station.financials.worker')</th>
                                    <th class="text-end">@lang('station.financials.liters')</th>
                                    <th class="text-end">@lang('station.financials.amount')</th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Receipt Modal --}}
<div class="modal fade" id="receiptModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-station text-white">
                <h5 class="modal-title">
                    <i class="fas fa-file-invoice-dollar me-2"></i>@lang('station.financials.settlement_receipt')
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-5">
                        <h6 class="text-station border-bottom pb-2 mb-3">
                            <i class="fas fa-info-circle me-2"></i>@lang('station.financials.settlement_details')
                        </h6>
                        <table class="table table-sm table-borderless">
                            <tr>
                                <td class="text-muted">@lang('station.financials.reference'):</td>
                                <td class="fw-bold" id="receiptReference">-</td>
                            </tr>
                            <tr>
                                <td class="text-muted">@lang('station.financials.date'):</td>
                                <td id="receiptDate">-</td>
                            </tr>
                            <tr>
                                <td class="text-muted">@lang('station.financials.amount'):</td>
                                <td class="fw-bold text-success fs-5" id="receiptAmount">-</td>
                            </tr>
                            <tr>
                                <td class="text-muted">@lang('station.financials.payment_method'):</td>
                                <td id="receiptPaymentMethod">-</td>
                            </tr>
                            <tr>
                                <td class="text-muted">@lang('station.financials.settled_by'):</td>
                                <td id="receiptAdmin">-</td>
                            </tr>
                            <tr>
                                <td class="text-muted">@lang('station.financials.details'):</td>
                                <td id="receiptDetails">-</td>
                            </tr>
                        </table>
                    </div>
                    <div class="col-md-7">
                        <h6 class="text-station border-bottom pb-2 mb-3">
                            <i class="fas fa-image me-2"></i>@lang('station.financials.receipt_image')
                        </h6>
                        <div class="text-center" id="receiptImageContainer">
                            <div class="text-muted py-5" id="noReceiptPlaceholder">
                                <i class="fas fa-image fa-4x mb-3 text-muted"></i>
                                <p>@lang('station.financials.no_receipt_image')</p>
                            </div>
                            <img src="" alt="Receipt" class="receipt-image d-none" id="receiptImage">
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

    flatpickr('#settlementDateFrom', { dateFormat: 'Y-m-d', maxDate: 'today' });
    flatpickr('#settlementDateTo', { dateFormat: 'Y-m-d', maxDate: 'today' });
    flatpickr('#txDateFrom', { dateFormat: 'Y-m-d', maxDate: 'today' });
    flatpickr('#txDateTo', { dateFormat: 'Y-m-d', maxDate: 'today' });

    var settlementsTable = $('#settlementsTable').DataTable({
        processing: true,
        serverSide: false,
        ajax: {
            url: '{{ route("station.financials.settlements") }}',
            data: function(d) {
                d.date_from = $('#settlementDateFrom').val();
                d.date_to = $('#settlementDateTo').val();
            },
            dataSrc: 'data'
        },
        columns: [
            { data: 'date' },
            { 
                data: 'reference_no',
                render: function(data) {
                    return '<code class="small">' + data + '</code>';
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
                data: 'payment_method',
                render: function(data, type, row) {
                    var badges = {
                        'cash': 'success',
                        'bank_transfer': 'primary',
                        'check': 'warning'
                    };
                    return '<span class="badge payment-badge bg-' + (badges[data] || 'secondary') + '">' + row.payment_label + '</span>';
                }
            },
            { data: 'admin' },
            {
                data: 'id',
                className: 'text-center',
                orderable: false,
                render: function(data, type, row) {
                    if (row.has_receipt) {
                        return '<button type="button" class="btn btn-sm btn-outline-station btn-view-receipt" data-id="' + data + '" title="@lang("station.financials.view_receipt")"><i class="fas fa-file-image"></i></button>';
                    }
                    return '<span class="text-muted">-</span>';
                }
            }
        ],
        order: [[0, 'desc']],
        language: {
            url: window.is_ar ? '//cdn.datatables.net/plug-ins/1.13.4/i18n/ar.json' : ''
        }
    });

    var transactionsTable = $('#transactionsTable').DataTable({
        processing: true,
        serverSide: false,
        ajax: {
            url: '{{ route("station.financials.transactions") }}',
            data: function(d) {
                d.date_from = $('#txDateFrom').val();
                d.date_to = $('#txDateTo').val();
            },
            dataSrc: function(json) {
                if (json.summary) {
                    $('#filteredTxTotal').text(parseFloat(json.summary.total_amount || 0).toFixed(2));
                }
                return json.data;
            }
        },
        columns: [
            { data: 'date' },
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
            }
        ],
        order: [[0, 'desc']],
        pageLength: 25,
        language: {
            url: window.is_ar ? '//cdn.datatables.net/plug-ins/1.13.4/i18n/ar.json' : ''
        }
    });

    $('#filterSettlements').on('click', function() {
        settlementsTable.ajax.reload();
    });

    $('#resetSettlementFilters').on('click', function() {
        $('#settlementDateFrom').val('');
        $('#settlementDateTo').val('');
        settlementsTable.ajax.reload();
    });

    $('#filterTransactions').on('click', function() {
        transactionsTable.ajax.reload();
    });

    $('#resetTxFilters').on('click', function() {
        $('#txDateFrom').val('');
        $('#txDateTo').val('');
        transactionsTable.ajax.reload();
    });

    $('a[data-bs-toggle="tab"]').on('shown.bs.tab', function(e) {
        if ($(e.target).attr('href') === '#transactionsTab') {
            transactionsTable.columns.adjust().draw(false);
        } else {
            settlementsTable.columns.adjust().draw(false);
        }
    });

    $(document).on('click', '.btn-view-receipt', function() {
        var id = $(this).data('id');

        $('#receiptReference').text('-');
        $('#receiptDate').text('-');
        $('#receiptAmount').text('-');
        $('#receiptPaymentMethod').text('-');
        $('#receiptAdmin').text('-');
        $('#receiptDetails').text('-');
        $('#receiptImage').addClass('d-none').attr('src', '');
        $('#noReceiptPlaceholder').removeClass('d-none');

        $.ajax({
            url: '{{ route("station.financials.receipt", ":id") }}'.replace(':id', id),
            method: 'GET',
            success: function(res) {
                if (res.success) {
                    var data = res.data;
                    $('#receiptReference').text(data.reference_no);
                    $('#receiptDate').text(data.date);
                    $('#receiptAmount').text(parseFloat(data.amount).toFixed(2) + ' ' + currency);
                    $('#receiptPaymentMethod').text(data.payment_method);
                    $('#receiptAdmin').text(data.admin);
                    $('#receiptDetails').text(data.details || '-');

                    if (data.has_receipt && data.receipt_url) {
                        $('#receiptImage').attr('src', data.receipt_url).removeClass('d-none');
                        $('#noReceiptPlaceholder').addClass('d-none');
                    }

                    $('#receiptModal').modal('show');
                }
            },
            error: function() {
                Swal.fire({
                    icon: 'error',
                    title: '@lang("layouts.error")',
                    text: '@lang("station.financials.load_error")'
                });
            }
        });
    });
});
</script>
@endpush
