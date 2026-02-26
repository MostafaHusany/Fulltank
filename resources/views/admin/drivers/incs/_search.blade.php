<!-- START SEARCH BAR -->
<div style="display: none" class="search-container row g-2 px-2 py-3">
    <div class="col-12 col-sm-6 col-md-4 col-lg-2 search-action">
        <label class="form-label small mb-1">@lang('drivers.Client')</label>
        <select class="form-select form-control form-select-sm" id="s-client_id"><option value="">@lang('layouts.all')</option></select>
    </div>
    <div class="col-12 col-sm-6 col-md-4 col-lg-2 search-action">
        <label class="form-label small mb-1">@lang('drivers.Name')</label>
        <input type="text" class="form-control form-control-sm" id="s-name">
    </div>
    <div class="col-12 col-sm-6 col-md-4 col-lg-2 search-action">
        <label class="form-label small mb-1">@lang('drivers.Email')</label>
        <input type="text" class="form-control form-control-sm" id="s-email">
    </div>
    <div class="col-12 col-sm-6 col-md-4 col-lg-2 search-action">
        <label class="form-label small mb-1">@lang('drivers.Phone')</label>
        <input type="text" class="form-control form-control-sm" id="s-phone">
    </div>
    <div class="col-12 col-sm-6 col-md-4 col-lg-2 search-action">
        <label class="form-label small mb-1">@lang('layouts.Active')</label>
        <select class="form-select form-control form-select-sm" id="s-is_active">
            <option value="">@lang('layouts.all')</option>
            <option value="1">@lang('layouts.active')</option>
            <option value="0">@lang('layouts.de-active')</option>
        </select>
    </div>
</div>
<!-- END   SEARCH BAR -->
