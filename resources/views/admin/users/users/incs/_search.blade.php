
<!-- START SEARCH BAR -->
<div style="display: none" class="search-container row mb-2">
    <div class="col-4">
        <div class="my-2 search-action">
            <label for="">@lang('users.Name')</label>
            <input type="text" class="form-control" id="s-name">
        </div><!-- /.my-2 -->
    </div><!-- /.col-4 -->
    
    <div class="col-4">
        <div class="my-2 search-action">
            <label for="">@lang('users.Email')</label>
            <input type="text" class="form-control" id="s-email">
        </div><!-- /.my-2 -->
    </div><!-- /.col-4 -->

    <div class="col-4">
        <div class="my-2 search-action">
            <label for="">@lang('users.Phone')</label>
            <input type="text" class="form-control" id="s-phone">
        </div><!-- /.my-2 -->
    </div><!-- /.col-4 -->

    <div class="col-4">
        <div class="my-2 search-action">
            <label for="">@lang('users.Category')</label>
            <select type="text" class="form-control" id="s-category">
                <option value="">@lang('layouts.all')</option>
                <option value="admin">@lang('layouts.admin')</option>
                <option value="technical">@lang('layouts.technical')</option>
            </select>
        </div><!-- /.my-2 -->
    </div><!-- /.col-4 -->

    <div class="col-4">
        <div class="my-2 search-action">
            <label for="">@lang('layouts.Active')</label>
            <select type="text" class="form-control" id="s-is_active">
                <option value="">@lang('layouts.all')</option>
                <option value="1">@lang('layouts.active')</option>
                <option value="0">@lang('layouts.de-active')</option>
            </select>
        </div><!-- /.my-2 -->
    </div><!-- /.col-4 -->

    <div class="col-4">
        <div class="my-2 search-action">
            <label for="">@lang('users.Roles')</label>
            <select type="text" class="form-control" id="s-roles" multiple="multiple">
            </select>
        </div><!-- /.my-2 -->
    </div><!-- /.col-4 -->

</div><!-- /.row --> 
<!-- END   SEARCH BAR -->