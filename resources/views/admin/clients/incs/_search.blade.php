<div style="display: none" class="search-container row mb-2">
    <div class="col-6 col-md-2">
        <div class="my-2 search-action">
            <label>@lang('clients.Name')</label>
            <input type="text" class="form-control" id="s-name">
        </div>
    </div>
    <div class="col-6 col-md-2">
        <div class="my-2 search-action">
            <label>@lang('clients.Company Name')</label>
            <input type="text" class="form-control" id="s-company_name">
        </div>
    </div>
    <div class="col-6 col-md-2">
        <div class="my-2 search-action">
            <label>@lang('clients.Email')</label>
            <input type="text" class="form-control" id="s-email">
        </div>
    </div>
    <div class="col-6 col-md-2">
        <div class="my-2 search-action">
            <label>@lang('clients.Phone')</label>
            <input type="text" class="form-control" id="s-phone">
        </div>
    </div>
    <div class="col-6 col-md-2">
        <div class="my-2 search-action">
            <label>@lang('clients.Client Type')</label>
            <select class="form-control" id="s-client_category_id"><option value="">@lang('layouts.all')</option></select>
        </div>
    </div>
    <div class="col-6 col-md-2">
        <div class="my-2 search-action">
            <label>@lang('layouts.Active')</label>
            <select class="form-control" id="s-is_active">
                <option value="">@lang('layouts.all')</option>
                <option value="1">@lang('layouts.active')</option>
                <option value="0">@lang('layouts.de-active')</option>
            </select>
        </div>
    </div>
</div>
