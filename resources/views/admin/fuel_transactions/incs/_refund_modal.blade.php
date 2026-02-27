<!-- Refund Modal -->
<div class="modal fade" id="refundModal" tabindex="-1" aria-labelledby="refundModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="refundModalLabel">
                    <i class="fas fa-undo me-2"></i>
                    @lang('fuel_transactions.Refund Transaction')
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="refund-transaction-id">

                <div class="alert alert-info">
                    <i class="fas fa-info-circle me-2"></i>
                    @lang('fuel_transactions.refund_warning')
                    <br>
                    <strong>@lang('fuel_transactions.Reference'):</strong> <span id="refund-ref-no"></span>
                </div>

                <div class="mb-3">
                    <label for="refund_reason" class="form-label">
                        @lang('fuel_transactions.Refund Reason') <span class="text-danger">*</span>
                    </label>
                    <textarea class="form-control" id="refund_reason" rows="3" 
                              placeholder="@lang('fuel_transactions.Enter refund reason')" required></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">@lang('layouts.cancel')</button>
                <button type="button" class="btn btn-danger" id="confirm-refund-btn">
                    <i class="fas fa-undo me-1"></i>
                    @lang('fuel_transactions.Confirm Refund')
                </button>
            </div>
        </div>
    </div>
</div>
