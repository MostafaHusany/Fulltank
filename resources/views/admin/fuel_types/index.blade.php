@extends('layouts.admin.app')

@push('title')
    <h1 class="h2">@lang('fuel_types.Title')</h1>
@endpush

@section('content')
    <div id="objectsCard" class="card">
        <div class="card-header">
            <div class="row">
                <div class="col-6 pt-1">
                    @lang('fuel_types.Title')
                </div><!-- /.col-6 -->
                <div class="col-6 text-end">
                    <button class="relode-btn btn btn-sm btn-outline-dark">
                        <i class="relode-btn-icon fas fa-sync-alt"></i>
                        <span class="relode-btn-loader spinner-grow spinner-grow-sm" style="display: none;" role="status" aria-hidden="true"></span>
                    </button>

                    <button class="btn btn-sm btn-outline-dark toggle-search">
                        <i class="fas fa-search"></i>
                    </button>

                    @if($permissions == 'admin' || in_array('fuelTypes_add', $permissions ?? []))
                    <button class="btn btn-sm btn-outline-primary toggle-btn" data-current-card="#objectsCard" data-target-card="#createObjectCard">
                        <i class="fas fa-plus"></i>
                    </button>
                    @endif
                </div><!-- /.col-6 -->
            </div><!-- /.row -->
        </div><!-- /.card-header -->

        <div class="card-body custome-table">
            @include('admin.fuel_types.incs._search')

            <div style="overflow-x: scroll">
                <table id="dataTable" class="table text-center">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>@lang('fuel_types.Name')</th>
                            <th>@lang('fuel_types.Price')</th>
                            <th>@lang('layouts.Active')</th>
                            <th>@lang('fuel_types.Last Updated')</th>
                            <th>@lang('layouts.Actions')</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div><!-- /.card-body -->
    </div><!-- /.card -->

    @if($permissions == 'admin' || in_array('fuelTypes_add', $permissions ?? []))
        @include('admin.fuel_types.incs._create')
    @endif

    @if($permissions == 'admin' || in_array('fuelTypes_show', $permissions ?? []))
        @include('admin.fuel_types.incs._show')
    @endif

    @if($permissions == 'admin' || in_array('fuelTypes_edit', $permissions ?? []))
        @include('admin.fuel_types.incs._edit')
    @endif
@endSection

@push('custome-js')
<script>
    $('document').ready(function () {
        const ROUTES = {
            index        : "{{ route('admin.fuelTypes.index') }}",
            store        : "{{ route('admin.fuelTypes.store') }}",
            show         : "{{ route('admin.fuelTypes.show', ['id' => 'ID']) }}",
            update       : "{{ route('admin.fuelTypes.update', ['id' => 'ID']) }}",
            destroy      : "{{ route('admin.fuelTypes.destroy', ['id' => 'ID']) }}",
            toggleStatus : "{{ route('admin.fuelTypes.toggleStatus', ['id' => 'ID']) }}"
        };

        const objects_dynamic_table = new DynamicTable(
            {
                index_route   : ROUTES.index,
                store_route   : ROUTES.index,
                show_route    : ROUTES.show,
                update_route  : ROUTES.update,
                destroy_route : ROUTES.destroy,
                draft         : { route: '', flag: '' }
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
                fields_list     : ['id', 'name', 'price_per_liter', 'description'],
                imgs_fields     : []
            },
            [
                { data: 'id',               name: 'id' },
                { data: 'name',             name: 'name' },
                { data: 'price_formatted',  name: 'price_formatted' },
                { data: 'status_toggle',    name: 'status_toggle' },
                { data: 'last_updated',     name: 'last_updated' },
                { data: 'actions',          name: 'actions' }
            ],
            function (d) {
                if ($('#s-name').length)
                    d.name = $('#s-name').val();
            }
        );

        objects_dynamic_table.table_object.buttons().container().hide();

        objects_dynamic_table.addDataToForm = function (fields_list, imgs_fields, data, prefix) {
            var p = prefix || '';
            fields_list.forEach(function (f) {
                if (f !== 'id' && $('#' + p + f).length)
                    $('#' + p + f).val(data[f] != null ? data[f] : '').change();
            });
            if (p === 'edit-')
                $('#edit-id').val(data.id);
        };

        objects_dynamic_table.showDataForm = async function (targetBtn) {
            var id = $(targetBtn).data('object-id');
            try {
                var res = await axios.get(ROUTES.show.replace('ID', id));
                var d = res.data;
                if (d.success && d.data) {
                    $('#show-name').text(d.data.name || '---');
                    $('#show-price_per_liter').text(d.data.price_per_liter != null ? d.data.price_per_liter : '---');
                    $('#show-description').text(d.data.description || '---');
                    $('#show-is_active').text(d.data.is_active ? '@lang('layouts.active')' : '@lang('layouts.de-active')');
                    $('#show-updated_at').text(d.data.updated_at ? d.data.updated_at : '---');
                    return true;
                }
                failerToast(d.msg || 'Error');
            } catch (e) {
                failerToast(e.response && e.response.data && e.response.data.msg ? e.response.data.msg : 'Error');
            }
            return false;
        };

        $(document).on('click', '.ft-status-toggle', function () {
            var id = $(this).data('id');
            var sw = $(this);
            sw.prop('disabled', true);
            axios.put(ROUTES.toggleStatus.replace('ID', id), { _token: $('meta[name="csrf-token"]').attr('content'), _method: 'PUT' }).then(function (r) {
                if (r.data.success) {
                    successToast(r.data.msg);
                    objects_dynamic_table.table_object.draw();
                } else {
                    sw.prop('checked', !sw.prop('checked'));
                    failerToast(r.data.msg || 'Error');
                }
            }).catch(function (e) {
                sw.prop('checked', !sw.prop('checked'));
                failerToast(e.response && e.response.data && e.response.data.msg ? e.response.data.msg : 'Error');
            }).finally(function () { sw.prop('disabled', false); });
        });

        $('.relode-btn').on('click', function () {
            objects_dynamic_table.table_object.draw();
        });
    });
</script>
@endpush
