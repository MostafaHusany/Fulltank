
<div style="display: none" id="showObjectsCard" class="card card-body">
    <div class="row g-2 align-items-center">
        <div class="col-8 col-md-6">
            <h5 class="mb-0">@lang('drivers.Show Driver')</h5>
        </div>
        <div class="col-4 col-md-6 text-end">
            <button type="button" class="toggle-btn btn btn-outline-dark btn-sm" data-current-card="#showObjectsCard" data-target-card="#objectsCard">
                <i class="fas fa-times"></i>
            </button>
        </div>
    </div>
    <hr class="my-3">

    <div class="row g-3">
        <div class="col-12 col-md-6">
            <label class="text-muted small">@lang('drivers.Name')</label>
            <div class="fw-semibold" id="show-name">---</div>
        </div>
        <div class="col-12 col-md-6">
            <label class="text-muted small">@lang('drivers.Email')</label>
            <div class="fw-semibold" id="show-email">---</div>
        </div>
        <div class="col-12 col-md-6">
            <label class="text-muted small">@lang('drivers.Phone')</label>
            <div class="fw-semibold" id="show-phone">---</div>
        </div>
        <div class="col-12 col-md-6">
            <label class="text-muted small">@lang('drivers.Client')</label>
            <div class="fw-semibold" id="show-client_name">---</div>
        </div>
        <div class="col-12 col-md-6">
            <label class="text-muted small">@lang('drivers.Vehicle')</label>
            <div class="fw-semibold" id="show-vehicle_display">---</div>
        </div>
    </div>
</div>

