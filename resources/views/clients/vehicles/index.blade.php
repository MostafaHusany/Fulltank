@extends('layouts.clients.app')

@push('title')
    <h1 class="h2">@lang('client.vehicles.title')</h1>
@endpush

@section('content')
    <div id="objectsCard" class="card">
        <div class="card-header">
            <div class="row">
                <div class="col-6 pt-1">
                    @lang('client.vehicles.title')
                </div><!-- /.col-6 -->
                <div class="col-6 text-end">
                    <button class="bulk-delete-btn btn btn-sm btn-outline-dark" data-bs-toggle="tooltip" data-bs-placement="top" title="@lang('layouts.delete')">
                        <i class="fas fa-trash-alt"></i>
                    </button>

                    <button class="relode-btn btn btn-sm btn-outline-dark">
                        <i class="relode-btn-icon fas fa-sync-alt"></i>
                        <span class="relode-btn-loader spinner-grow spinner-grow-sm" style="display: none;" role="status" aria-hidden="true"></span>
                    </button>

                    <button class="btn btn-sm btn-outline-primary toggle-btn" data-current-card="#objectsCard" data-target-card="#createObjectCard">
                        <i class="fas fa-plus"></i>
                    </button>
                </div><!-- /.col-6 -->
            </div><!-- /.row -->
        </div><!-- /.card-header -->

        <div class="card-body custome-table">
            <div style="overflow-x: scroll">
                <table id="dataTable" class="table text-center">
                    <thead>
                        <tr>
                            <th>@include('layouts.admin.incs._checkbox_select_all')</th>
                            <th>#</th>
                            <th>@lang('client.vehicles.plate_number')</th>
                            <th>@lang('client.vehicles.model')</th>
                            <th>@lang('client.vehicles.fuel_type')</th>
                            <th>@lang('client.vehicles.quota')</th>
                            <th>@lang('layouts.Active')</th>
                            <th>@lang('layouts.Actions')</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div><!-- /.card-body -->
    </div><!-- /.card -->

    @include('clients.vehicles.incs._create')
    @include('clients.vehicles.incs._edit')

@endsection

@push('custome-js')
<script>
    $('document').ready(function () {
        window.is_ar = '{{ $is_ar }}';

        const objects_dynamic_table = new DynamicTable(
            {
                index_route   : "{{ route('client.vehicles.index') }}",
                store_route   : "{{ route('client.vehicles.store') }}",
                show_route    : "{{ route('client.vehicles.index') }}",
                update_route  : "{{ route('client.vehicles.index') }}",
                destroy_route : "{{ route('client.vehicles.index') }}",
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
                table_id           : '#dataTable',
                toggle_btn         : '.toggle-btn',
                create_obj_btn     : '.create-object',
                update_obj_btn     : '.update-object',
                draft_obj_btn      : '',
                edit_objects_card  : '#editObjectsCard',
                fields_list        : ['id', 'plate_number', 'model', 'fuel_type_id', 'monthly_quota'],
                imgs_fields        : []
            },
            [
                { data: 'checkbox_selector', name: 'checkbox_selector', 'orderable': false },
                { data: 'id',                name: 'id' },
                { data: 'plate_number',      name: 'plate_number' },
                { data: 'model',             name: 'model' },
                { data: 'fuel_type_name', name: 'fuel_type_name', 'orderable': false },
                { data: 'quota_info',        name: 'quota_info', 'orderable': false },
                { data: 'activation',        name: 'activation', 'orderable': false },
                { data: 'actions',           name: 'actions', 'orderable': false }
            ],
            function (d) {}
        );

        objects_dynamic_table.validateData = (data, prefix = '') => {
            let is_valide = true;

            $('.err-msg').slideUp(500);

            if (!data.get('plate_number') || data.get('plate_number') === '') {
                is_valide = false;
                $(`#${prefix}plate_numberErr`).text('@lang("client.vehicles.plate_required")').slideDown(500);
            }

            if (!data.get('fuel_type_id') || data.get('fuel_type_id') === '') {
                is_valide = false;
                $(`#${prefix}fuel_type_idErr`).text('@lang("client.vehicles.fuel_required")').slideDown(500);
            }

            if (!data.get('monthly_quota') || data.get('monthly_quota') === '') {
                is_valide = false;
                $(`#${prefix}monthly_quotaErr`).text('@lang("client.vehicles.quota_required")').slideDown(500);
            }

            return is_valide;
        };

        objects_dynamic_table.addDataToForm = (fields_id_list, imgs_fields, data, prefix) => {
            fields_id_list.forEach(el_id => {
                $(`#${prefix}${el_id}`).val(Boolean(data[el_id]) ? data[el_id] : '').change();
            });

            if (data.quota) {
                $(`#${prefix}monthly_quota`).val(data.quota.amount_limit).change();
            }

            $('#edit-id').val(data.id);
        };

        $('#dataTable').on('change', '.activation-toggle', async function () {
            let target_id = $(this).data('object-id');
            let $toggle = $(this);

            if (!Boolean(target_id)) return -1;

            $(window.loddingSpinnerEl).fadeIn(500);

            try {
                let res = await axios.put(`{{ route('client.vehicles.index') }}/${target_id}`, {
                    _token       : "{{ csrf_token() }}",
                    toggle_status: true
                });

                let { data, success, msg } = res.data;

                if (!success) throw msg;

                successToast(`@lang('client.vehicles.status_updated')`);

            } catch (err) {
                failerToast(typeof(err) == 'string' ? err : `@lang('client.vehicles.error')`);
                $toggle.prop('checked', !$toggle.prop('checked'));
            }

            $(window.loddingSpinnerEl).fadeOut(500);
        });

        $('#dataTable').on('click', '.delete-object', function () {
            let target_id = $(this).data('object-id');
            let object_name = $(this).data('object-name');

            if (!Boolean(target_id)) return -1;

            Swal.fire({
                title: `@lang('client.vehicles.confirm_delete')`,
                text: object_name || '',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#6c757d',
                confirmButtonText: `@lang('layouts.confirm')`,
                cancelButtonText: `@lang('layouts.cancel')`
            }).then(async (result) => {
                if (result.isConfirmed) {
                    $(window.loddingSpinnerEl).fadeIn(500);

                    try {
                        let res = await axios.delete(`{{ route('client.vehicles.index') }}/${target_id}`);

                        let { data, success, msg } = res.data;

                        if (!success) throw msg;

                        successToast(msg || `@lang('client.vehicles.deleted')`);
                        $('.relode-btn').trigger('click');

                    } catch (err) {
                        failerToast(typeof(err) == 'string' ? err : `@lang('client.vehicles.error')`);
                    }

                    $(window.loddingSpinnerEl).fadeOut(500);
                }
            });
        });

        const init = (() => {

        })();

    });
</script>
@endpush
