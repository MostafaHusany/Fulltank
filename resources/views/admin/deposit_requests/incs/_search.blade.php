
<!-- START SEARCH BAR -->
<div style="display: none" class="search-container row g-2 px-2 py-3">
    <div class="col-12 col-sm-6 col-md-4 col-lg-2 search-action">
        <label class="form-label small mb-1">@lang('deposit_requests.Ref Number')</label>
        <input type="text" class="form-control form-control-sm" id="s-ref_number" placeholder="@lang('deposit_requests.Ref Number')">
    </div>
    <div class="col-12 col-sm-6 col-md-4 col-lg-2 search-action">
        <label class="form-label small mb-1">@lang('deposit_requests.Client')</label>
        <select class="form-select form-control form-select-sm" id="s-client_id">
            <option value="">@lang('layouts.all')</option>
        </select>
    </div>
    <div class="col-12 col-sm-6 col-md-4 col-lg-2 search-action">
        <label class="form-label small mb-1">@lang('deposit_requests.Status')</label>
        <select class="form-select form-control form-select-sm" id="s-status">
            <option value="">@lang('layouts.all')</option>
            <option value="pending">@lang('deposit_requests.pending')</option>
            <option value="approved">@lang('deposit_requests.approved')</option>
            <option value="rejected">@lang('deposit_requests.rejected')</option>
        </select>
    </div>
    <div class="col-12 col-sm-6 col-md-4 col-lg-2 search-action">
        <label class="form-label small mb-1">@lang('deposit_requests.Start Date')</label>
        <input type="date" class="form-control form-control-sm" id="s-start_date">
    </div>
    <div class="col-12 col-sm-6 col-md-4 col-lg-2 search-action">
        <label class="form-label small mb-1">@lang('deposit_requests.End Date')</label>
        <input type="date" class="form-control form-control-sm" id="s-end_date">
    </div>
</div>
<!-- END   SEARCH BAR -->
