<!-- START SEARCH BAR -->
<div style="display: none" class="search-container row mb-2">

    <div class="col-md-3">
        <div class="my-2">
            <label for="s-station_id">@lang('station_workers.Station')</label>
            <select class="form-control search-action" id="s-station_id"></select>
        </div>
    </div>

    <div class="col-md-3">
        <div class="my-2">
            <label for="s-username">@lang('station_workers.Username')</label>
            <input type="text" class="form-control search-action" id="s-username">
        </div>
    </div>

    <div class="col-md-3">
        <div class="my-2">
            <label for="s-full_name">@lang('station_workers.Full Name')</label>
            <input type="text" class="form-control search-action" id="s-full_name">
        </div>
    </div>

    <div class="col-md-3">
        <div class="my-2">
            <label for="s-is_active">@lang('station_workers.Status')</label>
            <select class="form-control search-action" id="s-is_active">
                <option value="">@lang('layouts.all')</option>
                <option value="1">@lang('station_workers.Active')</option>
                <option value="0">@lang('station_workers.Inactive')</option>
            </select>
        </div>
    </div>

</div>
<!-- END SEARCH BAR -->
