@extends('layouts.admin.app')

@push('title')
    <h1 class="h2">@lang('clients.Title Administration')</h1>
@endpush

@section('content')
    <div id="objectsCard" class="card">
        <div class="card-header">
            <div class="row g-2 align-items-center">
                <div class="col-12 col-md-6 pt-1 order-2 order-md-1">
                    <span class="fw-bold">@lang('clients.Title Administration')</span>
                </div>
                <div class="col-12 col-md-6 order-1 order-md-2">
                    <div class="d-flex flex-wrap gap-1 justify-content-end">
                        @if($permissions == 'admin' || in_array('clients_delete', $permissions))
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
                        @if($permissions == 'admin' || in_array('clients_add', $permissions))
                        <button class="btn btn-sm btn-outline-primary toggle-btn" data-current-card="#objectsCard" data-target-card="#createObjectCard" title="@lang('clients.Create Client')">
                            <i class="fas fa-plus"></i>
                        </button>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <div class="card-body custome-table p-2 p-md-3">
            @include('admin.clients.incs._search')

            <div class="table-responsive">
            <table id="dataTable" class="table table-sm table-hover text-center mb-0">
                <thead>
                    <tr>
                        <th>@include('layouts.admin.incs._checkbox_select_all')</th>
                        <th>#</th>
                        <th>@lang('clients.Name')</th>
                        <th>@lang('clients.Company Name')</th>
                        <th>@lang('clients.Client Type')</th>
                        <th>@lang('clients.Email')</th>
                        <th>@lang('clients.Phone')</th>
                        <th>@lang('clients.Documents')</th>
                        <th>@lang('layouts.Active')</th>
                        <th>@lang('layouts.Actions')</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
            </div>
        </div>
    </div>

    @if($permissions == 'admin' || in_array('clients_add', $permissions))
        @include('admin.clients.incs._create')
    @endif

    @if($permissions == 'admin' || in_array('clients_show', $permissions))
        @include('admin.clients.incs._show')
    @endif

    @if($permissions == 'admin' || in_array('clients_edit', $permissions))
        @include('admin.clients.incs._edit')
    @endif

    @include('admin.clients.incs._documents_slideover')
    @include('admin.clients.incs._document_upload_modal')
@endSection

@push('custome-js')
<script>
    (function () {
        const ROUTES = {
            index      : "{{ route('admin.clients.index') }}",
            store      : "{{ route('admin.clients.store') }}",
            categories : "{{ route('admin.search.clientCategories') }}"
        };

        const LANG = {
            name_required         : '@lang("clients.name_required")',
            company_name_required : '@lang("clients.company_name_required")',
            client_category_required : '@lang("clients.client_category_required")',
            email_required        : '@lang("clients.email_required")',
            phone_required        : '@lang("clients.phone_required")',
            password_required     : '@lang("clients.password_required")',
            selectClientType      : '{{ __("clients.Client Type") }}'
        };

        const VALIDATION = {
            name            : LANG.name_required,
            company_name    : LANG.company_name_required,
            client_category : LANG.client_category_required,
            email           : LANG.email_required,
            phone           : LANG.phone_required
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
                { success_el : '#successAlert', danger_el : '#dangerAlert', warning_el : '#warningAlert' },
                {
                    table_id        : '#dataTable',
                    toggle_btn      : '.toggle-btn',
                    create_obj_btn  : '.create-object',
                    update_obj_btn  : '.update-object',
                    draft_obj_btn   : '.create-draft',
                    fields_list     : ['id', 'name', 'company_name', 'client_category', 'email', 'phone', 'password'],
                    imgs_fields     : ['picture']
                },
                [
                    { data: 'checkbox_selector',  name: 'checkbox_selector', orderable: false },
                    { data: 'id',                 name: 'id' },
                    { data: 'name',               name: 'name' },
                    { data: 'company_name',       name: 'company_name' },
                    { data: 'client_category_name', name: 'client_category_name' },
                    { data: 'email',              name: 'email' },
                    { data: 'phone',              name: 'phone' },
                    { data: 'documents_btn',      name: 'documents_btn' },
                    { data: 'activation',         name: 'activation' },
                    { data: 'actions',            name: 'actions' },
                ],
                function (d) {
                    if ($('#s-name').length) d.name = $('#s-name').val();
                    if ($('#s-company_name').length) d.company_name = $('#s-company_name').val();
                    if ($('#s-client_category_id').length) d.client_category_id = $('#s-client_category_id').val();
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
                        $(`#${prefix}${field}Err`).text(VALIDATION[field]).slideDown(500);
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
                    $('#show-company_name').text(data.company_name || '---');
                    $('#show-client_category_name').text(data.client_category_name || '---');
                    $('#show-email').text(data.email || '---');
                    $('#show-phone').text(data.phone || '---');
                    return true;
                } catch (err) {
                    const msg = err.response?.data?.msg || (typeof err === 'string' ? err : (Array.isArray(err) ? err[0] : 'Error'));
                    failerToast(Array.isArray(msg) ? msg[0] : msg);
                    return false;
                }
            };

            objects_dynamic_table.addDataToForm = (fields_id_list, imgs_fields, data, prefix) => {
                fields_id_list.forEach(el_id => {
                    if (!imgs_fields.includes(el_id) && el_id !== 'client_category')
                        $(`#${prefix}${el_id}`).val(data[el_id] ?? '').change();
                });
                if (prefix === 'edit-' && data.client_category_id != null && data.client_category_name != null) {
                    const $sel = $(`#${prefix}client_category`);
                    $sel.empty().append(new Option(data.client_category_name, data.client_category_id, true, true)).trigger('change');
                }
                $(`#${prefix}id`).val(data.id);
            };

            const categoriesSelect2Opts = {
                allowClear: true,
                width: '100%',
                placeholder: LANG.selectClientType,
                tags: true,
                ajax: {
                    url: ROUTES.categories,
                    dataType: 'json',
                    delay: 150,
                    data: function (params) { return { q: params.term || '' }; },
                    processResults: function (data) {
                        return { results: (data || []).map(item => ({ id: item.id, text: item.name })) };
                    },
                    cache: true
                },
                createTag: function (params) {
                    const term = $.trim(params.term);
                    if (term === '') return null;
                    return { id: term, text: term, newTag: true };
                }
            };

            $('#client_category').select2(categoriesSelect2Opts);
            $('#edit-client_category').select2(categoriesSelect2Opts);

            axios.get(ROUTES.categories).then(function (res) {
                const data = res.data || [];
                const $sel = $('#s-client_category_id');
                $sel.find('option:not(:first)').remove();
                data.forEach(item => $sel.append(new Option(item.name, item.id)));
            });

        });
    })();
</script>
@endpush
