<div>
    <h6>@lang('deposit_requests.Service Fees')</h6>
    <form id="feeSettingsForm" class="row g-3" style="max-width: 400px;">
        <div class="col-12">
            <label class="form-label">@lang('deposit_requests.Fee Type')</label>
            <select class="form-select" id="fee-type">
                <option value="fixed">@lang('deposit_requests.Fixed Amount')</option>
                <option value="percentage">@lang('deposit_requests.Percentage')</option>
            </select>
        </div>
        <div class="col-12">
            <label class="form-label">@lang('deposit_requests.Fee Value')</label>
            <input type="number" step="0.01" min="0" class="form-control" id="fee-value" placeholder="0">
        </div>
        <div class="col-12">
            <button type="submit" class="btn btn-primary">@lang('layouts.save')</button>
        </div>
    </form>
</div>

@push('custome-js')
<script>
(function () {
    var ROUTES = { get: "{{ route('admin.financialSettings.fee') }}", update: "{{ route('admin.financialSettings.updateFee') }}" };
    $('document').ready(function () {
        axios.get(ROUTES.get).then(function (r) {
            if (r.data.success && r.data.data) {
                $('#fee-type').val(r.data.data.fee_type || 'fixed');
                $('#fee-value').val(r.data.data.fee_value || 0);
            }
        });
        $('#feeSettingsForm').on('submit', function (e) {
            e.preventDefault();
            axios.put(ROUTES.update, {
                _token: $('meta[name="csrf-token"]').attr('content'),
                fee_type: $('#fee-type').val(),
                fee_value: $('#fee-value').val()
            }).then(function (r) {
                if (r.data.success) successToast(r.data.msg);
                else failerToast(r.data.msg || 'Error');
            }).catch(function (e) { failerToast(e.response?.data?.msg?.[0] || 'Error'); });
        });
    });
})();
</script>
@endpush
