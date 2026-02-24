
<div style="display: none" id="createObjectCard" class="card card-body">
    <div class="row">
        <div class="col-6">
            <h5>@lang('roles.Create Role')</h5>
        </div>
        <div class="col-6 text-end">
            <div class="toggle-btn btn btn-default btn-sm" data-current-card="#createObjectCard" data-target-card="#objectsCard">
                <i class="fas fa-times"></i>
            </div>
        </div>
    </div><!-- /.row -->
    <hr/>

    <form action="/" id="objectForm">
        <div class="my-2 row">
            <label for="name" class="col-sm-2 col-form-label">@lang('roles.Name') <span class="text-danger float-end">*</span></label>
            <div class="col-sm-10">
                <input type="text" class="form-control" id="name" placeholder="@lang('roles.Name')">
                <div style="padding: 5px 7px; display: none" id="nameErr" class="err-msg mt-2 alert alert-danger">
                </div>
            </div>
        </div><!-- /.my-2 -->
        
        <div class="my-2 row">
            <label for="description" class="col-sm-2 col-form-label">@lang('roles.Description') <span class="text-danger float-end">*</span></label>
            <div class="col-sm-10">
                <textarea class="form-control" id="description" placeholder="@lang('roles.Description')"></textarea>
                <div style="padding: 5px 7px; display: none" id="descriptionErr" class="err-msg mt-2 alert alert-danger">
                </div>
            </div>
        </div><!-- /.my-2 -->
        
        <div class="my-2 row">
            <label for="permissions" class="col-sm-2 col-form-label">@lang('roles.Permissions')</label>
            <div class="col-sm-10">
                <select name="permissions[]" id="permissions" class="form-control" multiple="multiple"></select>
            </div>
        </div><!-- /.my-2 -->

        <div class="my-2 row">
            <label for="users" class="col-sm-2 col-form-label">@lang('roles.Assign_Users')</label>
            <div class="col-sm-10">
                <select class="form-control" id="users" data-prefix="" multiple="multiple"></select>
                <div style="padding: 5px 7px; display: none" id="usersErr" class="err-msg mt-2 alert alert-danger">
                </div>
            </div>
        </div><!-- /.my-2 -->

        <button class="create-object btn btn-primary float-end">@lang('roles.Create Role')</button>
    </form>
</div>