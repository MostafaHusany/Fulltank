@extends('layouts.admin.app')

@push('title')
    <h1 class="h2">@lang('station_wallets.Title')</h1>
@endpush

@section('content')
    <div id="objectsCard" class="card">
        <div class="card-header">
            <div class="row g-2 align-items-center">
                <div class="col-12 col-md-6 pt-1 order-2 order-md-1">
                    <span class="fw-bold">@lang('station_wallets.Title')</span>
                </div>
                <div class="col-12 col-md-6 order-1 order-md-2">
                    <div class="d-flex flex-wrap gap-1 justify-content-end">
                        <button class="relode-btn btn btn-sm btn-outline-dark" title="Refresh">
                            <i class="relode-btn-icon fas fa-sync-alt"></i>
                            <span class="relode-btn-loader spinner-grow spinner-grow-sm" style="display: none;" role="status" aria-hidden="true"></span>
                        </button>
                        <button class="btn btn-sm btn-outline-dark toggle-search">
                            <i class="fas fa-search"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <div class="card-body custome-table p-2 p-md-3">
            @include('admin.station_wallets.incs._search')

            <div class="table-responsive">
                <table id="dataTable" class="table table-sm table-hover text-center mb-0">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>@lang('station_wallets.Station Name')</th>
                            <th>@lang('station_wallets.Governorate')</th>
                            <th>@lang('station_wallets.District')</th>
                            <th>@lang('station_wallets.Current Balance')</th>
                            <th>@lang('station_wallets.Wallet Status')</th>
                            <th>@lang('layouts.Actions')</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
    </div>

    @include('admin.station_wallets.incs._transactions_slideover')
@endsection

@push('custome-js')
<script>
(function () {
    var ROUTES = {
        index: "{{ route('admin.stationWallets.index') }}",
        governors: "{{ route('admin.search.governorates') }}",
        districts: "{{ route('admin.search.districts') }}"
    };

    $('document').ready(function () {
        var stationWalletDataTable = new DynamicTable(
            {
                index_route   : ROUTES.index,
                store_route   : ROUTES.index,
                show_route    : ROUTES.index,
                update_route  : ROUTES.index,
                destroy_route : ROUTES.index,
                draft         : { route: '', flag: '' }
            },
            '#dataTable',
            { success_el: '#successAlert', danger_el: '#dangerAlert', warning_el: '#warningAlert' },
            {
                table_id       : '#dataTable',
                toggle_btn     : '.toggle-btn',
                create_obj_btn : '.create-object',
                update_obj_btn : '.update-object',
                draft_obj_btn  : '',
                fields_list    : [],
                imgs_fields    : []
            },
            [
                { data: 'id', name: 'id' },
                { data: 'station_name', name: 'station_name' },
                { data: 'governorate_name', name: 'governorate_name' },
                { data: 'district_name', name: 'district_name' },
                { data: 'current_balance', name: 'current_balance' },
                { data: 'wallet_status', name: 'wallet_status' },
                { data: 'actions', name: 'actions' }
            ],
            function (d) {
                if ($('#s-station_name').length) d.station_name = $('#s-station_name').val();
                if ($('#s-governorate_id').length) d.governorate_id = $('#s-governorate_id').val();
                if ($('#s-district_id').length) d.district_id = $('#s-district_id').val();
                if ($('#s-is_active').length && $('#s-is_active').val() !== '') d.is_active = $('#s-is_active').val();
            }
        );

        window.stationWalletDataTable = stationWalletDataTable;

        stationWalletDataTable.table_object.buttons().container().hide();

        $('.relode-btn').on('click', function () {
            $('.relode-btn-icon').hide();
            $('.relode-btn-loader').show();
            stationWalletDataTable.table_object.draw();
        });

        stationWalletDataTable.table_object.on('draw.dt', function () {
            $('.relode-btn-icon').show();
            $('.relode-btn-loader').hide();
        });

        $('#s-governorate_id').select2({
            allowClear: true,
            width: '100%',
            placeholder: '@lang("station_wallets.Governorate")',
            ajax: {
                url: ROUTES.governors,
                dataType: 'json',
                delay: 150,
                data: function (p) { return { q: p.term }; },
                processResults: function (d) {
                    return { results: (d || []).map(function (g) { return { id: g.id, text: g.text }; }) };
                },
                cache: true
            }
        }).on('change', function () {
            $('#s-district_id').val(null).trigger('change');
        });

        $('#s-district_id').select2({
            allowClear: true,
            width: '100%',
            placeholder: '@lang("station_wallets.District")',
            ajax: {
                url: ROUTES.districts,
                dataType: 'json',
                delay: 150,
                data: function (p) { return { q: p.term, governorate_id: $('#s-governorate_id').val() }; },
                processResults: function (d) {
                    return { results: (d || []).map(function (x) { return { id: x.id, text: x.text }; }) };
                },
                cache: true
            }
        });
    });
})();
</script>
@endpush
