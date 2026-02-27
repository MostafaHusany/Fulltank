
<!-- START SEARCH BAR -->
<div style="display: none" class="search-container row mb-2">

    <div class="col-md-3">
        <div class="my-2 search-action">
            <label for="s-station_id">@lang('settlements.Station')</label>
            <select class="form-control" id="s-station_id">
                <option value="">@lang('settlements.All')</option>
            </select>
        </div><!-- /.my-2 -->
    </div><!-- /.col-md-3 -->

    <div class="col-md-2">
        <div class="my-2 search-action">
            <label for="s-payment_method">@lang('settlements.Payment Method')</label>
            <select class="form-control" id="s-payment_method">
                <option value="">@lang('settlements.All')</option>
                <option value="cash">@lang('settlements.method_cash')</option>
                <option value="bank_transfer">@lang('settlements.method_bank_transfer')</option>
                <option value="check">@lang('settlements.method_check')</option>
            </select>
        </div><!-- /.my-2 -->
    </div><!-- /.col-md-2 -->

    <div class="col-md-2">
        <div class="my-2 search-action">
            <label for="s-reference_no">@lang('settlements.Reference')</label>
            <input type="text" class="form-control" id="s-reference_no" placeholder="@lang('settlements.Reference')">
        </div><!-- /.my-2 -->
    </div><!-- /.col-md-2 -->

    <div class="col-md-2">
        <div class="my-2 search-action">
            <label for="s-date_from">@lang('settlements.Date From')</label>
            <input type="date" class="form-control" id="s-date_from">
        </div><!-- /.my-2 -->
    </div><!-- /.col-md-2 -->

    <div class="col-md-2">
        <div class="my-2 search-action">
            <label for="s-date_to">@lang('settlements.Date To')</label>
            <input type="date" class="form-control" id="s-date_to">
        </div><!-- /.my-2 -->
    </div><!-- /.col-md-2 -->

</div><!-- /.row -->
<!-- END   SEARCH BAR -->
