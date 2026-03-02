
<div style="display: none" id="createObjectCard" class="card card-body">
    <div class="row g-2 align-items-center">
        <div class="col-8 col-md-6">
            <h5 class="mb-0">@lang('deposit_requests.Create Request')</h5>
        </div>
        <div class="col-4 col-md-6 text-end">
            <button type="button" class="toggle-btn btn btn-outline-dark btn-sm" data-current-card="#createObjectCard" data-target-card="#objectsCard">
                <i class="fas fa-times"></i>
            </button>
        </div>
    </div>
    <hr class="my-3">

    <form action="/" id="objectForm" enctype="multipart/form-data">
        <div class="mb-3">
            <label class="form-label">@lang('deposit_requests.Client') <span class="text-danger">*</span></label>
            <select id="client_id" class="form-select" style="width:100%"></select>
            <div class="err-msg mt-2 alert alert-danger py-2" style="display: none;" id="client_idErr"></div>
        </div>
        <div class="mb-3">
            <label class="form-label">@lang('deposit_requests.Amount') <span class="text-danger">*</span></label>
            <input type="number" step="0.01" min="0.01" class="form-control" id="amount">
            <div class="err-msg mt-2 alert alert-danger py-2" style="display: none;" id="amountErr"></div>
        </div>
        <div class="mb-3 row">
            <div class="col-6">
                <label class="form-label text-muted small">@lang('deposit_requests.Fee')</label>
                <input type="text" class="form-control form-control-sm" id="fee_display" readonly>
            </div>
            <div class="col-6">
                <label class="form-label text-muted small">@lang('deposit_requests.Total to Pay')</label>
                <input type="text" class="form-control form-control-sm fw-bold" id="total_display" readonly>
            </div>
        </div>
        <div class="mb-3">
            <label class="form-label">@lang('deposit_requests.Payment Method') <span class="text-danger">*</span></label>
            <select id="payment_method_id" class="form-select"></select>
            <div class="err-msg mt-2 alert alert-danger py-2" style="display: none;" id="payment_method_idErr"></div>
        </div>
        <div class="mb-3">
            <label class="form-label">@lang('deposit_requests.Proof Image') <span class="text-danger">*</span></label>
            <input type="file" class="form-control" id="proof_image" accept="image/*">
            <div class="err-msg mt-2 alert alert-danger py-2" style="display: none;" id="proof_imageErr"></div>
        </div>
        <div class="d-flex flex-wrap gap-2 justify-content-end mt-3">
            <button type="button" class="toggle-btn btn btn-outline-secondary" data-current-card="#createObjectCard" data-target-card="#objectsCard">@lang('layouts.cancel')</button>
            <button type="button" class="create-object btn btn-primary">@lang('deposit_requests.Create Request')</button>
        </div>
    </form>
</div>
