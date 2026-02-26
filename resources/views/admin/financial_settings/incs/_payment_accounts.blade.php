<div class="row">
    <div class="col-12 col-lg-6 mb-3">
        <h6>@lang('deposit_requests.Payment Accounts')</h6>
        <div class="table-responsive">
            <table id="paymentMethodsTable" class="table table-sm table-hover">
                <thead><tr><th>#</th><th>@lang('deposit_requests.Name')</th><th>@lang('deposit_requests.Account Details')</th><th>@lang('layouts.Active')</th><th>@lang('layouts.Actions')</th></tr></thead>
                <tbody></tbody>
            </table>
        </div>
    </div>
    <div class="col-12 col-lg-6">
        <h6 id="pmFormTitle">@lang('layouts.add') @lang('deposit_requests.Payment Accounts')</h6>
        <form id="paymentMethodForm">
            <input type="hidden" id="pm-id">
            <div class="mb-2">
                <label class="form-label small">@lang('deposit_requests.Name')</label>
                <input type="text" class="form-control form-control-sm" id="pm-name">
            </div>
            <div class="mb-2">
                <label class="form-label small">@lang('deposit_requests.Account Details')</label>
                <input type="text" class="form-control form-control-sm" id="pm-account_details">
            </div>
            <div class="mb-2 form-check">
                <input type="checkbox" class="form-check-input" id="pm-is_active" checked>
                <label class="form-check-label small" for="pm-is_active">@lang('layouts.active')</label>
            </div>
            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-sm btn-primary">@lang('layouts.save')</button>
                <button type="button" class="btn btn-sm btn-outline-secondary" id="pm-cancel">@lang('layouts.cancel')</button>
            </div>
        </form>
    </div>
</div>

@push('custome-js')
<script>
(function () {
    var ROUTES = {
        index: "{{ route('admin.paymentMethods.index') }}",
        store: "{{ route('admin.paymentMethods.store') }}",
        update: "{{ route('admin.paymentMethods.update', ['id' => 'ID']) }}",
        destroy: "{{ route('admin.paymentMethods.destroy', ['id' => 'ID']) }}"
    };
    var pmTable = null;
    $('document').ready(function () {
        pmTable = new DynamicTable({ index_route: ROUTES.index, store_route: ROUTES.index, show_route: ROUTES.index, update_route: ROUTES.index, destroy_route: ROUTES.index, draft: { route: '', flag: '' } }, '#paymentMethodsTable', {}, { table_id: '#paymentMethodsTable', toggle_btn: '', create_obj_btn: '', update_obj_btn: '', draft_obj_btn: '', fields_list: [], imgs_fields: [] }, [
            { data: 'id' }, { data: 'name' }, { data: 'account_details' }, { data: 'status_badge' }, { data: 'actions' }
        ], function () {});
        pmTable.table_object.buttons().container().hide();
        $('#paymentMethodForm').on('submit', function (e) {
            e.preventDefault();
            var id = $('#pm-id').val();
            var data = { _token: $('meta[name="csrf-token"]').attr('content'), name: $('#pm-name').val(), account_details: $('#pm-account_details').val(), is_active: $('#pm-is_active').prop('checked') ? 1 : 0 };
            var url = id ? ROUTES.update.replace('ID', id) : ROUTES.store;
            var method = id ? 'put' : 'post';
            (method === 'put' ? axios.put(url, data) : axios.post(url, data)).then(function (r) {
                if (r.data.success) { successToast(r.data.msg); $('#paymentMethodForm')[0].reset(); $('#pm-id').val(''); $('#pm-is_active').prop('checked', true); pmTable.table_object.draw(); }
                else failerToast(r.data.msg || 'Error');
            }).catch(function (e) { failerToast(e.response?.data?.msg?.[0] || 'Error'); });
        });
        $('#pm-cancel').on('click', function () { $('#paymentMethodForm')[0].reset(); $('#pm-id').val(''); });
        $(document).on('click', '.pm-edit-btn', function () {
            $('#pm-id').val($(this).data('id'));
            $('#pm-name').val($(this).data('name'));
            $('#pm-account_details').val($(this).data('account-details') || '');
            $('#pm-is_active').prop('checked', $(this).data('is-active') == 1);
        });
        $(document).on('click', '.pm-delete-btn', function () {
            if (!confirm('@lang("layouts.delete")?')) return;
            var id = $(this).data('id');
            axios.delete(ROUTES.destroy.replace('ID', id), { data: { _token: $('meta[name="csrf-token"]').attr('content') } }).then(function (r) {
                if (r.data.success) { successToast(r.data.msg); pmTable.table_object.draw(); }
            });
        });
    });
})();
</script>
@endpush
