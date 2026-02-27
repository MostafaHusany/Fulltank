<div style="display: none" class="search-container row mb-2">
    <div class="col-12 col-sm-6 col-md-3">
        <div class="my-2 search-action">
            <label for="s-station_name">@lang('station_wallets.Station Name')</label>
            <input type="text" class="form-control" id="s-station_name">
        </div>
    </div>
    <div class="col-12 col-sm-6 col-md-3">
        <div class="my-2 search-action">
            <label for="s-governorate_id">@lang('station_wallets.Governorate')</label>
            <select class="form-control" id="s-governorate_id"></select>
        </div>
    </div>
    <div class="col-12 col-sm-6 col-md-3">
        <div class="my-2 search-action">
            <label for="s-district_id">@lang('station_wallets.District')</label>
            <select class="form-control" id="s-district_id"></select>
        </div>
    </div>
    <div class="col-12 col-sm-6 col-md-3">
        <div class="my-2 search-action">
            <label for="s-is_active">@lang('station_wallets.Wallet Status')</label>
            <select class="form-control" id="s-is_active">
                <option value="">@lang('layouts.all')</option>
                <option value="1">@lang('layouts.active')</option>
                <option value="0">@lang('layouts.de-active')</option>
            </select>
        </div>
    </div>
</div>
