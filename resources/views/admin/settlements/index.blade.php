@extends('layouts.admin.app')

@push('title')
    <h1 class="h2">@lang('settlements.Title')</h1>
@endpush

@section('content')
    <div id="objectsCard" class="card mb-4">
        <div class="card-header">
            <div class="row">
                <div class="col-6 pt-1">
                    @lang('settlements.Stations Balances')
                </div>
                <div class="col-6 text-end">
                    <button class="btn btn-sm btn-outline-dark refresh-stations-btn">
                        <i class="fas fa-sync-alt"></i>
                    </button>
                </div>
            </div>
        </div>

        <div class="card-body custome-table">
            <div style="overflow-x: scroll">
                <table id="stationsTable" class="table text-center">
                    <thead>
                        <tr>
                            <th>@lang('settlements.Station')</th>
                            <th>@lang('settlements.Governorate')</th>
                            <th>@lang('settlements.Unsettled Balance')</th>
                            <th>@lang('settlements.Last Settlement')</th>
                            <th>@lang('settlements.Status')</th>
                            <th>@lang('layouts.Actions')</th>
                        </tr>
                    </thead>
                    <tbody id="stationsTableBody">
                        <tr>
                            <td colspan="6" class="text-center py-4">
                                <div class="spinner-border spinner-border-sm" role="status"></div>
                                @lang('settlements.Loading')
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div id="historyCard" class="card">
        <div class="card-header">
            <div class="row">
                <div class="col-6 pt-1">
                    @lang('settlements.Settlement History')
                </div>
                <div class="col-6 text-end">
                    <button class="relode-btn btn btn-sm btn-outline-dark">
                        <i class="relode-btn-icon fas fa-sync-alt"></i>
                        <span class="relode-btn-loader spinner-grow spinner-grow-sm" style="display: none;" role="status" aria-hidden="true"></span>
                    </button>

                    <button class="btn btn-sm btn-outline-dark toggle-search">
                        <i class="fas fa-search"></i>
                    </button>
                </div>
            </div>
        </div>

        <div class="card-body custome-table">
            @include('admin.settlements.incs._search')

            <div style="overflow-x: scroll">
                <table id="dataTable" class="table text-center">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>@lang('settlements.Reference')</th>
                            <th>@lang('settlements.Station')</th>
                            <th>@lang('settlements.Amount')</th>
                            <th>@lang('settlements.Payment Method')</th>
                            <th>@lang('settlements.Admin')</th>
                            <th>@lang('settlements.Receipt')</th>
                            <th>@lang('settlements.Date')</th>
                            <th>@lang('layouts.Actions')</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
    </div>

    @include('admin.settlements.incs._create_modal')
    @include('admin.settlements.incs._receipt_modal')
    @include('admin.settlements.incs._station_details_modal')
@endSection

@push('custome-js')
<script>
$('document').ready(function () {
    window.is_ar = '{{ $is_ar }}';

    const ROUTES = {
        index    : "{{ route('admin.settlements.index') }}",
        store    : "{{ route('admin.settlements.store') }}",
        stations : "{{ route('admin.stations.index') }}?list=true"
    };

    const objects_dynamic_table = new DynamicTable(
        {
            index_route   : ROUTES.index,
            store_route   : ROUTES.store,
            show_route    : ROUTES.index,
            update_route  : ROUTES.index,
            destroy_route : ROUTES.index,
        },
        '#dataTable',
        {
            success_el : '#successAlert',
            danger_el  : '#dangerAlert',
            warning_el : '#warningAlert'
        },
        {
            table_id        : '#dataTable',
            toggle_btn      : '.toggle-btn',
            create_obj_btn  : '.create-object',
            fields_list     : ['station_id', 'amount', 'payment_method', 'transaction_details'],
            imgs_fields     : ['receipt_image']
        },
        [
            { data: 'id',                   name: 'id' },
            { data: 'reference_no',         name: 'reference_no' },
            { data: 'station_name',         name: 'station_name' },
            { data: 'formatted_amount',     name: 'formatted_amount' },
            { data: 'payment_method_label', name: 'payment_method_label' },
            { data: 'admin_name',           name: 'admin_name' },
            { data: 'receipt_btn',          name: 'receipt_btn' },
            { data: 'created_at',           name: 'created_at' },
            { data: 'actions',              name: 'actions' },
        ],
        function (d) {
            if ($('#s-station_id').length)      d.station_id      = $('#s-station_id').val();
            if ($('#s-payment_method').length)  d.payment_method  = $('#s-payment_method').val();
            if ($('#s-reference_no').length)    d.reference_no    = $('#s-reference_no').val();
            if ($('#s-date_from').length)       d.date_from       = $('#s-date_from').val();
            if ($('#s-date_to').length)         d.date_to         = $('#s-date_to').val();
        }
    );

    function loadStationsBalances() {
        $('#stationsTableBody').html(`
            <tr>
                <td colspan="6" class="text-center py-4">
                    <div class="spinner-border spinner-border-sm" role="status"></div>
                    @lang('settlements.Loading')
                </td>
            </tr>
        `);

        axios.get(ROUTES.index + '?stations_list=true')
            .then(function (res) {
                if (!res.data.success) throw res.data.msg;

                var html = '';
                var stations = res.data.data;

                if (stations.length === 0) {
                    html = '<tr><td colspan="6" class="text-center text-muted py-4">@lang("settlements.No stations found")</td></tr>';
                } else {
                    stations.forEach(function (station) {
                        var statusBadge = '';
                        if (!station.has_wallet) {
                            statusBadge = '<span class="badge bg-secondary">@lang("settlements.No Wallet")</span>';
                        } else if (station.wallet_is_active) {
                            statusBadge = '<span class="badge bg-success">@lang("settlements.Active")</span>';
                        } else {
                            statusBadge = '<span class="badge bg-danger">@lang("settlements.Inactive")</span>';
                        }

                        var lastSettlement = station.last_settlement_date;
                        if (station.last_settlement_amount) {
                            lastSettlement += '<br><small class="text-muted">' + station.last_settlement_amount + ' EGP</small>';
                        }

                        var actionBtn = '';
                        if (!station.has_wallet) {
                            actionBtn = '<span class="text-muted">@lang("settlements.No Wallet")</span>';
                        } else if (station.wallet_is_active && station.unsettled_balance_raw > 0) {
                            actionBtn = '<button class="btn btn-sm btn-primary create-settlement-btn" ' +
                                'data-station-id="' + station.id + '" ' +
                                'data-station-name="' + station.name + '" ' +
                                'data-max-amount="' + station.unsettled_balance_raw + '" ' +
                                'data-bank-details="' + (station.bank_account_details || '').replace(/"/g, '&quot;') + '">' +
                                '<i class="fas fa-hand-holding-usd me-1"></i>@lang("settlements.Create Settlement")' +
                                '</button>';
                        } else if (!station.wallet_is_active) {
                            actionBtn = '<span class="text-muted">@lang("settlements.Wallet Inactive")</span>';
                        } else {
                            actionBtn = '<span class="text-muted">@lang("settlements.No Balance")</span>';
                        }

                        var stationDataAttr = JSON.stringify({
                            id: station.id,
                            name: station.name,
                            address: station.address,
                            phone: station.phone,
                            manager_name: station.manager_name,
                            governorate: station.governorate,
                            district: station.district,
                            bank_account_details: station.bank_account_details
                        }).replace(/'/g, '&#39;');

                        html += '<tr>' +
                            '<td><a href="javascript:void(0)" class="view-station-details text-primary text-decoration-underline" data-station=\'' + stationDataAttr + '\'>' + station.name + '</a></td>' +
                            '<td>' + station.governorate + '</td>' +
                            '<td><strong class="text-success">' + station.unsettled_balance + ' EGP</strong></td>' +
                            '<td>' + lastSettlement + '</td>' +
                            '<td>' + statusBadge + '</td>' +
                            '<td>' + actionBtn + '</td>' +
                            '</tr>';
                    });
                }

                $('#stationsTableBody').html(html);
            })
            .catch(function (err) {
                $('#stationsTableBody').html('<tr><td colspan="6" class="text-center text-danger py-4">@lang("settlements.Error loading stations")</td></tr>');
                console.error(err);
            });
    }

    loadStationsBalances();

    $(document).on('click', '.refresh-stations-btn', function () {
        loadStationsBalances();
    });

    function initSelect2() {
        $('#s-station_id').select2({
            allowClear: true,
            width: '100%',
            placeholder: "@lang('settlements.Select Station')",
            ajax: {
                url: ROUTES.stations,
                dataType: 'json',
                delay: 150,
                processResults: function (response) {
                    var items = response.data || response;
                    return {
                        results: $.map(items, function (item) {
                            return { text: item.name, id: item.id };
                        })
                    };
                },
                cache: true
            }
        });
    }

    initSelect2();

    $(document).on('click', '.create-settlement-btn', function () {
        var stationId = $(this).data('station-id');
        var stationName = $(this).data('station-name');
        var maxAmount = $(this).data('max-amount');
        var bankDetails = $(this).data('bank-details') || '';

        $('#settlement-station-name').text(stationName);
        $('#settlement-station-id').val(stationId);
        $('#settlement-max-amount').text(maxAmount.toFixed(2));
        $('#settlement-bank-details').text(bankDetails || '@lang("settlements.Not provided")');
        $('#amount').attr('max', maxAmount).val('');
        $('#payment_method').val('');
        $('#transaction_details').val('');
        $('#receipt_image').val('');

        $('#createSettlementModal').modal('show');
    });

    $(document).on('click', '#submit-settlement-btn', async function () {
        var $btn = $(this);
        $btn.attr('disabled', 'disabled');
        $(window.loddingSpinnerEl).fadeIn(500);

        var formData = new FormData();
        formData.append('_token', "{{ csrf_token() }}");
        formData.append('station_id', $('#settlement-station-id').val());
        formData.append('amount', $('#amount').val());
        formData.append('payment_method', $('#payment_method').val());
        formData.append('transaction_details', $('#transaction_details').val());

        var receiptFile = $('#receipt_image')[0].files[0];
        if (receiptFile) {
            formData.append('receipt_image', receiptFile);
        }

        try {
            var res = await axios.post(ROUTES.store, formData, {
                headers: { 'Content-Type': 'multipart/form-data' }
            });

            if (!res.data.success) throw res.data.msg;

            $('#createSettlementModal').modal('hide');
            loadStationsBalances();
            $('.relode-btn').trigger('click');
            successToast("@lang('settlements.object_created')");
        } catch (err) {
            var msg = "@lang('settlements.object_error')";
            if (typeof err === 'string') {
                msg = err;
            } else if (err.response && err.response.data && err.response.data.msg) {
                msg = typeof err.response.data.msg === 'object'
                    ? Object.values(err.response.data.msg).flat().join(', ')
                    : err.response.data.msg;
            }
            failerToast(msg);
        }

        $btn.removeAttr('disabled');
        $(window.loddingSpinnerEl).fadeOut(500);
    });

    $(document).on('click', '.view-receipt-btn', function () {
        var imageUrl = $(this).data('image-url');
        $('#receiptModal img').attr('src', imageUrl);
        $('#receiptModal').modal('show');
    });

    $(document).on('click', '.view-station-details', function () {
        var data = $(this).data('station');
        if (typeof data === 'string') data = JSON.parse(data);

        var rows = [
            { label: "@lang('settlements.Name')",                 value: data.name },
            { label: "@lang('settlements.Manager')",              value: data.manager_name },
            { label: "@lang('settlements.Phone')",                value: data.phone },
            { label: "@lang('settlements.Address')",              value: data.address },
            { label: "@lang('settlements.Governorate')",          value: data.governorate },
            { label: "@lang('settlements.District')",             value: data.district },
            { label: "@lang('settlements.Bank Account Details')", value: data.bank_account_details }
        ];

        var tbody = '';
        rows.forEach(function(row) {
            var val = row.value || '---';
            tbody += '<tr><th class="text-muted" style="width:40%">' + row.label + '</th><td>' + val + '</td></tr>';
        });

        $('#stationDetailsBody').html(tbody);
        $('#stationDetailsModal').modal('show');
    });
});
</script>
@endpush
