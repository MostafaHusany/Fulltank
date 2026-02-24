<div style="display: none" id="editObjectsCard" class="card card-body">
    <div class="row">
        <div class="col-6">
            <h5>@lang('users.Update User')</h5>
        </div>
        <div class="col-6 text-end">
            <div class="toggle-btn btn btn-outline-dark btn-sm" data-current-card="#editObjectsCard" data-target-card="#objectsCard">
                <i class="fas fa-times"></i>
            </div>
        </div>
    </div><!-- /.row -->
    <hr/>

    <form action="/" id="objectForm">
        <input type="hidden" id="edit-id">

        <div class="my-2 row">
            <label for="edit-name" class="col-sm-2 col-form-label">@lang('users.Name') <span class="text-danger float-end">*</span></label>
            <div class="col-sm-10">
                <input type="text" class="form-control" id="edit-name" placeholder="@lang('users.Name')">
                <div style="padding: 5px 7px; display: none" id="edit-nameErr" class="err-msg mt-2 alert alert-danger">
                </div>
            </div>
        </div><!-- /.my-2 -->
        
        <div class="my-2 row">
            <label for="edit-phone" class="col-sm-2 col-form-label">@lang('users.Phone') <span class="text-danger float-end">*</span></label>
            <div class="col-sm-10">
                <input type="phone" class="form-control" id="edit-phone" placeholder="@lang('users.Phone')">
                <div style="padding: 5px 7px; display: none" id="edit-phoneErr" class="err-msg mt-2 alert alert-danger">
                </div>
            </div>
        </div><!-- /.my-2 -->

        <div class="my-2 row">
            <label for="edit-email" class="col-sm-2 col-form-label">@lang('users.Email') <span class="text-danger float-end">*</span></label>
            <div class="col-sm-10">
                <input type="email" class="form-control" id="edit-email" placeholder="@lang('users.Email')">
                <div style="padding: 5px 7px; display: none" id="edit-emailErr" class="err-msg mt-2 alert alert-danger">
                </div>
            </div>
        </div><!-- /.my-2 -->

        <div class="my-2 row">
            <label for="edit-category" class="col-sm-2 col-form-label">@lang('users.Category') <span class="text-danger float-end">*</span></label>
            <div class="col-sm-10">
                <select class="form-control" id="edit-category" placeholder="@lang('users.Category')">    
                    <option value="">@lang('layouts.select category')</option>
                    <option value="admin">@lang('layouts.admin')</option>
                    <option value="technical">@lang('layouts.technical')</option>
                </select>
            </div>
        </div><!-- /.my-2 -->

        <div class="my-2 row edit-technical-options" style="display: none">
            <label for="edit-role" class="col-sm-2 col-form-label">@lang('users.Role') <span class="text-danger float-end">*</span></label>
            <div class="col-sm-10">
                <select class="form-control" id="edit-role"></select>
                <div style="padding: 5px 7px; display: none" id="edit-roleErr" class="err-msg mt-2 alert alert-danger">
                </div>
            </div>
        </div><!-- /.my-2 -->
        
        <div class="my-2 row edit-technical-options" style="display: none">
            <label for="edit-permissions" class="col-sm-2 col-form-label">@lang('users.Permissions')</label>
           
            <div class="col-sm-6">
                <input type="hidden" name="edit-permissions[]" id="edit-permissions" style="display: none"></select>
                <input class="form-control" id="edit-search-permission" placeholder="@lang('layouts.Search_Permissions')" disabled="disabled" />

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
            
            <label class="col-sm-3 pt-1">
                <span class="pr-2">@lang('users.Custome_Permissions')</span>
                <input type="hidden" id="edit-is_custome_permissions" value="false">
                <input type="checkbox" id="edit-is_custome_permissions_flag"> 
            </label>
            <div class="col-sm-1">
                <div id="edit-spinner-border" style="display: none;" class="spinner-border" role="status">
                    <span class="sr-only">Loading...</span>
                </div>
            </div><!-- /.col-sm-1 -->
        </div><!-- /.my-2 -->

        <div class="my-2 row">
            <label for="edit-password" class="col-sm-2 col-form-label">@lang('users.Password')</label>
            <div class="col-sm-10">
                <input type="text" class="form-control" id="edit-password" placeholder="Password">
            </div>
        </div><!-- /.my-2 -->

        <button class="update-object btn btn-warning float-end">@lang('users.Update User')</button>
    </form>
</div>

@push('custome-js-2')
<script>
$(document).ready(function () {

    const init = (async () => {
        const prefix = 'edit-';
        const { renders, getters, setters } = window.permissionModule;
        
        $('#edit-role').change(async function () {
            let role_id = $(this).val();

            if (Boolean(role_id)) {
                renders.loadding({ is_show : true , prefix });

                let permissions = await getters.fetchRolePermission(role_id);
                
                renders.permissions(permissions, prefix, true);

                renders.loadding({ is_show : false , prefix });
            }
        });

        $('#edit-category').change(function () {
            let target = $(this).val();
            
            if (target === 'admin') {
                $('.edit-technical-options').slideUp(500);
            } else if (target === 'technical') {
                $('.edit-technical-options').slideDown(500);
            }
        });
        
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

        $('#edit-is_custome_permissions_flag').change(async function () {

            if ($(this).prop('checked')) {
                renders.loadding({ is_show : true , prefix });
                renders.toggleLoading(true, prefix);
                                
                let permissions = await getters.fetchPermissions();

                renders.permissions(getters.getPermission(), prefix, is_disabled = false, selected_list = getters.getSelectedPermission());

                renders.loadding({ is_show : false , prefix });

            } else {
                
                renders.toggleLoading(false, prefix);
                renders.permissions([], prefix, is_disabled = true);

            }
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