<div class="modal fade" id="addBalanceModal" tabindex="-1" aria-labelledby="addBalanceModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addBalanceModalLabel">@lang('wallets.Add Balance') — <span id="addBalanceClientName"></span></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="addBalanceForm">
                    <input type="hidden" id="addBalanceWalletId">
                    <div class="mb-3">
                        <label for="addBalanceAmount" class="form-label">@lang('wallets.Amount') <span class="text-danger">*</span></label>
                        <input type="number" step="0.01" min="0.01" class="form-control" id="addBalanceAmount" required>
                        <div class="err-msg mt-1 alert alert-danger d-none" id="addBalanceAmountErr"></div>
                    </div>
                    <div class="mb-3">
                        <label for="addBalanceNotes" class="form-label">@lang('wallets.Notes')</label>
                        <textarea class="form-control" id="addBalanceNotes" rows="2"></textarea>
                        <div class="err-msg mt-1 alert alert-danger d-none" id="addBalanceNotesErr"></div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">@lang('layouts.cancel')</button>
                <button type="button" class="btn btn-primary" id="addBalanceSubmitBtn">@lang('wallets.Add Balance')</button>
            </div>
        </div>
    </div>
</div>

@push('custome-js')
<script>
(function () {
    var ROUTES = { deposit: "{{ route('admin.wallets.deposit') }}" };
    var LANG = { amount_required: '{{ __("wallets.amount_required") }}' };

    $('document').ready(function () {
        $(document).on('click', '.wallet-add-balance-btn', function () {
            var walletId = $(this).data('wallet-id');
            var clientName = $(this).data('client-name') || '';
            $('#addBalanceWalletId').val(walletId);
            $('#addBalanceClientName').text(clientName);
            $('#addBalanceForm')[0].reset();
            $('#addBalanceAmountErr, #addBalanceNotesErr').addClass('d-none');
            var modal = new bootstrap.Modal(document.getElementById('addBalanceModal'));
            modal.show();
        });

        $('#addBalanceSubmitBtn').on('click', function () {
            var walletId = $('#addBalanceWalletId').val();
            var amount = $('#addBalanceAmount').val();
            var notes = $('#addBalanceNotes').val();
            $('#addBalanceAmountErr').addClass('d-none');
            if (!amount || parseFloat(amount) < 0.01) {
                $('#addBalanceAmountErr').text(LANG.amount_required).removeClass('d-none');
                return;
            }
            var $btn = $(this);
            $btn.prop('disabled', true);
            axios.post(ROUTES.deposit, {
                _token: $('meta[name="csrf-token"]').attr('content'),
                wallet_id: walletId,
                amount: amount,
                notes: notes
            }).then(function (res) {
                if (res.data.success) {
                    bootstrap.Modal.getInstance(document.getElementById('addBalanceModal')).hide();
                    if (window.walletDataTable && window.walletDataTable.table_object) window.walletDataTable.table_object.draw();
                    if (typeof successToast === 'function') successToast(res.data.msg);
                } else {
                    var msg = res.data.msg;
                    if (msg && typeof msg === 'object' && msg.amount) $('#addBalanceAmountErr').html(Array.isArray(msg.amount) ? msg.amount[0] : msg.amount).removeClass('d-none');
                    if (msg && typeof msg === 'object' && msg.notes) $('#addBalanceNotesErr').html(Array.isArray(msg.notes) ? msg.notes[0] : msg.notes).removeClass('d-none');
                    if (typeof failerToast === 'function') failerToast(Array.isArray(msg) ? msg[0] : (typeof msg === 'string' ? msg : 'Error'));
                }
            }).catch(function (err) {
                var msg = err.response && err.response.data && err.response.data.msg;
                if (msg && typeof msg === 'object' && msg.amount) $('#addBalanceAmountErr').html(Array.isArray(msg.amount) ? msg.amount[0] : msg.amount).removeClass('d-none');
                if (typeof failerToast === 'function') failerToast(Array.isArray(msg) ? (msg[0] || 'Error') : (msg || 'Error'));
            }).finally(function () {
                $btn.prop('disabled', false);
            });
        });
    });
})();
</script>
@endpush
