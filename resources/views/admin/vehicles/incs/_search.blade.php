
<!-- START SEARCH BAR -->
<div style="display: none" class="search-container row g-2 px-2 py-3">
    <div class="col-12 col-sm-6 col-md-4 col-lg-2 search-action">
        <label class="form-label small mb-1">@lang('vehicles.Client')</label>
        <select class="form-select form-control form-select-sm" id="s-client_id"><option value="">@lang('layouts.all')</option></select>
    </div>
    <div class="col-12 col-sm-6 col-md-4 col-lg-2 search-action">
        <label class="form-label small mb-1">@lang('vehicles.Plate Number')</label>
        <input type="text" class="form-control form-control-sm" id="s-plate_number" placeholder="ABC 1234">
    </div>
    <div class="col-12 col-sm-6 col-md-4 col-lg-2 search-action">
        <label class="form-label small mb-1">@lang('vehicles.Model')</label>
        <input type="text" class="form-control form-control-sm" id="s-model">
    </div>
    <div class="col-12 col-sm-6 col-md-4 col-lg-2 search-action">
        <label class="form-label small mb-1">@lang('vehicles.Fuel Type')</label>
        <select class="form-select form-control form-select-sm" id="s-fuel_type">
            <option value="">@lang('layouts.all')</option>
            <option value="petrol">@lang('vehicles.Petrol')</option>
            <option value="diesel">@lang('vehicles.Diesel')</option>
            <option value="electric">@lang('vehicles.Electric')</option>
            <option value="hybrid">@lang('vehicles.Hybrid')</option>
            <option value="cng">@lang('vehicles.CNG')</option>
        </select>
    </div>
    <div class="col-12 col-sm-6 col-md-4 col-lg-2 search-action">
        <label class="form-label small mb-1">@lang('vehicles.Status')</label>
        <select class="form-select form-control form-select-sm" id="s-status">
            <option value="">@lang('layouts.all')</option>
            <option value="active">@lang('layouts.active')</option>
            <option value="inactive">@lang('layouts.de-active')</option>
        </select>
    </div>
</div>
<!-- END   SEARCH BAR -->
