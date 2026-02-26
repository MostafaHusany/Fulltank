@extends('layouts.admin.app')

@push('title')
    <h1 class="h2">@lang('vehicle_quotas.Title')</h1>
@endpush

@section('content')
    <div id="quotaCard" class="card">
        <div class="card-header">
            <div class="row g-2 align-items-center">
                <div class="col-12 col-md-6">
                    <label class="form-label small mb-0 me-2">@lang('vehicle_quotas.Select Client')</label>
                    <select id="client_select" class="form-select form-select-sm" style="width: 280px; display: inline-block;">
                        <option value="">— @lang('vehicle_quotas.Select Client') —</option>
                    </select>
                </div>
                <div class="col-12 col-md-6 text-end">
                    <button type="button" class="btn btn-sm btn-outline-dark" id="reloadVehicles" title="@lang('clients.object_updated')" style="display: none;">
                        <i class="fas fa-sync-alt"></i>
                    </button>
                </div>
            </div>
        </div>

        <div id="bulkBar" class="card-footer bg-light py-2" style="display: none;">
            <div class="row g-2 align-items-center">
                <div class="col-auto">
                    <span class="small text-muted" id="bulkCount">0</span> @lang('vehicle_quotas.vehicles_selected')
                </div>
                <div class="col-auto">
                    <input type="number" step="0.01" min="0" class="form-control form-control-sm" id="bulkAmount" placeholder="@lang('vehicle_quotas.Amount')" style="width: 120px; display: inline-block;">
                </div>
                <div class="col-auto">
                    <select class="form-select form-select-sm" id="bulkCycle" style="width: 160px; display: inline-block;">
                        <option value="daily">@lang('vehicle_quotas.daily')</option>
                        <option value="weekly">@lang('vehicle_quotas.weekly')</option>
                        <option value="monthly">@lang('vehicle_quotas.monthly')</option>
                        <option value="one_time">@lang('vehicle_quotas.one_time')</option>
                    </select>
                </div>
                <div class="col-auto">
                    <button type="button" class="btn btn-sm btn-primary" id="bulkApply">@lang('vehicle_quotas.Apply')</button>
                </div>
            </div>
        </div>

        <div class="card-body custome-table p-2 p-md-3">
            <div id="tablePlaceholder" class="text-center text-muted py-5">
                @lang('vehicle_quotas.select_client_first')
            </div>
            <div id="tableLoading" class="text-center py-5" style="display: none;">
                <div class="spinner-border text-primary" role="status"></div>
            </div>
            <div id="tableContainer" class="table-responsive" style="display: none;">
                <table id="quotaTable" class="table table-sm table-hover text-center mb-0">
                    <thead>
                        <tr>
                            <th><input type="checkbox" id="selectAllQuotas" class="form-check-input"></th>
                            <th>#</th>
                            <th>@lang('vehicle_quotas.Vehicle Plate')</th>
                            <th>@lang('vehicle_quotas.Model')</th>
                            <th>@lang('vehicle_quotas.Current Limit')</th>
                            <th>@lang('vehicle_quotas.Consumed')</th>
                            <th>@lang('vehicle_quotas.Remaining')</th>
                            <th>@lang('vehicle_quotas.Cycle Type')</th>
                            <th>@lang('vehicle_quotas.Status')</th>
                            <th>@lang('layouts.Actions')</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="modal fade" id="editQuotaModal" tabindex="-1">
        <div class="modal-dialog modal-sm">
            <div class="modal-content">
                <div class="modal-header py-2">
                    <h6 class="modal-title">@lang('vehicle_quotas.Edit')</h6>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" id="editVehicleId">
                    <input type="hidden" id="editClientId">
                    <div class="mb-2">
                        <label class="form-label small">@lang('vehicle_quotas.Amount')</label>
                        <input type="number" step="0.01" min="0" class="form-control form-control-sm" id="editAmount">
                    </div>
                    <div class="mb-2">
                        <label class="form-label small">@lang('vehicle_quotas.Cycle')</label>
                        <select class="form-select form-select-sm" id="editCycle">
                            <option value="daily">@lang('vehicle_quotas.daily')</option>
                            <option value="weekly">@lang('vehicle_quotas.weekly')</option>
                            <option value="monthly">@lang('vehicle_quotas.monthly')</option>
                            <option value="one_time">@lang('vehicle_quotas.one_time')</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer py-2">
                    <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">@lang('layouts.Close')</button>
                    <button type="button" class="btn btn-sm btn-primary" id="editSave">@lang('layouts.Update')</button>
                </div>
            </div>
        </div>
    </div>
@endSection

@push('custome-js')
<script>
(function () {
    const ROUTES = {
        clients   : "{{ route('admin.search.clients') }}",
        vehicles  : "{{ route('admin.vehicleQuotas.vehicles') }}",
        update    : "{{ route('admin.vehicleQuotas.update', ['id' => 'ID']) }}",
        bulk      : "{{ route('admin.vehicleQuotas.bulk') }}"
    };

    const LANG = {
        selectClientFirst : '{{ __("vehicle_quotas.select_client_first") }}',
        selectVehicles    : '{{ __("vehicle_quotas.select_vehicles") }}',
        amountRequired    : '{{ __("vehicle_quotas.amount_required") }}',
        selectClient      : '{{ __("vehicle_quotas.Select Client") }}',
        noVehicles        : '{{ __("vehicle_quotas.no_vehicles") }}',
        daily             : '{{ __("vehicle_quotas.daily") }}',
        weekly            : '{{ __("vehicle_quotas.weekly") }}',
        monthly           : '{{ __("vehicle_quotas.monthly") }}',
        oneTime           : '{{ __("vehicle_quotas.one_time") }}'
    };

    const CYCLE_MAP = { daily: LANG.daily, weekly: LANG.weekly, monthly: LANG.monthly, one_time: LANG.oneTime };

    let currentClientId = null;
    let vehiclesData = [];

    function renderProgressBar(limit, consumed) {
        if (!limit || limit <= 0) return '<span class="text-muted">—</span>';
        var pct = Math.min(100, (consumed / limit) * 100);
        var cls = pct >= 100 ? 'danger' : (pct >= 80 ? 'warning' : 'success');
        return '<div class="progress" style="height: 18px; min-width: 60px;"><div class="progress-bar bg-' + cls + '" style="width: ' + pct + '%">' + consumed.toFixed(0) + ' / ' + limit.toFixed(0) + '</div></div>';
    }

    function buildRow(idx, v) {
        var remaining = v.remaining || 0;
        return '<tr data-vehicle-id="' + v.id + '">' +
            '<td><input type="checkbox" class="form-check-input record-selector quota-selector" value="' + v.id + '"></td>' +
            '<td>' + idx + '</td>' +
            '<td>' + (v.plate_number || '—') + '</td>' +
            '<td>' + (v.model || '—') + '</td>' +
            '<td>' + (v.amount_limit || 0) + '</td>' +
            '<td>' + (v.consumed_amount || 0) + '</td>' +
            '<td class="text-start">' + renderProgressBar(v.amount_limit, v.consumed_amount) + '</td>' +
            '<td><span class="badge bg-secondary">' + (CYCLE_MAP[v.reset_cycle] || v.reset_cycle) + '</span></td>' +
            '<td>' + (v.is_active ? '<span class="badge bg-success">@lang("layouts.active")</span>' : '<span class="badge bg-secondary">@lang("layouts.de-active")</span>') + '</td>' +
            '<td><button type="button" class="btn btn-sm btn-outline-primary edit-quota-btn" data-id="' + v.id + '" data-amount="' + (v.amount_limit || 0) + '" data-cycle="' + (v.reset_cycle || 'one_time') + '"><i class="fas fa-edit"></i></button></td>' +
            '</tr>';
    }

    function loadVehicles(clientId) {
        if (!clientId) {
            $('#tablePlaceholder').show().html(LANG.selectClientFirst);
            $('#tableLoading').hide();
            $('#tableContainer').hide();
            $('#reloadVehicles').hide();
            return;
        }
        currentClientId = clientId;
        $('#tablePlaceholder').hide();
        $('#tableLoading').show();
        $('#tableContainer').hide();

        axios.get(ROUTES.vehicles, { params: { client_id: clientId } }).then(function (r) {
            $('#tableLoading').hide();
            if (r.data.success && Array.isArray(r.data.data)) {
                vehiclesData = r.data.data;
                var tbody = $('#quotaTable tbody');
                tbody.empty();
                if (vehiclesData.length === 0) {
                    $('#tablePlaceholder').show().html(LANG.noVehicles || '@lang("vehicle_quotas.no_vehicles")');
                } else {
                    $('#tableContainer').show();
                    vehiclesData.forEach(function (v, i) {
                        tbody.append(buildRow(i + 1, v));
                    });
                    $('#reloadVehicles').show();
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
        }
        var idx = vehiclesData.findIndex(function (x) { return x.id == vehicleId; }) + 1;
        var row = $('#quotaTable tbody tr[data-vehicle-id="' + vehicleId + '"]');
        if (row.length && v) row.replaceWith(buildRow(idx, v));
    }

    $(document).ready(function () {
        var clientsOpts = {
            allowClear: true,
            width: '280px',
            placeholder: LANG.selectClient,
            ajax: {
                url: ROUTES.clients,
                dataType: 'json',
                delay: 250,
                data: function (p) { return { q: p.term || '' }; },
                processResults: function (data) {
                    var arr = Array.isArray(data) ? data : (data && data.results ? data.results : []);
                    return {
                        results: arr.map(function (x) {
                            return { id: x.id, text: (x.company_name || x.name) + (x.phone ? ' - ' + x.phone : '') };
                        })
                    };
                },
                cache: true
            },
            minimumInputLength: 0
        };

        $('#client_select').select2(clientsOpts).on('change', function () {
            var id = $(this).val();
            loadVehicles(id || null);
        });

        $('#reloadVehicles').on('click', function () {
            if (currentClientId) loadVehicles(currentClientId);
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
            if (!ids.length) { failerToast(LANG.selectVehicles); return; }
            if (isNaN(amount) || amount < 0) { failerToast(LANG.amountRequired); return; }

            axios.post(ROUTES.bulk, {
                _token: $('meta[name="csrf-token"]').attr('content'),
                client_id: currentClientId,
                vehicle_ids: ids,
                amount_limit: amount,
                reset_cycle: cycle
            }).then(function (r) {
                if (r.data.success) {
                    successToast(r.data.msg);
                    if (currentClientId) loadVehicles(currentClientId);
                } else {
                    failerToast(Array.isArray(r.data.msg) ? (r.data.msg[0] || 'Error') : (r.data.msg || 'Error'));
                }
            }).catch(function (e) {
                var m = e.response?.data?.msg;
                failerToast(Array.isArray(m) ? (m[0] || 'Error') : (m || 'Error'));
            });
        });

        $(document).on('click', '.edit-quota-btn', function () {
            var id = $(this).data('id');
            var amount = $(this).data('amount');
            var cycle = $(this).data('cycle');
            $('#editVehicleId').val(id);
            $('#editClientId').val(currentClientId);
            $('#editAmount').val(amount);
            $('#editCycle').val(cycle);
            new bootstrap.Modal(document.getElementById('editQuotaModal')).show();
        });

        $('#editSave').on('click', function () {
            var id = $('#editVehicleId').val();
            var clientId = $('#editClientId').val();
            var amount = parseFloat($('#editAmount').val());
            var cycle = $('#editCycle').val();
            if (isNaN(amount) || amount < 0) { failerToast(LANG.amountRequired); return; }

            axios.put(ROUTES.update.replace('ID', id), {
                _token: $('meta[name="csrf-token"]').attr('content'),
                _method: 'PUT',
                client_id: clientId,
                amount_limit: amount,
                reset_cycle: cycle
            }).then(function (r) {
                if (r.data.success) {
                    successToast(r.data.msg);
                    updateRowInPlace(parseInt(id), r.data.data);
                    bootstrap.Modal.getInstance(document.getElementById('editQuotaModal')).hide();
                } else {
                    failerToast(Array.isArray(r.data.msg) ? (r.data.msg[0] || 'Error') : (r.data.msg || 'Error'));
                }
            }).catch(function (e) {
                var m = e.response?.data?.msg;
                failerToast(Array.isArray(m) ? (m[0] || 'Error') : (m || 'Error'));
            });
        });
    });
})();
</script>
@endpush
