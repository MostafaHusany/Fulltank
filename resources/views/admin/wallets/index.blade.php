@extends('layouts.admin.app')

@push('title')
    <h1 class="h2">@lang('wallets.Title Administration')</h1>
@endpush

@section('content')
    <div id="objectsCard" class="card">
        <div class="card-header">
            <div class="row g-2 align-items-center">
                <div class="col-12 col-md-6 pt-1 order-2 order-md-1">
                    <span class="fw-bold">@lang('wallets.Title Administration')</span>
                </div>
                <div class="col-12 col-md-6 order-1 order-md-2">
                    <div class="d-flex flex-wrap gap-1 justify-content-end">
                        <button class="relode-btn btn btn-sm btn-outline-dark" title="@lang('clients.object_updated')">
                            <i class="relode-btn-icon fas fa-sync-alt"></i>
                            <span class="relode-btn-loader spinner-grow spinner-grow-sm" style="display: none;" role="status" aria-hidden="true"></span>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <div class="card-body custome-table p-2 p-md-3">
            <div class="table-responsive">
                <table id="dataTable" class="table table-sm table-hover text-center mb-0">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>@lang('wallets.Client')</th>
                            <th>@lang('wallets.Current Balance')</th>
                            <th>@lang('wallets.Wallet Status')</th>
                            <th>@lang('layouts.Actions')</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
    </div><!-- /.card -->

    @include('admin.wallets.incs._add_balance_modal')
    @include('admin.wallets.incs._transactions_slideover')
@endsection

@push('custome-js')
<script>
(function () {
    var ROUTES = { index: "{{ route('admin.wallets.index') }}" };

    $('document').ready(function () {
        var walletDataTable = new DynamicTable(
            {
                index_route   : ROUTES.index,
                store_route   : ROUTES.index,
                show_route    : ROUTES.index,
                update_route  : ROUTES.index,
                destroy_route : ROUTES.index,
                draft         : { route: '', flag: '' }
            },
            '#dataTable',
            { success_el : '#successAlert', danger_el : '#dangerAlert', warning_el : '#warningAlert' },
            {
                table_id        : '#dataTable',
                toggle_btn      : '.toggle-btn',
                create_obj_btn  : '.create-object',
                update_obj_btn  : '.update-object',
                draft_obj_btn   : '.create-draft',
                fields_list     : [],
                imgs_fields     : []
            },
            [
                { data: 'id', name: 'id' },
                { data: 'client_name', name: 'client_name' },
                { data: 'current_balance', name: 'current_balance' },
                { data: 'wallet_status', name: 'wallet_status' },
                { data: 'actions', name: 'actions' },
            ],
            function (d) {}
        );
        
        window.walletDataTable = walletDataTable;

        $('.relode-btn').on('click', function () {
            $('.relode-btn-icon').hide();
            $('.relode-btn-loader').show();
            walletDataTable.table_object.draw();
        });

        walletDataTable.table_object.on('draw.dt', function () {
            $('.relode-btn-icon').show();
            $('.relode-btn-loader').hide();
        });
    });
})();
</script>
@endpush
