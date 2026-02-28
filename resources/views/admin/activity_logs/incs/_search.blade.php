<!-- START SEARCH BAR -->
<div style="display: none" class="search-container row mb-2">

    <div class="col-md-2">
        <div class="my-2 search-action">
            <label for="s-causer_id">@lang('activity_logs.User')</label>
            <select class="form-control" id="s-causer_id">
                <option value="">@lang('layouts.all')</option>
                @foreach($admins as $admin)
                    <option value="{{ $admin->id }}">{{ $admin->name }}</option>
                @endforeach
            </select>
        </div>
    </div><!-- /.col-md-2 -->

    <div class="col-md-2">
        <div class="my-2 search-action">
            <label for="s-subject_type">@lang('activity_logs.Model')</label>
            <select class="form-control" id="s-subject_type">
                <option value="">@lang('layouts.all')</option>
                @foreach($modelTypes as $type => $label)
                    <option value="{{ $type }}">{{ $label }}</option>
                @endforeach
            </select>
        </div>
    </div><!-- /.col-md-2 -->

    <div class="col-md-2">
        <div class="my-2 search-action">
            <label for="s-event">@lang('activity_logs.Action')</label>
            <select class="form-control" id="s-event">
                <option value="">@lang('layouts.all')</option>
                <option value="created">@lang('activity_logs.Created')</option>
                <option value="updated">@lang('activity_logs.Updated')</option>
                <option value="deleted">@lang('activity_logs.Deleted')</option>
            </select>
        </div>
    </div><!-- /.col-md-2 -->

    <div class="col-md-2">
        <div class="my-2 search-action">
            <label for="s-date_from">@lang('activity_logs.Date From')</label>
            <input type="date" class="form-control" id="s-date_from">
        </div>
    </div><!-- /.col-md-2 -->

    <div class="col-md-2">
        <div class="my-2 search-action">
            <label for="s-date_to">@lang('activity_logs.Date To')</label>
            <input type="date" class="form-control" id="s-date_to">
        </div>
    </div><!-- /.col-md-2 -->

</div><!-- /.row -->
<!-- END SEARCH BAR -->
