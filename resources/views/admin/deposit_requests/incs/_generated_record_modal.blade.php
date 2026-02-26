<div class="modal fade" id="generatedRecordModal" tabindex="-1" aria-labelledby="generatedRecordModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header py-2">
                <h5 class="modal-title" id="generatedRecordModalLabel">@lang('deposit_requests.Generated Balance Record')</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row g-2">
                    <div class="col-6 text-muted">@lang('deposit_requests.Deposit Amount')</div>
                    <div class="col-6" id="generatedRecordAmount">—</div>
                    <div class="col-6 text-muted">@lang('deposit_requests.Generated At')</div>
                    <div class="col-6" id="generatedRecordDate">—</div>
                    <div class="col-12 text-muted small mt-2" id="generatedRecordNotes"></div>
                </div>
            </div>
        </div>
    </div>
</div>
