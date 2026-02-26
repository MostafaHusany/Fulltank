@extends('layouts.admin.app')

@push('title')
    <h1 class="h2">@lang('vehicles.Title Administration')</h1>
@endpush

@section('content')
    <div id="objectsCard" class="card">
        <div class="card-header">
            <div class="row g-2 align-items-center">
                <div class="col-12 col-md-6 pt-1 order-2 order-md-1">
                    <span class="fw-bold">@lang('vehicles.Title Administration')</span>
                </div>
                <div class="col-12 col-md-6 order-1 order-md-2">
                    <div class="d-flex flex-wrap gap-1 justify-content-end">
                        @if($permissions == 'admin' || in_array('vehicles_delete', $permissions))
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
                        @if($permissions == 'admin' || in_array('vehicles_add', $permissions))
                        <button class="btn btn-sm btn-outline-primary toggle-btn" data-current-card="#objectsCard" data-target-card="#createObjectCard" title="@lang('vehicles.Create Vehicle')">
                            <i class="fas fa-plus"></i>
                        </button>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <div class="card-body custome-table p-2 p-md-3">
            @include('admin.vehicles.incs._search')

            <div class="table-responsive">
            <table id="dataTable" class="table table-sm table-hover text-center mb-0">
                <thead>
                    <tr>
                        <th>@include('layouts.admin.incs._checkbox_select_all')</th>
                        <th>#</th>
                        <th>@lang('vehicles.Plate Number')</th>
                        <th>@lang('vehicles.Client')</th>
                        <th>@lang('vehicles.Model')</th>
                        <th>@lang('vehicles.Fuel Type')</th>
                        <th>@lang('layouts.Active')</th>
                        <th>@lang('layouts.Actions')</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
            </div>
        </div>
    </div><!-- /.card -->

    @if($permissions == 'admin' || in_array('vehicles_add', $permissions))
        @include('admin.vehicles.incs._create')
    @endif

    @if($permissions == 'admin' || in_array('vehicles_show', $permissions))
        @include('admin.vehicles.incs._show')
    @endif

    @if($permissions == 'admin' || in_array('vehicles_edit', $permissions))
        @include('admin.vehicles.incs._edit')
    @endif

@endSection

@push('custome-js')
<script>
    (function () {
        var filterClientId   = @json($filterClientId ?? null);
        var filterClientName = @json($filterClientName ?? null);

        const ROUTES = {
            index   : "{{ route('admin.vehicles.index') }}",
            store   : "{{ route('admin.vehicles.store') }}",
            clients : "{{ route('admin.search.clients') }}"
        };
        
        const LANG = {
            client_required   : '@lang("vehicles.client_required")',
            plate_required    : '@lang("vehicles.plate_number_required")',
            model_required    : '@lang("vehicles.model_required")',
            fuel_required     : '@lang("vehicles.fuel_type_required")',
            petrol            : '@lang("vehicles.Petrol")',
            diesel            : '@lang("vehicles.Diesel")',
            electric          : '@lang("vehicles.Electric")',
            hybrid            : '@lang("vehicles.Hybrid")',
            cng               : '@lang("vehicles.CNG")',
            active            : '@lang("layouts.active")',
            inactive          : '@lang("layouts.de-active")',
            historyPlaceholder: '{{ __("vehicles.History Placeholder") }}',
            selectClient      : '{{ __("vehicles.Select Client") }}'
        };

        const FUEL_MAP = { petrol: LANG.petrol, diesel: LANG.diesel, electric: LANG.electric, hybrid: LANG.hybrid, cng: LANG.cng };
        const VALIDATION = { client_id: LANG.client_required, plate_number: LANG.plate_required, model: LANG.model_required, fuel_type: LANG.fuel_required };

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
                    fields_list     : ['id', 'client_id', 'plate_number', 'model', 'fuel_type'],
                    imgs_fields     : []
                },
                [
                    { data: 'checkbox_selector',  name: 'checkbox_selector', orderable: false },
                    { data: 'id',                 name: 'id' },
                    { data: 'formatted_plate',    name: 'formatted_plate' },
                    { data: 'client_name',        name: 'client_name' },
                    { data: 'model',              name: 'model' },
                    { data: 'fuel_type',          name: 'fuel_type' },
                    { data: 'activation',         name: 'activation' },
                    { data: 'actions',            name: 'actions' },
                ],
                function (d) {
                    if (filterClientId) d.client_id = filterClientId;
                    else if ($('#s-client_id').length) d.client_id = $('#s-client_id').val();
                    if ($('#s-plate_number').length) d.plate_number = $('#s-plate_number').val();
                    if ($('#s-model').length) d.model = $('#s-model').val();
                    if ($('#s-fuel_type').length) d.fuel_type = $('#s-fuel_type').val();
                    if ($('#s-status').length) d.status = $('#s-status').val();
                }
            );

            objects_dynamic_table.validateData = (data, prefix = '') => {
                let valid = true;
                $('.err-msg').slideUp(500);

                Object.keys(VALIDATION).forEach(field => {
                    const val = data.get(field);
                    if (!val || val === '') {
                        valid = false;
                        $(`#${prefix}${field}Err`).text(VALIDATION[field]).slideDown(500);
                    }
                });
                return valid;
            };

            objects_dynamic_table.showDataForm = async (targetBtn) => {
                const id = $(targetBtn).data('object-id');
                try {
                    const { data, success, msg } = (await axios.get(`${ROUTES.index}/${id}`)).data;
                    if (!success) { failerToast(Array.isArray(msg) ? msg[0] : (msg || 'Error')); return false; }

                    $('#show-plate_number').text(data.formatted_plate_number || '---');
                    $('#show-client_name').text(data.client_name || '---');
                    $('#show-model').text(data.model || '---');
                    $('#show-fuel_type').text(data.fuel_type ? (FUEL_MAP[data.fuel_type] || data.fuel_type) : '---');
                    $('#show-status').html(data.status === 'active' ? `<span class="badge bg-success">${LANG.active}</span>` : `<span class="badge bg-warning">${LANG.inactive}</span>`);
                    return true;
                } catch (err) {
                    const msg = err.response?.data?.msg || (typeof err === 'string' ? err : (Array.isArray(err) ? err[0] : 'Error'));
                    failerToast(Array.isArray(msg) ? msg[0] : msg);
                    return false;
                }
            };

            objects_dynamic_table.addDataToForm = (fields_id_list, imgs_fields, data, prefix) => {
                fields_id_list.forEach(el_id => {
                    if (el_id !== 'client_id') $(`#${prefix}${el_id}`).val(data[el_id] ?? '').change();
                });
                if (prefix === 'edit-' && data.client_id) {
                    const $sel = $(`#${prefix}client_id`);
                    $sel.empty().append(new Option(data.client_name || `Client #${data.client_id}`, data.client_id, true, true)).trigger('change');
                }
                $(`#${prefix}id`).val(data.id);
            };

            (() => {
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

                $(document).on('click', '.vehicle-history-btn', function () {
                    successToast(`${LANG.historyPlaceholder}: ${$(this).data('plate')}`);
                });
            })();
            

        });
    })();
</script>
@endpush
