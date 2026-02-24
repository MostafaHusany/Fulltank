<div style="display: none" id="editObjectsCard" class="card card-body">
    <div class="row">
        <div class="col-6">
            <h5>@lang('roles.Update Role')</h5>
        </div>
        <div class="col-6 text-end">
            <div class="toggle-btn btn btn-default btn-sm" data-current-card="#editObjectsCard" data-target-card="#objectsCard">
                <i class="fas fa-times"></i>
            </div>
        </div>
    </div><!-- /.row -->
    <hr/>

    <form action="/" id="objectForm">
        <input type="hidden" id="edit-id">

        <div class="my-2 row">
            <label for="edit-name" class="col-sm-2 col-form-label">@lang('roles.Name') <span class="text-danger float-end">*</span></label>
            <div class="col-sm-10">
                <input type="text" class="form-control" id="edit-name" placeholder="@lang('roles.Name')">
                <div style="padding: 5px 7px; display: none" id="edit-nameErr" class="err-msg mt-2 alert alert-danger">
                </div>
            </div>
        </div><!-- /.my-2 -->
        
        <div class="my-2 row">
            <label for="edit-description" class="col-sm-2 col-form-label">@lang('roles.Description') <span class="text-danger float-end">*</span></label>
            <div class="col-sm-10">
                <textarea class="form-control" id="edit-description" placeholder="@lang('roles.Description')"></textarea>
                <div style="padding: 5px 7px; display: none" id="edit-descriptionErr" class="err-msg mt-2 alert alert-danger">
                </div>
            </div>
        </div><!-- /.my-2 -->
        
        <div class="my-2 row">
            <label for="edit-permissions" class="col-sm-2 col-form-label">@lang('roles.Permissions')</label>
            <div class="col-sm-10">
                <select name="permissions[]" id="edit-permissions" class="form-control" multiple="multiple"></select>
            </div>
        </div><!-- /.my-2 -->

        <div class="my-2 row">
            <label for="edit-users" class="col-sm-2 col-form-label">@lang('roles.Assign_Users')</label>
            <div class="col-sm-10">
                <select class="form-control" id="edit-users" data-prefix="" multiple="multiple"></select>
                <div style="padding: 5px 7px; display: none" id="edit-usersErr" class="err-msg mt-2 alert alert-danger">
                </div>
            </div>
        </div><!-- /.my-2 -->

        <button class="update-object btn btn-warning float-end">@lang('roles.Update Role')</button>
    </form>
</div>