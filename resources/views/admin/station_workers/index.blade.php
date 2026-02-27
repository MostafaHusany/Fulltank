@extends('layouts.admin.app')

@push('title')
    <h1 class="h2">@lang('station_workers.Title')</h1>
@endpush

@section('content')
    <div id="objectsCard" class="card">
        <div class="card-header">
            <div class="row">
                <div class="col-6 pt-1">
                    @lang('station_workers.Title Administration')
                </div>
                <div class="col-6 text-end">
                    <button class="relode-btn btn btn-sm btn-outline-dark">
                        <i class="relode-btn-icon fas fa-sync-alt"></i>
                        <span class="relode-btn-loader spinner-grow spinner-grow-sm" style="display: none;" role="status" aria-hidden="true"></span>
                    </button>

                    <button class="btn btn-sm btn-outline-dark toggle-search">
                        <i class="fas fa-search"></i>
                    </button>

                    @if($permissions == 'admin' || in_array('stationWorkers_add', $permissions))
                    <button class="btn btn-sm btn-outline-primary toggle-btn" data-current-card="#objectsCard" data-target-card="#createObjectCard">
                        <i class="fas fa-plus"></i>
                    </button>
                    @endif
                </div>
            </div>
        </div>

        <div class="card-body custome-table">
            @include('admin.station_workers.incs._search')

            <div style="overflow-x: scroll">
                <table id="dataTable" class="table text-center">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>@lang('station_workers.Full Name')</th>
                            <th>@lang('station_workers.Username')</th>
                            <th>@lang('station_workers.Station')</th>
                            <th>@lang('station_workers.Phone')</th>
                            <th>@lang('station_workers.Status')</th>
                            <th>@lang('layouts.Actions')</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
    </div>

    @if($permissions == 'admin' || in_array('stationWorkers_add', $permissions))
        @include('admin.station_workers.incs._create')
    @endif

    @if($permissions == 'admin' || in_array('stationWorkers_edit', $permissions))
        @include('admin.station_workers.incs._edit')
    @endif
@endSection

@push('custome-js')
<script>
$('document').ready(function () {
    window.is_ar = '{{ $is_ar }}';

    const ROUTES = {
        index   : "{{ route('admin.stationWorkers.index') }}",
        store   : "{{ route('admin.stationWorkers.store') }}",
        stations: "{{ route('admin.stations.index') }}?list=true"
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
            update_obj_btn  : '.update-object',
            fields_list     : ['id', 'full_name', 'station_id', 'phone', 'username', 'password'],
            imgs_fields     : []
        },
        [
            { data: 'id',           name: 'id' },
            { data: 'full_name',    name: 'full_name' },
            { data: 'username',     name: 'username' },
            { data: 'station_name', name: 'station_name' },
            { data: 'phone',        name: 'phone' },
            { data: 'status',       name: 'status' },
            { data: 'actions',      name: 'actions' },
        ],
        function (d) {
            if ($('#s-station_id').length) d.station_id = $('#s-station_id').val();
            if ($('#s-username').length)   d.username   = $('#s-username').val();
            if ($('#s-full_name').length)  d.full_name  = $('#s-full_name').val();
            if ($('#s-is_active').length)  d.is_active  = $('#s-is_active').val();
        }
    );

    objects_dynamic_table.addDataToForm = function(fields_id_list, imgs_fields, data, prefix) {
        $('#' + prefix + 'station_id').empty();

        fields_id_list.forEach(function(el_id) {
            if (el_id === 'password') {
                $('#' + prefix + 'password').val('');
            } else if (el_id === 'username' && data.user) {
                $('#' + prefix + 'username').val(data.user.username || '');
            } else {
                var val = data[el_id] !== undefined && data[el_id] !== null ? data[el_id] : '';
                $('#' + prefix + el_id).val(val).change();
            }
        });

        if (data.station) {
            var option = new Option(data.station.name, data.station.id, true, true);
            $('#' + prefix + 'station_id').append(option).trigger('change');
        }

        $('#edit-id').val(data.id);
    };

    function initSelect2() {
        $('#station_id, #edit-station_id, #s-station_id').select2({
            allowClear: true,
            width: '100%',
            placeholder: "@lang('station_workers.Select Station')",
            ajax: {
                url: ROUTES.stations,
                dataType: 'json',
                delay: 150,
                data: function (params) {
                    return { q: params.term };
                },
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

    $(document).on('click', '.worker-status-toggle', async function () {
        var target_id = $(this).data('target');
        var $btn = $(this);

        if (!target_id) return;

        $btn.attr('disabled', 'disabled');
        $(window.loddingSpinnerEl).fadeIn(500);

        try {
            var res = await axios.post(ROUTES.index + '/' + target_id, {
                _token        : "{{ csrf_token() }}",
                _method       : 'PUT',
                update_status : true,
            });

            var result = res.data;

            if (!result.success) throw result.msg;

            $('.relode-btn').trigger('click');
            successToast("@lang('station_workers.status_updated')");
        } catch (err) {
            failerToast(typeof(err) == 'string' ? err : "@lang('station_workers.object_error')");
        }

        $btn.removeAttr('disabled');
        $(window.loddingSpinnerEl).fadeOut(500);
    });
});
</script>
@endpush
