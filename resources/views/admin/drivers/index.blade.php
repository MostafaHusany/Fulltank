@extends('layouts.admin.app')

@push('title')
    <h1 class="h2">@lang('drivers.Title Administration')</h1>
@endpush

@section('content')
    <div id="objectsCard" class="card">
        <div class="card-header">
            <div class="row g-2 align-items-center">
                <div class="col-12 col-md-6 pt-1 order-2 order-md-1">
                    <span class="fw-bold">@lang('drivers.Title Administration')</span>
                </div>
                <div class="col-12 col-md-6 order-1 order-md-2">
                    <div class="d-flex flex-wrap gap-1 justify-content-end">
                        @if($permissions == 'admin' || in_array('drivers_delete', $permissions))
                        <button class="bulk-delete-btn btn btn-sm btn-outline-dark" title="@lang('layouts.delete')">
                            <i class="fas fa-trash-alt"></i>
                        </button>
                        @endif
                        <button class="relode-btn btn btn-sm btn-outline-dark" title="@lang('clients.object_updated')">
                            <i class="relode-btn-icon fas fa-sync-alt"></i>
                            <span class="relode-btn-loader spinner-grow spinner-grow-sm" style="display: none;" role="status" aria-hidden="true"></span>
                        </button>
                        <button class="btn btn-sm btn-outline-dark toggle-search" title="@lang('layouts.show')">
                            <i class="fas fa-search"></i>
                        </button>
                        @if($permissions == 'admin' || in_array('drivers_add', $permissions))
                        <button class="btn btn-sm btn-outline-primary toggle-btn" data-current-card="#objectsCard" data-target-card="#createObjectCard" title="@lang('drivers.Create Driver')">
                            <i class="fas fa-plus"></i>
                        </button>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <div class="card-body custome-table p-2 p-md-3">
            @include('admin.drivers.incs._search')

            <div class="table-responsive">
            <table id="dataTable" class="table table-sm table-hover text-center mb-0">
                <thead>
                    <tr>
                        <th>@include('layouts.admin.incs._checkbox_select_all')</th>
                        <th>#</th>
                        <th>@lang('drivers.Name')</th>
                        <th>@lang('drivers.Email')</th>
                        <th>@lang('drivers.Phone')</th>
                        <th>@lang('drivers.Client')</th>
                        <th>@lang('drivers.Vehicle')</th>
                        <th>@lang('layouts.Active')</th>
                        <th>@lang('layouts.Actions')</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
            </div>
        </div>
    </div><!-- /.card -->

    @if($permissions == 'admin' || in_array('drivers_add', $permissions))
        @include('admin.drivers.incs._create')
    @endif

    @if($permissions == 'admin' || in_array('drivers_show', $permissions))
        @include('admin.drivers.incs._show')
    @endif

    @if($permissions == 'admin' || in_array('drivers_edit', $permissions))
        @include('admin.drivers.incs._edit')
    @endif

@endSection

@push('custome-js')
<script>
    (function () {
        var filterClientId   = @json($filterClientId ?? null);
        var filterClientName = @json($filterClientName ?? null);

        const ROUTES = {
            index   : "{{ route('admin.drivers.index') }}",
            store   : "{{ route('admin.drivers.store') }}",
            clients : "{{ route('admin.search.clients') }}",
            vehiclesByClient : "{{ route('admin.search.vehiclesByClient') }}"
        };

        const LANG = {
            name_required     : '@lang("drivers.name_required")',
            email_required    : '@lang("drivers.email_required")',
            phone_required    : '@lang("drivers.phone_required")',
            password_required : '@lang("drivers.password_required")',
            client_required   : '@lang("drivers.client_required")',
            selectClient      : '{{ __("drivers.Select Client") }}',
            selectVehicle     : '{{ __("drivers.Select Vehicle") }}',
            noVehicle         : '{{ __("drivers.No vehicle") }}'
        };

        const VALIDATION = {
            name      : LANG.name_required,
            email     : LANG.email_required,
            phone     : LANG.phone_required,
            client_id : LANG.client_required
        };

        $('document').ready(function () {

            const objects_dynamic_table = new DynamicTable(
                {
                    index_route   : ROUTES.index,
                    store_route   : ROUTES.store,
                    show_route    : ROUTES.index,
                    update_route  : ROUTES.index,
                    destroy_route : ROUTES.index,
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
                    draft_obj_btn   : '.create-draft',
                    fields_list     : ['id', 'name', 'email', 'phone', 'password', 'client_id', 'vehicle_id'],
                    imgs_fields     : ['picture']
                },
                [
                    { data: 'checkbox_selector',  name: 'checkbox_selector', orderable: false },
                    { data: 'id',                 name: 'id' },
                    { data: 'name',               name: 'name' },
                    { data: 'email',              name: 'email' },
                    { data: 'phone',              name: 'phone' },
                    { data: 'client_name',        name: 'client_name' },
                    { data: 'vehicle_display',    name: 'vehicle_display' },
                    { data: 'activation',         name: 'activation' },
                    { data: 'actions',            name: 'actions' },
                ],
                function (d) {
                    if (filterClientId) d.client_id = filterClientId;
                    else if ($('#s-client_id').length) d.client_id = $('#s-client_id').val();
                    if ($('#s-name').length) d.name = $('#s-name').val();
                    if ($('#s-email').length) d.email = $('#s-email').val();
                    if ($('#s-phone').length) d.phone = $('#s-phone').val();
                    if ($('#s-is_active').length) d.is_active = $('#s-is_active').val();
                }
            );

            objects_dynamic_table.validateData = (data, prefix = '') => {
                let valid = true;
                $('.err-msg').slideUp(500);

                Object.keys(VALIDATION).forEach(field => {
                    const val = data.get(field);
                    if (!val || val === '') {
                        valid = false;
                        const errEl = $(`#${prefix}${field}Err`);
                        if (errEl.length) errEl.text(VALIDATION[field]).slideDown(500);
                    }
                });
                if (prefix === '' && (!data.get('password') || data.get('password') === '')) {
                    valid = false;
                    $(`#${prefix}passwordErr`).text(LANG.password_required).slideDown(500);
                }
                return valid;
            };

            objects_dynamic_table.showDataForm = async (targetBtn) => {
                const id = $(targetBtn).data('object-id');
                try {
                    const { data, success, msg } = (await axios.get(`${ROUTES.index}/${id}`)).data;
                    if (!success) { failerToast(Array.isArray(msg) ? msg[0] : (msg || 'Error')); return false; }

                    $('#show-name').text(data.name || '---');
                    $('#show-email').text(data.email || '---');
                    $('#show-phone').text(data.phone || '---');
                    $('#show-client_name').text(data.client_name || '---');
                    $('#show-vehicle_display').text(data.vehicle_display || LANG.noVehicle);
                    return true;
                } catch (err) {
                    const msg = err.response?.data?.msg || (typeof err === 'string' ? err : (Array.isArray(err) ? err[0] : 'Error'));
                    failerToast(Array.isArray(msg) ? msg[0] : msg);
                    return false;
                }
            };

            objects_dynamic_table.addDataToForm = (fields_id_list, imgs_fields, data, prefix) => {
                fields_id_list.forEach(el_id => {
                    if (imgs_fields.includes(el_id)) return;
                    if (el_id !== 'client_id' && el_id !== 'vehicle_id') {
                        $(`#${prefix}${el_id}`).val(data[el_id] ?? '').change();
                    }
                });
                if (prefix === 'edit-' && data.client_id) {
                    const $sel = $(`#${prefix}client_id`);
                    $sel.empty().append(new Option(data.client_name || 'Client #' + data.client_id, data.client_id, true, true)).trigger('change');
                    const $vehicleSel = $(`#${prefix}vehicle_id`);
                    $vehicleSel.empty();
                    if (data.vehicle_id && data.vehicle_display) {
                        $vehicleSel.append(new Option(data.vehicle_display, data.vehicle_id, true, true));
                    } else {
                        $vehicleSel.append(new Option(LANG.selectVehicle, '', true, true));
                    }
                    $vehicleSel.trigger('change');
                    loadVehiclesForClient(prefix, data.client_id, data.vehicle_id);
                }
                $(`#${prefix}id`).val(data.id);
            };

            function loadVehiclesForClient(prefix, clientId, selectedVehicleId) {
                const $sel = $(`#${prefix}vehicle_id`);
                if (!clientId) {
                    $sel.empty().append(new Option(LANG.noVehicle, '', true, true)).trigger('change');
                    return;
                }
                axios.get(ROUTES.vehiclesByClient, { params: { client_id: clientId } }).then(function (res) {
                    const list = res.data || [];
                    $sel.empty().append(new Option(LANG.noVehicle, '', !selectedVehicleId, !selectedVehicleId));
                    list.forEach(function (item) {
                        const sel = item.id == selectedVehicleId;
                        $sel.append(new Option(item.text, item.id, sel, sel));
                    });
                    $sel.trigger('change');
                });
            }

            (function initSelect2() {
                const clientsSelect2Opts = {
                    allowClear: true,
                    width: '100%',
                    placeholder: LANG.selectClient,
                    ajax: {
                        url: ROUTES.clients,
                        dataType: 'json',
                        delay: 250,
                        data: function (params) { return { q: params.term || '' }; },
                        processResults: function (data) {
                            return {
                                results: (data || []).map(function (item) {
                                    return { id: item.id, text: (item.company_name || item.name) + (item.phone ? ' - ' + item.phone : '') };
                                })
                            };
                        },
                        cache: true
                    },
                    minimumInputLength: 0
                };

                $('#client_id').select2(clientsSelect2Opts);
                $('#edit-client_id').select2(clientsSelect2Opts);

                $(document).on('click', '.toggle-btn[data-target-card="#createObjectCard"]', function () {
                    if (filterClientId && filterClientName) {
                        var $sel = $('#client_id');
                        if ($sel.length) {
                            $sel.empty().append(new Option(filterClientName, filterClientId, true, true)).trigger('change');
                        }
                    }
                });

                $(document).on('change', '#client_id, #edit-client_id', function () {
                    const id = $(this).attr('id');
                    const prefix = id.indexOf('edit-') === 0 ? 'edit-' : '';
                    const clientId = $(this).val();
                    const $vehicle = $(`#${prefix}vehicle_id`);
                    $vehicle.empty().append(new Option(LANG.selectVehicle, '', true, true)).trigger('change');
                    if (clientId) {
                        axios.get(ROUTES.vehiclesByClient, { params: { client_id: clientId, q: '' } }).then(function (res) {
                            const list = res.data || [];
                            $vehicle.empty().append(new Option(LANG.noVehicle, '', true, true));
                            list.forEach(function (item) {
                                $vehicle.append(new Option(item.text, item.id));
                            });
                            $vehicle.trigger('change');
                        });
                    }
                });

                const vehicleSelect2Opts = {
                    allowClear: true,
                    width: '100%',
                    placeholder: LANG.selectVehicle,
                    ajax: {
                        url: ROUTES.vehiclesByClient,
                        dataType: 'json',
                        delay: 250,
                        data: function (params) {
                            const prefix = $(this.element).data('prefix') || '';
                            const clientId = $(`#${prefix}client_id`).val();
                            return { client_id: clientId, q: params.term || '' };
                        },
                        processResults: function (data) {
                            return {
                                results: (data || []).map(function (item) {
                                    return { id: item.id, text: item.text };
                                })
                            };
                        },
                        cache: true
                    },
                    minimumInputLength: 0
                };

                $('#vehicle_id').select2({
                    ...vehicleSelect2Opts,
                    ajax: {
                        url: ROUTES.vehiclesByClient,
                        dataType: 'json',
                        delay: 250,
                        data: function (params) {
                            const clientId = $('#client_id').val();
                            return { client_id: clientId, q: params.term || '' };
                        },
                        processResults: function (data) {
                            return { results: (data || []).map(item => ({ id: item.id, text: item.text })) };
                        },
                        cache: true
                    }
                });
                
                $('#edit-vehicle_id').select2({
                    ...vehicleSelect2Opts,
                    ajax: {
                        url: ROUTES.vehiclesByClient,
                        dataType: 'json',
                        delay: 250,
                        data: function (params) {
                            const clientId = $('#edit-client_id').val();
                            return { client_id: clientId, q: params.term || '' };
                        },
                        processResults: function (data) {
                            return { results: (data || []).map(item => ({ id: item.id, text: item.text })) };
                        },
                        cache: true
                    }
                });

                if (filterClientId && filterClientName) {
                    $('.search-container').show(500);
                }

                axios.get(ROUTES.clients, { params: { q: '' } }).then(function (res) {
                    var data = res.data || [];
                    var $sel = $('#s-client_id');
                    $sel.find('option:not(:first)').remove();
                    data.forEach(function (item) {
                        $sel.append(new Option((item.company_name || item.name) + (item.phone ? ' - ' + item.phone : ''), item.id));
                    });
                    if (filterClientId && filterClientName) {
                        if (!$sel.find('option[value="' + filterClientId + '"]').length) {
                            $sel.append(new Option(filterClientName, filterClientId));
                        }
                        $sel.val(filterClientId).trigger('change');
                    }
                });
            })();

        });
    })();
</script>
@endpush
