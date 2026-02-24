@extends('layouts.admin.app')

@push('title')
    <h1 class="h2">@lang('clients.Title Administration')</h1>
@endpush

@section('content')
    <div id="objectsCard" class="card">
        <div class="card-header">
            <div class="row">
                <div class="col-6 pt-1">@lang('clients.Title Administration')</div>
                <div class="col-6 text-end">
                    @if($permissions == 'admin' || in_array('clients_delete', $permissions))
                    <button class="bulk-delete-btn btn btn-sm btn-outline-dark"><i class="fas fa-trash-alt"></i></button>
                    @endif
                    <button class="relode-btn btn btn-sm btn-outline-dark">
                        <i class="relode-btn-icon fas fa-sync-alt"></i>
                        <span class="relode-btn-loader spinner-grow spinner-grow-sm" style="display: none;"></span>
                    </button>
                    <button class="btn btn-sm btn-outline-dark toggle-search"><i class="fas fa-search"></i></button>
                    @if($permissions == 'admin' || in_array('clients_add', $permissions))
                    <button class="btn btn-sm btn-outline-primary toggle-btn" data-current-card="#objectsCard" data-target-card="#createObjectCard"><i class="fas fa-plus"></i></button>
                    @endif
                </div>
            </div>
        </div>
        <div class="card-body custome-table">
            @include('admin.clients.incs._search')
            <table id="dataTable" class="table text-center">
                <thead>
                    <tr>
                        <th>@include('layouts.admin.incs._checkbox_select_all')</th>
                        <th>#</th>
                        <th>@lang('clients.Name')</th>
                        <th>@lang('clients.Company Name')</th>
                        <th>@lang('clients.Client Type')</th>
                        <th>@lang('clients.Email')</th>
                        <th>@lang('clients.Phone')</th>
                        <th>@lang('layouts.Active')</th>
                        <th>@lang('layouts.Actions')</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    </div>
    @if($permissions == 'admin' || in_array('clients_add', $permissions))@include('admin.clients.incs._create')@endif
    @if($permissions == 'admin' || in_array('clients_show', $permissions))@include('admin.clients.incs._show')@endif
    @if($permissions == 'admin' || in_array('clients_edit', $permissions))@include('admin.clients.incs._edit')@endif
@endSection

@push('custome-js')
<script>
$('document').ready(function () {
    const objects_dynamic_table = new DynamicTable(
        { index_route: "{{ route('admin.clients.index') }}", store_route: "{{ route('admin.clients.store') }}", show_route: "{{ route('admin.clients.index') }}", update_route: "{{ route('admin.clients.index') }}", destroy_route: "{{ route('admin.clients.index') }}", draft: { route: '', flag: '' } },
        '#dataTable',
        { success_el: '#successAlert', danger_el: '#dangerAlert', warning_el: '#warningAlert' },
        { table_id: '#dataTable', toggle_btn: '.toggle-btn', create_obj_btn: '.create-object', update_obj_btn: '.update-object', draft_obj_btn: '.create-draft', fields_list: ['id', 'name', 'company_name', 'client_category', 'email', 'phone', 'password'], imgs_fields: ['picture'] },
        [
            { data: 'checkbox_selector', name: 'checkbox_selector', orderable: false },
            { data: 'id', name: 'id' },
            { data: 'name', name: 'name' },
            { data: 'company_name', name: 'company_name' },
            { data: 'client_category_name', name: 'client_category_name' },
            { data: 'email', name: 'email' },
            { data: 'phone', name: 'phone' },
            { data: 'activation', name: 'activation' },
            { data: 'actions', name: 'actions' },
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
    objects_dynamic_table.validateData = (data, prefix) => {
        var is_valide = true;
        $('.err-msg').slideUp(500);
        if (!data.get('name')) { is_valide = false; $('#' + prefix + 'nameErr').text('Name is required').slideDown(500); }
        if (!data.get('company_name')) { is_valide = false; $('#' + prefix + 'company_nameErr').text('Company name is required').slideDown(500); }
        if (!data.get('client_category')) { is_valide = false; $('#' + prefix + 'client_categoryErr').text('Client type is required').slideDown(500); }
        if (!data.get('email')) { is_valide = false; $('#' + prefix + 'emailErr').text('Email is required').slideDown(500); }
        if (!data.get('phone')) { is_valide = false; $('#' + prefix + 'phoneErr').text('Phone is required').slideDown(500); }
        if (prefix === '' && !data.get('password')) { is_valide = false; $('#passwordErr').text('Password is required').slideDown(500); }
        return is_valide;
    };
    objects_dynamic_table.showDataForm = async (targetBtn) => {
        var target_id = $(targetBtn).data('object-id');
        try {
            var response = await axios.get("{{ url('admin/clients') }}/" + target_id);
            var data = response.data.data, success = response.data.success, msg = response.data.msg;
            if (!success) throw msg;
            $('#show-name').text(data.name || '---');
            $('#show-company_name').text(data.company_name || '---');
            $('#show-client_category_name').text(data.client_category_name || '---');
            $('#show-email').text(data.email || '---');
            $('#show-phone').text(data.phone || '---');
            return true;
        } catch (err) {
            Toastify({ text: typeof err === 'string' ? err : (Array.isArray(err) ? err[0] : 'Error'), style: { background: '#f8d7da' }, offset: { x: 20, y: 50 } }).showToast();
            return false;
        }
    };
    objects_dynamic_table.addDataToForm = (fields_id_list, imgs_fields, data, prefix) => {
        fields_id_list.forEach(function(el_id) {
            if (imgs_fields.indexOf(el_id) === -1 && el_id !== 'client_category') {
                var val = data[el_id];
                $('#' + prefix + el_id).val(val != null ? val : '').change();
            }
        });
        if (prefix === 'edit-' && data.client_category_id != null && data.client_category_name != null) {
            var $sel = $('#edit-client_category');
            $sel.empty();
            var opt = new Option(data.client_category_name, data.client_category_id, true, true);
            $sel.append(opt).trigger('change');
        }
        $('#edit-id').val(data.id);
    };

    var categoriesSelect2Opts = {
        allowClear: true,
        width: '100%',
        placeholder: '{{ __("clients.Client Type") }}',
        tags: true,
        ajax: {
            url: '{{ route("admin.search.clientCategories") }}',
            dataType: 'json',
            delay: 150,
            data: function (params) { return { q: params.term || '' }; },
            processResults: function (data) {
                return {
                    results: data.map(function (item) { return { id: item.id, text: item.name }; })
                };
            },
            cache: true
        },
        createTag: function (params) {
            var term = $.trim(params.term);
            if (term === '') return null;
            return { id: term, text: term, newTag: true };
        }
    };
    $('#client_category').select2(categoriesSelect2Opts);
    $('#edit-client_category').select2(categoriesSelect2Opts);

    axios.get('{{ route("admin.search.clientCategories") }}').then(function (res) {
        var data = res.data || [];
        var $sel = $('#s-client_category_id');
        $sel.find('option:not(:first)').remove();
        data.forEach(function (item) {
            $sel.append(new Option(item.name, item.id));
        });
    });
});
</script>
@endpush
