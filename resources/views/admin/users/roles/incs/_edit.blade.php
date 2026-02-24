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
                <input type="hidden" name="edit-permissions[]" id="edit-permissions" style="display: none"></select>
                
                <div class="row">
                    <div class="col-10">
                        <input class="form-control" id="edit-search-permission" placeholder="@lang('layouts.Search_Permissions')" />
                    </div><!-- /.col-8 -->
                    <div class="col-2">
                        <div id="edit-spinner-border" style="display: none;" class="spinner-border" role="status">
                            <span class="sr-only">Loading...</span>
                        </div>
                    </div><!-- /.col-2 -->
                </div><!-- /.row --> 

                <div class="my-3" style="height: 200px; overflow-y: scroll">
                    <table class="table table-sm text-center">
                        <thead>
                            <tr>
                                <th>@lang('users.Permissions')</th>
                                <th>@lang('layouts.Actions')</th>
                            </tr>
                        </thead>
                        <tbody id="edit-permissions-list"></tbody>
                    </table>
                </div><!-- /.my-3 -->
            
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

@push('custome-js-2')
<script>
$(document).ready(function () {
    
    const init = (async () => {
        const prefix = 'edit-';
        const { renders, getters, setters } = window.permissionModule;
        
        $(`#edit-search-permission`).on('keyup', function () {
            let search_key = $(this).val();
            
            Boolean(window.search_permission) && clearInterval(window.search_permission);

            window.search_permission = setTimeout(async () => {
                renders.loadding({ is_show : true , prefix });

                let permissions = await getters.fetchPermissions(search_key);

                renders.permissions(getters.getPermission(), prefix, is_disabled = false, selected_list = getters.getSelectedPermission());
                
                renders.loadding({ is_show : false , prefix });
            }, 500);
        });

        $('#edit-permissions-list').on('click', '.permissions-checkbox', function () {
            let target_id  = $(this).data('id');
            let is_checked = $(this).prop('checked');
            
            if (target_id)
            Boolean(is_checked) 
            ? setters.selectPermission(target_id)
            : setters.removePermission(target_id);

            renders.permissions(getters.getPermission(), prefix, false, getters.getSelectedPermission());
        });

    })();
    
});
</script>
@endpush