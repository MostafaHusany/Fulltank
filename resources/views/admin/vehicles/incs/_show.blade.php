
<div style="display: none" id="showObjectsCard" class="card card-body">
    <div class="row g-2 align-items-center">
        <div class="col-8 col-md-6">
            <h5 class="mb-0">@lang('vehicles.Show Vehicle')</h5>
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
            <label class="text-muted small">@lang('vehicles.Plate Number')</label>
            <div class="fw-semibold" id="show-plate_number">---</div>
        </div>
        <div class="col-12 col-md-6">
            <label class="text-muted small">@lang('vehicles.Client')</label>
            <div class="fw-semibold" id="show-client_name">---</div>
        </div>
        <div class="col-12 col-md-6">
            <label class="text-muted small">@lang('vehicles.Model')</label>
            <div class="fw-semibold" id="show-model">---</div>
        </div>
        <div class="col-12 col-md-6">
            <label class="text-muted small">@lang('vehicles.Fuel Type')</label>
            <div class="fw-semibold" id="show-fuel_type">---</div>
        </div>
        <div class="col-12 col-md-6">
            <label class="text-muted small">@lang('vehicles.Status')</label>
            <div id="show-status">---</div>
        </div>
    </div>
</div>
