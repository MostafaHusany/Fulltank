@extends('layouts.clients.app')

@push('title')
    <h1 class="h2">@lang('client.quotas.title')</h1>
@endpush

@section('content')
    <div id="quotaCard" class="card">
        <div class="card-header">
            <div class="row g-2 align-items-center">
                <div class="col-12 col-md-6 pt-1">
                    <span class="fw-bold">@lang('client.quotas.title')</span>
                </div>
                <div class="col-12 col-md-6 text-end">
                    <button type="button" class="btn btn-sm btn-outline-dark toggle-search" title="@lang('layouts.search')">
                        <i class="fas fa-search"></i>
                    </button>
                    <button type="button" class="btn btn-sm btn-outline-dark" id="reloadVehicles" title="@lang('layouts.refresh')">
                        <i class="fas fa-sync-alt"></i>
                    </button>
                </div>
            </div>
        </div>

        <!-- Search/Filter Bar -->
        <div id="searchBar" class="card-footer bg-light py-2" style="display: none;">
            <div class="row g-2 align-items-center">
                <div class="col-12 col-sm-6 col-md-3">
                    <label class="form-label small mb-1">@lang('client.quotas.plate_number')</label>
                    <select class="form-select form-select-sm" id="s-vehicle_id">
                        <option value="">@lang('layouts.all')</option>
                        @foreach($vehiclesList as $vehicle)
                            <option value="{{ $vehicle->id }}">{{ $vehicle->plate_number }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-12 col-sm-6 col-md-3">
                    <label class="form-label small mb-1">@lang('client.quotas.fuel_type')</label>
                    <select class="form-select form-select-sm" id="s-fuel_type_id">
                        <option value="">@lang('layouts.all')</option>
                        @foreach($fuelTypes as $fuelType)
                            <option value="{{ $fuelType->id }}">{{ $fuelType->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-12 col-sm-6 col-md-3">
                    <label class="form-label small mb-1">&nbsp;</label>
                    <button type="button" class="btn btn-sm btn-outline-secondary d-block" id="clearFilters">
                        <i class="fas fa-times me-1"></i>@lang('layouts.clear')
                    </button>
                </div>
            </div>
        </div>

        <!-- Bulk Action Bar -->
        <div id="bulkBar" class="card-footer bg-primary bg-opacity-10 py-2" style="display: none;">
            <div class="row g-2 align-items-center">
                <div class="col-auto">
                    <span class="small fw-bold text-primary"><span id="bulkCount">0</span> @lang('client.quotas.vehicles_selected')</span>
                </div>
                <div class="col-auto">
                    <input type="number" step="0.01" min="0" class="form-control form-control-sm" id="bulkAmount" placeholder="@lang('client.quotas.amount')" style="width: 120px;">
                </div>
                <div class="col-auto">
                    <select class="form-select form-select-sm" id="bulkCycle" style="width: 140px;">
                        <option value="daily">@lang('client.quotas.daily')</option>
                        <option value="weekly">@lang('client.quotas.weekly')</option>
                        <option value="monthly" selected>@lang('client.quotas.monthly')</option>
                        <option value="one_time">@lang('client.quotas.one_time')</option>
                    </select>
                </div>
                <div class="col-auto">
                    <button type="button" class="btn btn-sm btn-primary" id="bulkApply">
                        <i class="fas fa-check me-1"></i>@lang('client.quotas.apply')
                    </button>
                </div>
            </div>
        </div>

        <div class="card-body custome-table p-2 p-md-3">
            <div id="tableLoading" class="text-center py-5">
                <div class="spinner-border text-primary" role="status"></div>
            </div>
            <div id="tablePlaceholder" class="text-center text-muted py-5" style="display: none;">
                @lang('client.quotas.no_vehicles')
            </div>
            <div id="tableContainer" class="table-responsive" style="display: none;">
                <table id="quotaTable" class="table table-sm table-hover text-center mb-0">
                    <thead>
                        <tr>
                            <th><input type="checkbox" id="selectAllQuotas" class="form-check-input"></th>
                            <th>#</th>
                            <th>@lang('client.quotas.plate_number')</th>
                            <th>@lang('client.quotas.fuel_type')</th>
                            <th>@lang('client.quotas.drivers')</th>
                            <th>@lang('client.quotas.monthly_limit')</th>
                            <th>@lang('client.quotas.used')</th>
                            <th>@lang('client.quotas.progress')</th>
                            <th>@lang('client.quotas.cycle')</th>
                            <th>@lang('client.quotas.quota_status')</th>
                            <th>@lang('layouts.Actions')</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Edit Quota Modal -->
    <div class="modal fade" id="editQuotaModal" tabindex="-1">
        <div class="modal-dialog modal-sm modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header py-2">
                    <h6 class="modal-title">@lang('client.quotas.edit_quota')</h6>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" id="editVehicleId">
                    <div class="mb-3">
                        <label class="form-label small fw-bold" id="editPlateNumber">---</label>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small">@lang('client.quotas.amount') (@lang('client.quotas.liters'))</label>
                        <input type="number" step="0.01" min="0" class="form-control form-control-sm" id="editAmount">
                    </div>
                    <div class="mb-3">
                        <label class="form-label small">@lang('client.quotas.cycle')</label>
                        <select class="form-select form-select-sm" id="editCycle">
                            <option value="daily">@lang('client.quotas.daily')</option>
                            <option value="weekly">@lang('client.quotas.weekly')</option>
                            <option value="monthly">@lang('client.quotas.monthly')</option>
                            <option value="one_time">@lang('client.quotas.one_time')</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer py-2">
                    <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">@lang('layouts.close')</button>
                    <button type="button" class="btn btn-sm btn-primary" id="editSave">@lang('layouts.save')</button>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('custome-js')
<script>
(function () {
    const ROUTES = {
        vehicles : "{{ route('client.quotas.vehicles') }}",
        update   : "{{ route('client.quotas.index') }}",
        bulk     : "{{ route('client.quotas.bulk') }}"
    };

    const LANG = {
        selectVehicles : '@lang("client.quotas.select_vehicles")',
        amountRequired : '@lang("client.quotas.amount_limit_required")',
        noVehicles     : '@lang("client.quotas.no_vehicles")',
        daily          : '@lang("client.quotas.daily")',
        weekly         : '@lang("client.quotas.weekly")',
        monthly        : '@lang("client.quotas.monthly")',
        oneTime        : '@lang("client.quotas.one_time")',
        active         : '@lang("client.quotas.status_active")',
        inactive       : '@lang("client.quotas.status_inactive")',
        noDriver       : '@lang("client.quotas.no_driver")'
    };

    const CYCLE_MAP = { 
        daily: LANG.daily, 
        weekly: LANG.weekly, 
        monthly: LANG.monthly, 
        one_time: LANG.oneTime 
    };

    let vehiclesData = [];

    function renderProgressBar(limit, consumed) {
        if (!limit || limit <= 0) return '<span class="text-muted">—</span>';
        var pct = Math.min(100, (consumed / limit) * 100).toFixed(1);
        var cls = pct >= 90 ? 'danger' : (pct >= 70 ? 'warning' : 'success');
        return '<div class="progress" style="height: 20px; min-width: 80px;">' +
               '<div class="progress-bar bg-' + cls + '" style="width: ' + pct + '%">' + pct + '%</div></div>';
    }

    function renderDrivers(drivers) {
        if (!drivers || drivers.length === 0) {
            return '<span class="text-muted">' + LANG.noDriver + '</span>';
        }
        return drivers.map(function(name) {
            return '<span class="badge bg-info me-1">' + name + '</span>';
        }).join('');
    }

    function buildRow(idx, v) {
        var statusBadge = v.is_active 
            ? '<span class="badge bg-success">' + LANG.active + '</span>' 
            : '<span class="badge bg-secondary">' + LANG.inactive + '</span>';

        return '<tr data-vehicle-id="' + v.id + '">' +
            '<td><input type="checkbox" class="form-check-input record-selector quota-selector" value="' + v.id + '"></td>' +
            '<td>' + idx + '</td>' +
            '<td>' + (v.plate_number || '—') + '</td>' +
            '<td>' + (v.fuel_type || '—') + '</td>' +
            '<td>' + renderDrivers(v.drivers) + '</td>' +
            '<td>' + (v.amount_limit || 0).toFixed(2) + '</td>' +
            '<td>' + (v.consumed_amount || 0).toFixed(2) + '</td>' +
            '<td>' + renderProgressBar(v.amount_limit, v.consumed_amount) + '</td>' +
            '<td><span class="badge bg-secondary">' + (CYCLE_MAP[v.reset_cycle] || v.reset_cycle) + '</span></td>' +
            '<td>' + statusBadge + '</td>' +
            '<td><button type="button" class="btn btn-sm btn-outline-primary edit-quota-btn" ' +
                'data-id="' + v.id + '" ' +
                'data-plate="' + (v.plate_number || '') + '" ' +
                'data-amount="' + (v.amount_limit || 0) + '" ' +
                'data-cycle="' + (v.reset_cycle || 'monthly') + '">' +
                '<i class="fas fa-edit"></i></button></td>' +
            '</tr>';
    }

    function getFilterParams() {
        var params = {};
        var vehicleId = $('#s-vehicle_id').val();
        var fuelTypeId = $('#s-fuel_type_id').val();
        
        if (vehicleId) params.vehicle_id = vehicleId;
        if (fuelTypeId) params.fuel_type_id = fuelTypeId;
        
        return params;
    }

    function loadVehicles() {
        $('#tablePlaceholder').hide();
        $('#tableLoading').show();
        $('#tableContainer').hide();

        axios.get(ROUTES.vehicles, { params: getFilterParams() }).then(function (r) {
            $('#tableLoading').hide();
            if (r.data.success && Array.isArray(r.data.data)) {
                vehiclesData = r.data.data;
                var tbody = $('#quotaTable tbody');
                tbody.empty();
                if (vehiclesData.length === 0) {
                    $('#tablePlaceholder').show();
                } else {
                    $('#tableContainer').show();
                    vehiclesData.forEach(function (v, i) {
                        tbody.append(buildRow(i + 1, v));
                    });
                }
            } else {
                $('#tablePlaceholder').show().html(r.data.msg || 'Error');
            }
            $('#bulkBar').hide();
            $('#selectAllQuotas').prop('checked', false);
        }).catch(function (e) {
            $('#tableLoading').hide();
            $('#tablePlaceholder').show().html(e.response?.data?.msg?.[0] || 'Error');
        });
    }

    function updateRowInPlace(vehicleId, data) {
        var v = vehiclesData.find(function (x) { return x.id == vehicleId; });
        if (v && data) {
            v.amount_limit = data.amount_limit;
            v.consumed_amount = data.consumed_amount;
            v.remaining = data.remaining;
            v.reset_cycle = data.reset_cycle;
            v.is_active = true;
        }
        var idx = vehiclesData.findIndex(function (x) { return x.id == vehicleId; }) + 1;
        var row = $('#quotaTable tbody tr[data-vehicle-id="' + vehicleId + '"]');
        if (row.length && v) row.replaceWith(buildRow(idx, v));
    }

    $(document).ready(function () {
        loadVehicles();

        // Toggle search bar
        $('.toggle-search').on('click', function () {
            $('#searchBar').slideToggle(300);
        });

        // Clear filters
        $('#clearFilters').on('click', function () {
            $('#s-vehicle_id').val('');
            $('#s-fuel_type_id').val('');
            loadVehicles();
        });

        // Filter on change
        $('#s-vehicle_id, #s-fuel_type_id').on('change', function () {
            loadVehicles();
        });

        $('#reloadVehicles').on('click', function () {
            loadVehicles();
        });

        $('#selectAllQuotas').on('click', function () {
            $('.quota-selector').prop('checked', $(this).prop('checked'));
            toggleBulkBar();
        });

        $(document).on('change', '.quota-selector', toggleBulkBar);

        function toggleBulkBar() {
            var n = $('.quota-selector:checked').length;
            if (n > 0) {
                $('#bulkBar').show();
                $('#bulkCount').text(n);
            } else {
                $('#bulkBar').hide();
            }
        }

        $('#bulkApply').on('click', function () {
            var ids = $('.quota-selector:checked').map(function () { return $(this).val(); }).get();
            var amount = parseFloat($('#bulkAmount').val());
            var cycle = $('#bulkCycle').val();

            if (!ids.length) { 
                failerToast(LANG.selectVehicles); 
                return; 
            }
            if (isNaN(amount) || amount < 0) { 
                failerToast(LANG.amountRequired); 
                return; 
            }

            $(window.loddingSpinnerEl).fadeIn(500);

            axios.post(ROUTES.bulk, {
                _token: "{{ csrf_token() }}",
                vehicle_ids: ids,
                amount_limit: amount,
                reset_cycle: cycle
            }).then(function (r) {
                $(window.loddingSpinnerEl).fadeOut(500);
                if (r.data.success) {
                    successToast(r.data.msg);
                    loadVehicles();
                } else {
                    failerToast(Array.isArray(r.data.msg) ? (r.data.msg[0] || 'Error') : (r.data.msg || 'Error'));
                }
            }).catch(function (e) {
                $(window.loddingSpinnerEl).fadeOut(500);
                var m = e.response?.data?.msg;
                failerToast(Array.isArray(m) ? (m[0] || 'Error') : (m || 'Error'));
            });
        });

        $(document).on('click', '.edit-quota-btn', function () {
            var id = $(this).data('id');
            var plate = $(this).data('plate');
            var amount = $(this).data('amount');
            var cycle = $(this).data('cycle');

            $('#editVehicleId').val(id);
            $('#editPlateNumber').text(plate || '---');
            $('#editAmount').val(amount);
            $('#editCycle').val(cycle);

            new bootstrap.Modal(document.getElementById('editQuotaModal')).show();
        });

        $('#editSave').on('click', function () {
            var id = $('#editVehicleId').val();
            var amount = parseFloat($('#editAmount').val());
            var cycle = $('#editCycle').val();

            if (isNaN(amount) || amount < 0) { 
                failerToast(LANG.amountRequired); 
                return; 
            }

            $(window.loddingSpinnerEl).fadeIn(500);

            axios.put(ROUTES.update + '/' + id, {
                _token: "{{ csrf_token() }}",
                amount_limit: amount,
                reset_cycle: cycle
            }).then(function (r) {
                $(window.loddingSpinnerEl).fadeOut(500);
                if (r.data.success) {
                    successToast(r.data.msg);
                    updateRowInPlace(parseInt(id), r.data.data);
                    bootstrap.Modal.getInstance(document.getElementById('editQuotaModal')).hide();
                } else {
                    failerToast(Array.isArray(r.data.msg) ? (r.data.msg[0] || 'Error') : (r.data.msg || 'Error'));
                }
            }).catch(function (e) {
                $(window.loddingSpinnerEl).fadeOut(500);
                var m = e.response?.data?.msg;
                failerToast(Array.isArray(m) ? (m[0] || 'Error') : (m || 'Error'));
            });
        });
    });
})();
</script>
@endpush
