@extends('layouts.clients.app')

@push('title')
    <h1 class="h2">@lang('client.drivers.title')</h1>
@endpush

@section('content')
    <div id="objectsCard" class="card">
        <div class="card-header">
            <div class="row">
                <div class="col-6 pt-1">
                    @lang('client.drivers.title')
                </div><!-- /.col-6 -->
                <div class="col-6 text-end">
                    <button class="bulk-delete-btn btn btn-sm btn-outline-dark" data-bs-toggle="tooltip" data-bs-placement="top" title="@lang('layouts.delete')">
                        <i class="fas fa-trash-alt"></i>
                    </button>

                    <button class="relode-btn btn btn-sm btn-outline-dark">
                        <i class="relode-btn-icon fas fa-sync-alt"></i>
                        <span class="relode-btn-loader spinner-grow spinner-grow-sm" style="display: none;" role="status" aria-hidden="true"></span>
                    </button>

                    <button class="btn btn-sm btn-outline-dark toggle-search">
                        <i class="fas fa-search"></i>
                    </button>

                    <button class="btn btn-sm btn-outline-primary toggle-btn" data-current-card="#objectsCard" data-target-card="#createObjectCard">
                        <i class="fas fa-plus"></i>
                    </button>
                </div><!-- /.col-6 -->
            </div><!-- /.row -->
        </div><!-- /.card-header -->

        <div class="card-body custome-table">
            @include('clients.drivers.incs._search')

            <div style="overflow-x: scroll">
                <table id="dataTable" class="table text-center">
                    <thead>
                        <tr>
                            <th>@include('layouts.admin.incs._checkbox_select_all')</th>
                            <th>#</th>
                            <th>@lang('client.drivers.name')</th>
                            <th>@lang('client.drivers.phone')</th>
                            <th>@lang('client.drivers.vehicle')</th>
                            <th>@lang('layouts.Active')</th>
                            <th>@lang('layouts.Actions')</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div><!-- /.card-body -->
    </div><!-- /.card -->

    @include('clients.drivers.incs._create')
    @include('clients.drivers.incs._edit')

@endsection

@push('custome-js')
<script>
    $('document').ready(function () {
        window.is_ar = '{{ $is_ar }}';

        const objects_dynamic_table = new DynamicTable(
            {
                index_route   : "{{ route('client.drivers.index') }}",
                store_route   : "{{ route('client.drivers.store') }}",
                show_route    : "{{ route('client.drivers.index') }}",
                update_route  : "{{ route('client.drivers.index') }}",
                destroy_route : "{{ route('client.drivers.index') }}",
                draft         : {
                    route : '',
                    flag  : ''
                }
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
                update_obj_btn  : '.update-object',
                draft_obj_btn   : '',
                fields_list     : ['id', 'name', 'phone', 'password', 'vehicle_id'],
                imgs_fields     : []
            },
            [
                { data: 'checkbox_selector', name: 'checkbox_selector', 'orderable': false },
                { data: 'id',                name: 'id' },
                { data: 'name',              name: 'name' },
                { data: 'phone',             name: 'phone' },
                { data: 'vehicle_plate',     name: 'vehicle_plate', 'orderable': false },
                { data: 'activation',        name: 'activation', 'orderable': false },
                { data: 'actions',           name: 'actions', 'orderable': false }
            ],
            function (d) {
                if ($('#s-name').length) d.name = $('#s-name').val();
                if ($('#s-phone').length) d.phone = $('#s-phone').val();
                if ($('#s-vehicle_id').length) d.vehicle_id = $('#s-vehicle_id').val();
                if ($('#s-is_active').length) d.is_active = $('#s-is_active').val();
            }
        );

        objects_dynamic_table.validateData = (data, prefix = '') => {
            let is_valide = true;

            $('.err-msg').slideUp(500);

            if (!data.get('name') || data.get('name') === '') {
                is_valide = false;
                $(`#${prefix}nameErr`).text('@lang("client.drivers.name_required")').slideDown(500);
            }

            if (!data.get('phone') || data.get('phone') === '') {
                is_valide = false;
                $(`#${prefix}phoneErr`).text('@lang("client.drivers.phone_required")').slideDown(500);
            }

            if (prefix === '' && (!data.get('password') || data.get('password') === '')) {
                is_valide = false;
                $(`#${prefix}passwordErr`).text('@lang("client.drivers.password_required")').slideDown(500);
            }

            return is_valide;
        };

        objects_dynamic_table.addDataToForm = (fields_id_list, imgs_fields, data, prefix) => {
            fields_id_list.forEach(el_id => {
                if (el_id !== 'password') {
                    $(`#${prefix}${el_id}`).val(Boolean(data[el_id]) ? data[el_id] : '').change();
                }
            });

            if (data.vehicle_id) {
                $(`#${prefix}vehicle_id`).val(data.vehicle_id).change();
            }

            $('#edit-id').val(data.id);
        };

        $('#dataTable').on('change', '.activation-toggle', async function () {
            let target_id = $(this).data('object-id');
            let $toggle = $(this);

            if (!Boolean(target_id)) return -1;

            $(window.loddingSpinnerEl).fadeIn(500);

            try {
                let res = await axios.post(`{{ route('client.drivers.index') }}/${target_id}`, {
                    _token         : "{{ csrf_token() }}",
                    _method        : 'PUT',
                    activate_object: true,
                });

                let { data, success, msg } = res.data;

                if (!success) throw msg;

                successToast(`@lang('client.drivers.status_updated')`);

            } catch (err) {
                failerToast(typeof(err) == 'string' ? err : `@lang('client.drivers.error')`);
                $toggle.prop('checked', !$toggle.prop('checked'));
            }

            $(window.loddingSpinnerEl).fadeOut(500);
        });

        const init = (() => {

        })();

    });
</script>
@endpush
