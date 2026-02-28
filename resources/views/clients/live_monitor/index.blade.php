@extends('layouts.clients.app')

@push('title')
    <h1 class="h2 d-flex align-items-center">
        @lang('client.live_monitor.title')
        <span class="live-badge ms-2">
            <span class="live-dot"></span>
            <span class="live-text">LIVE</span>
        </span>
    </h1>
@endpush

@push('custome-plugin')
    <style>
        .live-badge {
            display: inline-flex;
            align-items: center;
            background: #dc3545;
            color: white;
            padding: 0.25rem 0.6rem;
            border-radius: 20px;
            font-size: 0.7rem;
            font-weight: 600;
        }
        .live-dot {
            width: 8px;
            height: 8px;
            background: white;
            border-radius: 50%;
            margin-right: 6px;
            animation: blink 1s infinite;
        }
        @keyframes blink {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.3; }
        }
        .stat-card {
            border-radius: 16px;
            transition: all 0.3s ease;
            border: none;
            overflow: hidden;
        }
        .stat-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 25px rgba(0,0,0,0.1);
        }
        .stat-card .stat-icon {
            width: 50px;
            height: 50px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
        }
        .stat-card .stat-value {
            font-size: 1.5rem;
            font-weight: 700;
            line-height: 1.2;
        }
        .stat-card .stat-label {
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .feed-card {
            border-radius: 16px;
            border: none;
        }
        .last-updated {
            font-size: 0.8rem;
            color: #6c757d;
        }
        .status-completed { color: #198754; }
        .status-pending { color: #ffc107; }
        .status-refunded { color: #0dcaf0; }
        .proof-btn {
            padding: 0.25rem 0.5rem;
            font-size: 0.75rem;
        }
        .proof-image-container {
            max-height: 400px;
            overflow: hidden;
            border-radius: 12px;
            background: #f8f9fa;
        }
        .proof-image-container img {
            width: 100%;
            height: auto;
            object-fit: contain;
        }
        .transaction-detail {
            padding: 0.5rem 0;
            border-bottom: 1px solid #eee;
        }
        .transaction-detail:last-child {
            border-bottom: none;
        }
        .no-image-placeholder {
            height: 200px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #f8f9fa;
            border-radius: 12px;
            color: #adb5bd;
        }
    </style>
@endpush

@section('content')
<div class="container-fluid px-0">

    {{-- Today's Stats Widgets --}}
    <div class="row mb-4 g-3">
        {{-- Total Liters Today --}}
        <div class="col-6 col-lg-3">
            <div class="card stat-card shadow-sm h-100" style="background: linear-gradient(135deg, #0d6efd 0%, #6610f2 100%);">
                <div class="card-body text-white">
                    <div class="d-flex align-items-center mb-2">
                        <div class="stat-icon bg-white bg-opacity-25">
                            <i class="fas fa-gas-pump text-white"></i>
                        </div>
                    </div>
                    <p class="stat-label text-white-50 mb-1">@lang('client.live_monitor.liters_today')</p>
                    <h3 class="stat-value mb-0" id="statLiters">{{ number_format($litersToday, 2) }}</h3>
                    <small class="text-white-50">@lang('client.live_monitor.liters_unit')</small>
                </div>
            </div>
        </div>

        {{-- Active Drivers --}}
        <div class="col-6 col-lg-3">
            <div class="card stat-card shadow-sm h-100" style="background: linear-gradient(135deg, #198754 0%, #20c997 100%);">
                <div class="card-body text-white">
                    <div class="d-flex align-items-center mb-2">
                        <div class="stat-icon bg-white bg-opacity-25">
                            <i class="fas fa-users text-white"></i>
                        </div>
                    </div>
                    <p class="stat-label text-white-50 mb-1">@lang('client.live_monitor.active_drivers')</p>
                    <h3 class="stat-value mb-0" id="statDrivers">{{ $activeDrivers }}</h3>
                    <small class="text-white-50">@lang('client.live_monitor.drivers_unit')</small>
                </div>
            </div>
        </div>

        {{-- Spent Today --}}
        <div class="col-6 col-lg-3">
            <div class="card stat-card shadow-sm h-100" style="background: linear-gradient(135deg, #dc3545 0%, #fd7e14 100%);">
                <div class="card-body text-white">
                    <div class="d-flex align-items-center mb-2">
                        <div class="stat-icon bg-white bg-opacity-25">
                            <i class="fas fa-coins text-white"></i>
                        </div>
                    </div>
                    <p class="stat-label text-white-50 mb-1">@lang('client.live_monitor.spent_today')</p>
                    <h3 class="stat-value mb-0" id="statSpent">{{ number_format($spentToday, 2) }}</h3>
                    <small class="text-white-50">@lang('client.currency')</small>
                </div>
            </div>
        </div>

        {{-- Transactions Count --}}
        <div class="col-6 col-lg-3">
            <div class="card stat-card shadow-sm h-100" style="background: linear-gradient(135deg, #17a2b8 0%, #20c997 100%);">
                <div class="card-body text-white">
                    <div class="d-flex align-items-center mb-2">
                        <div class="stat-icon bg-white bg-opacity-25">
                            <i class="fas fa-receipt text-white"></i>
                        </div>
                    </div>
                    <p class="stat-label text-white-50 mb-1">@lang('client.live_monitor.transactions_today')</p>
                    <h3 class="stat-value mb-0" id="statTransactions">{{ $transactionsCount }}</h3>
                    <small class="text-white-50">@lang('client.live_monitor.transactions_unit')</small>
                </div>
            </div>
        </div>
    </div>

    {{-- Live Feed Table --}}
    <div class="card feed-card shadow-sm">
        <div class="card-header bg-white d-flex justify-content-between align-items-center py-3">
            <h5 class="mb-0">
                <i class="fas fa-stream me-2 text-primary"></i>@lang('client.live_monitor.live_feed')
            </h5>
            <div class="d-flex align-items-center gap-3">
                <span class="last-updated">
                    <i class="fas fa-sync-alt me-1"></i>
                    @lang('client.live_monitor.last_updated'): <span id="lastUpdated">--:--:--</span>
                </span>
                <button type="button" class="btn btn-outline-primary btn-sm" id="refreshBtn">
                    <i class="fas fa-refresh me-1"></i>@lang('client.live_monitor.refresh')
                </button>
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle" id="liveTable">
                    <thead class="table-light">
                        <tr>
                            <th>@lang('client.live_monitor.time')</th>
                            <th>@lang('client.live_monitor.vehicle')</th>
                            <th>@lang('client.live_monitor.driver')</th>
                            <th>@lang('client.live_monitor.station')</th>
                            <th>@lang('client.live_monitor.fuel_type')</th>
                            <th>@lang('client.live_monitor.liters')</th>
                            <th>@lang('client.live_monitor.amount')</th>
                            <th>@lang('client.live_monitor.status')</th>
                            <th>@lang('layouts.Actions')</th>
                        </tr>
                    </thead>
                    <tbody id="liveTableBody">
                        <tr>
                            <td colspan="9" class="text-center text-muted py-4">
                                <i class="fas fa-spinner fa-spin me-2"></i>@lang('client.live_monitor.loading')
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</div>

{{-- Proof Modal --}}
<div class="modal fade" id="proofModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content" style="border-radius: 16px; border: none;">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title">
                    <i class="fas fa-camera me-2 text-primary"></i>@lang('client.live_monitor.transaction_proof')
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div id="proofLoader" class="text-center py-5">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                </div>
                <div id="proofContent" style="display: none;">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <div class="proof-image-container" id="proofImageContainer">
                                <img src="" alt="Meter Photo" id="proofImage">
                            </div>
                            <div class="no-image-placeholder" id="noImagePlaceholder" style="display: none;">
                                <div class="text-center">
                                    <i class="fas fa-image fa-3x mb-2"></i>
                                    <p class="mb-0">@lang('client.live_monitor.no_image')</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <h6 class="text-muted mb-3">@lang('client.live_monitor.transaction_details')</h6>
                            <div class="transaction-detail">
                                <small class="text-muted">@lang('client.live_monitor.reference')</small>
                                <div class="fw-bold" id="proofReference">-</div>
                            </div>
                            <div class="transaction-detail">
                                <small class="text-muted">@lang('client.live_monitor.date_time')</small>
                                <div class="fw-bold" id="proofDateTime">-</div>
                            </div>
                            <div class="transaction-detail">
                                <small class="text-muted">@lang('client.live_monitor.vehicle')</small>
                                <div class="fw-bold" id="proofVehicle">-</div>
                            </div>
                            <div class="transaction-detail">
                                <small class="text-muted">@lang('client.live_monitor.driver')</small>
                                <div class="fw-bold" id="proofDriver">-</div>
                            </div>
                            <div class="transaction-detail">
                                <small class="text-muted">@lang('client.live_monitor.station')</small>
                                <div class="fw-bold" id="proofStation">-</div>
                            </div>
                            <div class="transaction-detail">
                                <small class="text-muted">@lang('client.live_monitor.fuel_type')</small>
                                <div class="fw-bold" id="proofFuelType">-</div>
                            </div>
                            <div class="row">
                                <div class="col-6">
                                    <div class="transaction-detail">
                                        <small class="text-muted">@lang('client.live_monitor.liters')</small>
                                        <div class="fw-bold text-primary" id="proofLiters">-</div>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="transaction-detail">
                                        <small class="text-muted">@lang('client.live_monitor.amount')</small>
                                        <div class="fw-bold text-danger" id="proofAmount">-</div>
                                    </div>
                                </div>
                            </div>
                            <div id="proofMapLink" class="mt-3" style="display: none;">
                                <a href="#" target="_blank" class="btn btn-outline-primary btn-sm w-100" id="proofMapBtn">
                                    <i class="fas fa-map-marker-alt me-2"></i>@lang('client.live_monitor.view_on_map')
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer border-0 pt-0">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">@lang('layouts.close')</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('custome-js')
<script>
(function() {
    var REFRESH_INTERVAL = 60000;
    var refreshTimer;
    var currency = '@lang('client.currency')';

    var LANG = {
        noTransactions: '@lang('client.live_monitor.no_transactions')',
        viewProof: '@lang('client.live_monitor.view_proof')',
        completed: '@lang('client.wallet.status_completed')',
        pending: '@lang('client.wallet.status_pending')',
        refunded: '@lang('client.wallet.status_refunded')'
    };

    function getStatusBadge(status) {
        var colors = {
            'completed': 'success',
            'pending': 'warning',
            'refunded': 'info',
            'cancelled': 'secondary'
        };
        var color = colors[status] || 'secondary';
        var label = LANG[status] || status;
        return '<span class="badge bg-' + color + '">' + label + '</span>';
    }

    function buildRow(tx) {
        var proofBtn = '<button class="btn btn-outline-primary proof-btn" onclick="viewProof(' + tx.id + ')">' +
            '<i class="fas fa-eye me-1"></i>' + LANG.viewProof + '</button>';

        return '<tr>' +
            '<td><span class="fw-semibold">' + tx.time + '</span></td>' +
            '<td>' + tx.vehicle + '</td>' +
            '<td>' + tx.driver + '</td>' +
            '<td>' + tx.station + '</td>' +
            '<td>' + tx.fuel_type + '</td>' +
            '<td>' + tx.liters + '</td>' +
            '<td class="fw-bold">' + tx.amount + ' ' + currency + '</td>' +
            '<td>' + getStatusBadge(tx.status) + '</td>' +
            '<td>' + proofBtn + '</td>' +
            '</tr>';
    }

    function loadTransactions() {
        $.ajax({
            url: '{{ route("client.live_monitor.transactions") }}',
            method: 'GET',
            success: function(res) {
                var tbody = $('#liveTableBody');
                tbody.empty();

                if (res.transactions.length === 0) {
                    tbody.html('<tr><td colspan="9" class="text-center text-muted py-4">' +
                        '<i class="fas fa-inbox fa-2x mb-2 d-block"></i>' + LANG.noTransactions + '</td></tr>');
                } else {
                    res.transactions.forEach(function(tx) {
                        tbody.append(buildRow(tx));
                    });
                }

                $('#lastUpdated').text(res.last_updated);
                $('#statLiters').text(parseFloat(res.stats.total_liters).toFixed(2));
                $('#statDrivers').text(res.stats.active_drivers);
                $('#statSpent').text(parseFloat(res.stats.spent_today).toFixed(2));
                $('#statTransactions').text(res.stats.transactions_count);
            },
            error: function() {
                console.error('Failed to load transactions');
            }
        });
    }

    window.viewProof = function(id) {
        var modal = new bootstrap.Modal(document.getElementById('proofModal'));
        modal.show();

        $('#proofLoader').show();
        $('#proofContent').hide();

        $.ajax({
            url: '{{ url("client/live-monitor/proof") }}/' + id,
            method: 'GET',
            success: function(res) {
                if (res.success) {
                    var tx = res.transaction;

                    $('#proofReference').text(tx.reference || '-');
                    $('#proofDateTime').text(tx.date + ' ' + tx.time);
                    $('#proofVehicle').text(tx.vehicle);
                    $('#proofDriver').text(tx.driver);
                    $('#proofStation').text(tx.station);
                    $('#proofFuelType').text(tx.fuel_type);
                    $('#proofLiters').text(tx.liters + ' L');
                    $('#proofAmount').text(tx.amount + ' ' + currency);

                    if (res.has_image && res.image_url) {
                        $('#proofImage').attr('src', res.image_url);
                        $('#proofImageContainer').show();
                        $('#noImagePlaceholder').hide();
                    } else {
                        $('#proofImageContainer').hide();
                        $('#noImagePlaceholder').show();
                    }

                    if (tx.map_url) {
                        $('#proofMapBtn').attr('href', tx.map_url);
                        $('#proofMapLink').show();
                    } else {
                        $('#proofMapLink').hide();
                    }

                    $('#proofLoader').hide();
                    $('#proofContent').show();
                }
            },
            error: function() {
                $('#proofLoader').html('<div class="text-danger"><i class="fas fa-exclamation-circle me-2"></i>Failed to load details</div>');
            }
        });
    };

    function startAutoRefresh() {
        refreshTimer = setInterval(loadTransactions, REFRESH_INTERVAL);
    }

    function stopAutoRefresh() {
        if (refreshTimer) {
            clearInterval(refreshTimer);
        }
    }

    $('#refreshBtn').on('click', function() {
        var btn = $(this);
        btn.prop('disabled', true);
        btn.find('i').addClass('fa-spin');

        loadTransactions();

        setTimeout(function() {
            btn.prop('disabled', false);
            btn.find('i').removeClass('fa-spin');
        }, 1000);
    });

    loadTransactions();
    startAutoRefresh();

    $(document).on('visibilitychange', function() {
        if (document.hidden) {
            stopAutoRefresh();
        } else {
            loadTransactions();
            startAutoRefresh();
        }
    });
})();
</script>
@endpush
