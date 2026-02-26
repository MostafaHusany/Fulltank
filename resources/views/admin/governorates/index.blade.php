@extends('layouts.admin.app')

@push('title')
<h1 class="h2">@lang('governorates.Title')</h1>
@endpush

@section('content')
<div id="objectsCard" class="card">
<div class="card-header">
<div class="row">
<div class="col-6 pt-1">@lang('governorates.Title')</div>
<div class="col-6 text-end">
@if($permissions == 'admin' || in_array('governorates_delete', $permissions ?? []))
<button class="bulk-delete-btn btn btn-sm btn-outline-dark"><i class="fas fa-trash-alt"></i></button>
@endif
<button class="relode-btn btn btn-sm btn-outline-dark">
<i class="relode-btn-icon fas fa-sync-alt"></i>
<span class="relode-btn-loader spinner-grow spinner-grow-sm" style="display:none;"></span>
</button>
<button class="btn btn-sm btn-outline-dark toggle-search"><i class="fas fa-search"></i></button>
@if($permissions == 'admin' || in_array('governorates_add', $permissions ?? []))
<button class="btn btn-sm btn-outline-primary toggle-btn" data-current-card="#objectsCard" data-target-card="#createObjectCard"><i class="fas fa-plus"></i></button>
@endif
</div>
</div>
</div>
<div class="card-body custome-table">
@include('admin.governorates.incs._search')
<table id="dataTable" class="table text-center">
<thead>
<tr>
<th>@include('layouts.admin.incs._checkbox_select_all')</th>
<th>#</th>
<th>@lang('governorates.Name')</th>
<th>@lang('governorates.Districts')</th>
<th>@lang('layouts.Actions')</th>
</tr>
</thead>
<tbody></tbody>
</table>
</div>
</div>

@if($permissions == 'admin' || in_array('governorates_add', $permissions ?? []))
@include('admin.governorates.incs._create')
@endif

@if($permissions == 'admin' || in_array('governorates_show', $permissions ?? []))
@include('admin.governorates.incs._show')
@endif

@if($permissions == 'admin' || in_array('governorates_edit', $permissions ?? []))
@include('admin.governorates.incs._edit')
@include('admin.governorates.incs._districts_form')
@endif
@endSection

@push('custome-js')
<script>
$('document').ready(function () {
const ROUTES = {
    index: "{{ route('admin.governorates.index') }}",
    store: "{{ route('admin.governorates.store') }}",
    update: "{{ route('admin.governorates.update', ['governorate' => 'ID']) }}",
    show: "{{ route('admin.governorates.show', ['governorate' => 'ID']) }}",
    destroy: "{{ route('admin.governorates.destroy', ['governorate' => 'ID']) }}",
    districtsStore: "{{ route('admin.governorates.districts.store') }}",
    districtsUpdate: "{{ route('admin.governorates.districts.update', ['id' => 'ID']) }}",
    districtsDestroy: "{{ route('admin.governorates.districts.destroy', ['id' => 'ID']) }}"
};

const objects_dynamic_table = new DynamicTable(
    { index_route: ROUTES.index, store_route: ROUTES.store, show_route: ROUTES.index, update_route: ROUTES.index, destroy_route: ROUTES.index, draft: { route: '', flag: '' } },
    '#dataTable',
    { success_el: '#successAlert', danger_el: '#dangerAlert', warning_el: '#warningAlert' },
    { table_id: '#dataTable', toggle_btn: '.toggle-btn', create_obj_btn: '.create-object', update_obj_btn: '.update-object', draft_obj_btn: null, fields_list: ['id', 'name'], imgs_fields: [] },
    [
        { data: 'checkbox_selector', name: 'checkbox_selector', orderable: false },
        { data: 'id', name: 'id' },
        { data: 'name', name: 'name' },
        { data: 'districts_btn', name: 'districts_btn' },
        { data: 'actions', name: 'actions' }
    ],
    function (d) { if ($('#s-name').length) d.name = $('#s-name').val(); }
);

objects_dynamic_table.validateData = function (data, prefix) {
    $('.err-msg').slideUp(500);
    var p = prefix || '';
    if (!data.get('name')) {
        $('#' + p + 'nameErr').text('@lang("governorates.name_required")').slideDown(500);
        return false;
    }
    return true;
};

objects_dynamic_table.addDataToForm = function (fields_list, imgs_fields, data, prefix) {
    var p = prefix || '';
    fields_list.forEach(function (f) {
        if (f !== 'id' && $('#' + p + f).length) $('#' + p + f).val(data[f] ?? '').change();
    });
    if (p === 'edit-') $('#edit-id').val(data.id);
};

objects_dynamic_table.showDataForm = async function (targetBtn) {
    var id = $(targetBtn).data('object-id');
    try {
        var res = await axios.get(ROUTES.show.replace('ID', id));
        var d = res.data;
        if (d.success && d.data) {
            $('#show-name').val(d.data.name || '');
            var html = '';
            (d.data.districts || []).forEach(function (dist) {
                html += '<tr><td>' + (dist.name || '') + '</td></tr>';
            });
            $('#show-districtsTable').html(html || '<tr><td class="text-muted">—</td></tr>');
            return true;
        }
        failerToast(d.msg || 'Error');
    } catch (e) {
        failerToast(e.response?.data?.msg || 'Error');
    }
    return false;
};

$('.relode-btn').on('click', function () { objects_dynamic_table.table_object.draw(); });

var meta = { gove: null, districts: [] };
function fetchGovernorate(id) {
    return axios.get(ROUTES.show.replace('ID', id)).then(function (r) {
        var d = r.data;
        if (d.success && d.data) {
            meta.gove = d.data;
            meta.districts = d.data.districts || [];
            return meta;
        }
        throw d.msg;
    });
}
function renderDistricts() {
    var html = '';
    meta.districts.forEach(function (d) {
        html += '<tr><td><input type="text" class="form-control form-control-sm district-edit-input" data-id="' + d.id + '" value="' + (d.name || '') + '"></td><td><button class="btn btn-sm btn-warning update-district" data-id="' + d.id + '"><i class="fas fa-edit"></i></button> <button class="btn btn-sm btn-danger delete-district" data-id="' + d.id + '"><i class="fas fa-trash-alt"></i></button></td></tr>';
    });
    $('#districtsTable').html(html || '<tr><td colspan="2" class="text-muted">—</td></tr>');
}

$('#dataTable').on('click', '.manage-districts', function () {
    var id = $(this).data('target');
    if (!id) return;
    $('#loddingSpinner').show();
    fetchGovernorate(id).then(function () {
        renderDistricts();
        $('#manageDistricts').find('[name="governorate_id"]').remove();
        $('#manageDistricts').prepend('<input type="hidden" name="governorate_id" value="' + meta.gove.id + '">');
        $('#objectsCard').slideUp(500);
        $('#manageDistricts').slideDown(500);
    }).catch(function (e) { failerToast(e.response?.data?.msg || 'Error'); }).finally(function () { $('#loddingSpinner').hide(); });
});

$('#add-district').on('click', function () {
    var name = $('#district-name').val().trim();
    var govId = $('input[name="governorate_id"]').val();
    if (!name || !govId) { failerToast('@lang("governorates.name_required")'); return; }
    $(this).prop('disabled', true);
    axios.post(ROUTES.districtsStore, { _token: $('meta[name="csrf-token"]').attr('content'), governorate_id: govId, name: name }).then(function (r) {
        if (r.data.success) {
            successToast(r.data.msg);
            meta.districts.push(r.data.data);
            renderDistricts();
            $('#district-name').val('');
        } else { failerToast(r.data.msg || 'Error'); }
    }).catch(function (e) { failerToast(e.response?.data?.msg || 'Error'); }).finally(function () { $('#add-district').prop('disabled', false); });
});

$('#districtsTable').on('click', '.update-district', function () {
    var id = $(this).data('id');
    var name = $(this).closest('tr').find('.district-edit-input').val().trim();
    if (!name) { failerToast('@lang("governorates.name_required")'); return; }
    axios.put(ROUTES.districtsUpdate.replace('ID', id), { _token: $('meta[name="csrf-token"]').attr('content'), _method: 'PUT', name: name }).then(function (r) {
        if (r.data.success) {
            successToast(r.data.msg);
            var idx = meta.districts.findIndex(function (d) { return d.id == id; });
            if (idx >= 0) meta.districts[idx] = r.data.data;
            renderDistricts();
        } else { failerToast(r.data.msg || 'Error'); }
    }).catch(function (e) { failerToast(e.response?.data?.msg || 'Error'); });
});

$('#districtsTable').on('click', '.delete-district', function () {
    var id = $(this).data('id');
    if (!confirm('@lang("layouts.delete")?')) return;
    axios.delete(ROUTES.districtsDestroy.replace('ID', id), { data: { _token: $('meta[name="csrf-token"]').attr('content') } }).then(function (r) {
        if (r.data.success) {
            successToast(r.data.msg);
            meta.districts = meta.districts.filter(function (d) { return d.id != id; });
            renderDistricts();
        } else { failerToast(r.data.msg || 'Error'); }
    }).catch(function (e) { failerToast(e.response?.data?.msg || 'Error'); });
});

$('.toggle-btn[data-target-card="#manageDistricts"]').on('click', function () {
    $('#manageDistricts').slideUp(500);
    $('#objectsCard').slideDown(500);
});
});
</script>
@endpush
