<div class="modal fade" id="createSettlementModal" tabindex="-1" aria-labelledby="createSettlementModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="createSettlementModalLabel">
                    <i class="fas fa-hand-holding-usd me-2"></i>
                    @lang('settlements.Create Settlement')
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="settlement-station-id">

                <div class="alert alert-info mb-4">
                    <div class="row mb-2">
                        <div class="col-6">
                            <strong>@lang('settlements.Station'):</strong>
                            <span id="settlement-station-name">---</span>
                        </div>
                        <div class="col-6 text-end">
                            <strong>@lang('settlements.Available Balance'):</strong>
                            <span id="settlement-max-amount" class="text-success fw-bold">0.00</span> EGP
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-12">
                            <strong><i class="fas fa-university me-1"></i>@lang('settlements.Bank Account Details'):</strong>
                            <div id="settlement-bank-details" class="mt-1 p-2 bg-white rounded border" style="white-space: pre-wrap;">---</div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="amount" class="form-label">@lang('settlements.Amount') <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <input type="number" id="amount" class="form-control" step="0.01" min="0.01" required placeholder="0.00">
                            <span class="input-group-text">EGP</span>
                        </div>
                        <small class="text-muted">@lang('settlements.amount_hint')</small>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label for="payment_method" class="form-label">@lang('settlements.Payment Method') <span class="text-danger">*</span></label>
                        <select id="payment_method" class="form-control" required>
                            <option value="">@lang('settlements.Select Payment Method')</option>
                            <option value="cash">@lang('settlements.method_cash')</option>
                            <option value="bank_transfer">@lang('settlements.method_bank_transfer')</option>
                            <option value="check">@lang('settlements.method_check')</option>
                        </select>
                    </div>
                </div>

                <div class="mb-3">
                    <label for="transaction_details" class="form-label">@lang('settlements.Transaction Details')</label>
                    <textarea id="transaction_details" class="form-control" rows="2" placeholder="@lang('settlements.transaction_details_hint')"></textarea>
                </div>

                <div class="mb-3">
                    <label for="receipt_image" class="form-label">@lang('settlements.Receipt Image') <span class="text-danger">*</span></label>
                    <input type="file" id="receipt_image" class="form-control" accept="image/jpeg,image/jpg,image/png" required>
                    <small class="text-muted">@lang('settlements.receipt_hint')</small>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">@lang('layouts.Close')</button>
                <button type="button" class="btn btn-primary" id="submit-settlement-btn">
                    <i class="fas fa-check me-1"></i>
                    @lang('settlements.Confirm Settlement')
                </button>
            </div>
        </div>
    </div>
</div>
