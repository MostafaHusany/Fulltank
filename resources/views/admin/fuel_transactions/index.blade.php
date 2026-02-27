@extends('layouts.admin.app')

@push('title')
    <h1 class="h2">@lang('fuel_transactions.Title')</h1>
@endpush

@section('content')
    <div id="objectsCard" class="card">
        <div class="card-header">
            <div class="row">
                <div class="col-6 pt-1">
                    @lang('fuel_transactions.Title Administration')
                </div>
                <div class="col-6 text-end">
                    <button class="relode-btn btn btn-sm btn-outline-dark">
                        <i class="relode-btn-icon fas fa-sync-alt"></i>
                        <span class="relode-btn-loader spinner-grow spinner-grow-sm" style="display: none;" role="status" aria-hidden="true"></span>
                    </button>

                    <button class="btn btn-sm btn-outline-dark toggle-search">
                        <i class="fas fa-search"></i>
                    </button>

                    @if($permissions == 'admin' || in_array('fuelTransactions_add', $permissions))
                    <button class="btn btn-sm btn-outline-primary toggle-btn" data-current-card="#objectsCard" data-target-card="#createObjectCard">
                        <i class="fas fa-plus"></i>
                        <span class="d-none d-md-inline mx-1">@lang('fuel_transactions.Manual Entry')</span>
                    </button>
                    @endif
                </div>
            </div>
        </div>

        <div class="card-body custome-table">
            @include('admin.fuel_transactions.incs._search')

            <div style="overflow-x: scroll">
                <table id="dataTable" class="table text-center">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>@lang('fuel_transactions.Reference')</th>
                            <th>@lang('fuel_transactions.Client')</th>
                            <th>@lang('fuel_transactions.Vehicle')</th>
                            <th>@lang('fuel_transactions.Station')</th>
                            <th>@lang('fuel_transactions.Fuel Type')</th>
                            <th>@lang('fuel_transactions.Amount')</th>
                            <th>@lang('fuel_transactions.Liters')</th>
                            <th>@lang('fuel_transactions.Status')</th>
                            <th>@lang('fuel_transactions.Image')</th>
                            <th>@lang('fuel_transactions.Processed By')</th>
                            <th>@lang('fuel_transactions.Date')</th>
                            <th>@lang('layouts.Actions')</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
    </div>

    @if($permissions == 'admin' || in_array('fuelTransactions_add', $permissions))
        @include('admin.fuel_transactions.incs._create')
    @endif

    @include('admin.fuel_transactions.incs._image_modal')
    @include('admin.fuel_transactions.incs._refund_modal')
    @include('admin.fuel_transactions.incs._details_modal')
@endSection

@push('custome-js')
<script>
$('document').ready(function () {
    window.is_ar = '{{ $is_ar }}';

    const ROUTES = {
        index     : "{{ route('admin.fuelTransactions.index') }}",
        store     : "{{ route('admin.fuelTransactions.store') }}",
        viewImage : "{{ route('admin.fuelTransactions.index') }}",
        clients   : "{{ route('admin.search.clients') }}",
        stations  : "{{ route('admin.stations.index') }}?list=true",
        vehicles  : "{{ route('admin.vehicles.index') }}?list=true",
        fuelTypes : "{{ route('admin.fuelTypes.list') }}"
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
            fields_list     : ['vehicle_id', 'station_id', 'fuel_type_id', 'total_amount', 'driver_id'],
            imgs_fields     : ['meter_image']
        },
        [
            { data: 'id',              name: 'id' },
            { data: 'reference_no',    name: 'reference_no' },
            { data: 'client_name',     name: 'client_name' },
            { data: 'vehicle_plate',   name: 'vehicle_plate' },
            { data: 'station_name',    name: 'station_name' },
            { data: 'fuel_type_name',  name: 'fuel_type_name' },
            { data: 'formatted_amount', name: 'formatted_amount' },
            { data: 'formatted_liters', name: 'formatted_liters' },
            { data: 'status_badge',    name: 'status_badge' },
            { data: 'meter_image_btn', name: 'meter_image_btn' },
            { data: 'processed_by',    name: 'processed_by' },
            { data: 'created_at',      name: 'created_at' },
            { data: 'actions',         name: 'actions' },
        ],
        function (d) {
            if ($('#s-reference_no').length) d.reference_no = $('#s-reference_no').val();
            if ($('#s-client_id').length)    d.client_id    = $('#s-client_id').val();
            if ($('#s-station_id').length)   d.station_id   = $('#s-station_id').val();
            if ($('#s-status').length)       d.status       = $('#s-status').val();
            if ($('#s-type').length)         d.type         = $('#s-type').val();
            if ($('#s-date_from').length)    d.date_from    = $('#s-date_from').val();
            if ($('#s-date_to').length)      d.date_to      = $('#s-date_to').val();
        }
    );

    function initSelect2() {
        $('#s-client_id').select2({
            allowClear: true,
            width: '100%',
            placeholder: "@lang('fuel_transactions.Select Client')",
            ajax: {
                url: ROUTES.clients,
                dataType: 'json',
                delay: 150,
                processResults: function (data) {
                    return {
                        results: $.map(data, function (item) {
                            return { text: item.company_name || item.name, id: item.id };
                        })
                    };
                },
                cache: true
            }
        });

        $('#s-station_id, #station_id').select2({
            allowClear: true,
            width: '100%',
            placeholder: "@lang('fuel_transactions.Select Station')",
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

        $('#vehicle_id').select2({
            allowClear: true,
            width: '100%',
            placeholder: "@lang('fuel_transactions.Select Vehicle')",
            ajax: {
                url: ROUTES.vehicles,
                dataType: 'json',
                delay: 150,
                processResults: function (response) {
                    var items = response.data || response;
                    return {
                        results: $.map(items, function (item) {
                            return { text: item.plate_number + ' - ' + (item.model || ''), id: item.id };
                        })
                    };
                },
                cache: true
            }
        });

        $('#fuel_type_id').select2({
            allowClear: true,
            width: '100%',
            placeholder: "@lang('fuel_transactions.Select Fuel Type')",
            ajax: {
                url: ROUTES.fuelTypes,
                dataType: 'json',
                delay: 150,
                processResults: function (response) {
                    var items = response.data || response;
                    return {
                        results: $.map(items, function (item) {
                            return { text: item.name + ' (' + item.price_per_liter + ' EGP/L)', id: item.id };
                        })
                    };
                },
                cache: true
            }
        });
    }

    initSelect2();

    $(document).on('click', '.view-meter-image', function () {
        var imageUrl = $(this).data('image-url');
        $('#meterImageModal img').attr('src', imageUrl);
        $('#meterImageModal').modal('show');
    });

    $(document).on('click', '.refund-btn', function () {
        var transactionId = $(this).data('transaction-id');
        var refNo = $(this).data('ref-no');
        $('#refund-transaction-id').val(transactionId);
        $('#refund-ref-no').text(refNo);
        $('#refund_reason').val('');
        $('#refundModal').modal('show');
    });

    $(document).on('click', '#confirm-refund-btn', async function () {
        var transactionId = $('#refund-transaction-id').val();
        var reason = $('#refund_reason').val();

        if (!reason.trim()) {
            failerToast("@lang('fuel_transactions.reason_required')");
            return;
        }

        $(this).attr('disabled', 'disabled');
        $(window.loddingSpinnerEl).fadeIn(500);

        try {
            var res = await axios.post(ROUTES.index + '/' + transactionId, {
                _token : "{{ csrf_token() }}",
                _method: 'PUT',
                refund : true,
                refund_reason: reason
            });

            if (!res.data.success) throw res.data.msg;

            $('#refundModal').modal('hide');
            $('.relode-btn').trigger('click');
            successToast("@lang('fuel_transactions.refund_success')");
        } catch (err) {
            failerToast(typeof(err) == 'string' ? err : "@lang('fuel_transactions.object_error')");
        }

        $(this).removeAttr('disabled');
        $(window.loddingSpinnerEl).fadeOut(500);
    });

    $(document).on('click', '.cancel-transaction-btn', async function () {
        var transactionId = $(this).data('transaction-id');

        if (!confirm("@lang('fuel_transactions.confirm_cancel')")) return;

        $(this).attr('disabled', 'disabled');
        $(window.loddingSpinnerEl).fadeIn(500);

        try {
            var res = await axios.post(ROUTES.index + '/' + transactionId, {
                _token : "{{ csrf_token() }}",
                _method: 'PUT',
                cancel : true
            });

            if (!res.data.success) throw res.data.msg;

            $('.relode-btn').trigger('click');
            successToast("@lang('fuel_transactions.cancel_success')");
        } catch (err) {
            failerToast(typeof(err) == 'string' ? err : "@lang('fuel_transactions.object_error')");
        }

        $(this).removeAttr('disabled');
        $(window.loddingSpinnerEl).fadeOut(500);
    });

    function showDetailsModal(title, icon, rows) {
        $('#detailsModalTitle').text(title);
        $('#detailsModalIcon').attr('class', 'fas ' + icon + ' me-2');

        var tbody = '';
        rows.forEach(function(row) {
            if (row.value) {
                tbody += '<tr><th class="text-muted" style="width:40%">' + row.label + '</th><td>' + row.value + '</td></tr>';
            }
        });
        $('#detailsModalBody').html(tbody);
        $('#detailsModal').modal('show');
    }

    $(document).on('click', '.view-client-details', function () {
        var data = $(this).data('client');
        if (typeof data === 'string') data = JSON.parse(data);

        showDetailsModal("@lang('fuel_transactions.Client Details')", 'fa-building', [
            { label: "@lang('fuel_transactions.Name')",  value: data.name },
            { label: "@lang('fuel_transactions.Phone')", value: data.phone },
            { label: "@lang('fuel_transactions.Email')", value: data.email }
        ]);
    });

    $(document).on('click', '.view-station-details', function () {
        var data = $(this).data('station');
        if (typeof data === 'string') data = JSON.parse(data);

        showDetailsModal("@lang('fuel_transactions.Station Details')", 'fa-gas-pump', [
            { label: "@lang('fuel_transactions.Name')",        value: data.name },
            { label: "@lang('fuel_transactions.Manager')",     value: data.manager_name },
            { label: "@lang('fuel_transactions.Phone')",       value: data.phone },
            { label: "@lang('fuel_transactions.Address')",     value: data.address },
            { label: "@lang('fuel_transactions.Governorate')", value: data.governorate },
            { label: "@lang('fuel_transactions.District')",    value: data.district }
        ]);
    });

    $(document).on('click', '.view-processor-details', function () {
        var data = $(this).data('processor');
        if (typeof data === 'string') data = JSON.parse(data);

        if (data.type === 'admin') {
            showDetailsModal("@lang('fuel_transactions.Admin Details')", 'fa-user-shield', [
                { label: "@lang('fuel_transactions.Name')",  value: data.name },
                { label: "@lang('fuel_transactions.Email')", value: data.email },
                { label: "@lang('fuel_transactions.Phone')", value: data.phone }
            ]);
        } else {
            showDetailsModal("@lang('fuel_transactions.Worker Details')", 'fa-hard-hat', [
                { label: "@lang('fuel_transactions.Name')",     value: data.name },
                { label: "@lang('fuel_transactions.Username')", value: data.username },
                { label: "@lang('fuel_transactions.Phone')",    value: data.phone }
            ]);
        }
    });
});
</script>
@endpush
