<!-- START SEARCH BAR -->
<div style="display: none" class="search-container row mb-2">

    <div class="col-md-2">
        <div class="my-2">
            <label for="s-reference_no">@lang('fuel_transactions.Reference')</label>
            <input type="text" class="form-control search-action" id="s-reference_no">
        </div>
    </div>

    <div class="col-md-2">
        <div class="my-2">
            <label for="s-client_id">@lang('fuel_transactions.Client')</label>
            <select class="form-control search-action" id="s-client_id"></select>
        </div>
    </div>

    <div class="col-md-2">
        <div class="my-2">
            <label for="s-station_id">@lang('fuel_transactions.Station')</label>
            <select class="form-control search-action" id="s-station_id"></select>
        </div>
    </div>

    <div class="col-md-2">
        <div class="my-2">
            <label for="s-status">@lang('fuel_transactions.Status')</label>
            <select class="form-control search-action" id="s-status">
                <option value="">@lang('layouts.all')</option>
                <option value="pending">@lang('fuel_transactions.status_pending')</option>
                <option value="completed">@lang('fuel_transactions.status_completed')</option>
                <option value="refunded">@lang('fuel_transactions.status_refunded')</option>
                <option value="cancelled">@lang('fuel_transactions.status_cancelled')</option>
            </select>
        </div>
    </div>

    <div class="col-md-2">
        <div class="my-2">
            <label for="s-date_from">@lang('fuel_transactions.Date From')</label>
            <input type="date" class="form-control search-action" id="s-date_from">
        </div>
    </div>

    <div class="col-md-2">
        <div class="my-2">
            <label for="s-date_to">@lang('fuel_transactions.Date To')</label>
            <input type="date" class="form-control search-action" id="s-date_to">
        </div>
    </div>

</div>
<!-- END SEARCH BAR -->
