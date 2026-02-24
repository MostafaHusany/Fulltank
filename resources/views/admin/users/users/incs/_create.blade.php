
<div style="display: none" id="createObjectCard" class="card card-body">
    <div class="row">
        <div class="col-6">
            <h5>@lang('users.Create User')</h5>
        </div>
        <div class="col-6 text-end">
            <div class="toggle-btn btn btn-outline-dark btn-sm" data-current-card="#createObjectCard" data-target-card="#objectsCard">
                <i class="fas fa-times"></i>
            </div>
        </div>
    </div><!-- /.row -->
    <hr/>

    <form action="/" id="objectForm">
        
        <div class="my-3 row">
            <label for="dreafted_data" class="col-sm-2 col-form-label">@lang('drafts.Drafted Data')</label>
            <div class="col-sm-10">
                <select class="form-control" id="dreafted_data"></select>
            </div>
        </div><!-- /.my-3 -->

        <div class="my-2 row">
            <label for="name" class="col-sm-2 col-form-label">@lang('users.Name') <span class="text-danger float-end">*</span></label>
            <div class="col-sm-10">
                <input type="text" class="form-control" id="name" placeholder="@lang('users.Name')">
                <div style="padding: 5px 7px; display: none" id="nameErr" class="err-msg mt-2 alert alert-danger">
                </div>
            </div>
        </div><!-- /.my-2 -->
        
        <div class="my-2 row">
            <label for="phone" class="col-sm-2 col-form-label">@lang('users.Phone') <span class="text-danger float-end">*</span></label>
            <div class="col-sm-10">
                <input type="phone" class="form-control" id="phone" placeholder="@lang('users.Phone')">
                <div style="padding: 5px 7px; display: none" id="phoneErr" class="err-msg mt-2 alert alert-danger">
                </div>
            </div>
        </div><!-- /.my-2 -->
        
        <div class="my-2 row">
            <label for="email" class="col-sm-2 col-form-label">@lang('users.Email') <span class="text-danger float-end">*</span></label>
            <div class="col-sm-10">
                <input type="email" class="form-control" id="email" placeholder="@lang('users.Email')">
                <div style="padding: 5px 7px; display: none" id="emailErr" class="err-msg mt-2 alert alert-danger">
                </div>
            </div>
        </div><!-- /.my-2 -->

        <div class="my-2 row">
            <label for="category" class="col-sm-2 col-form-label">@lang('users.Category') <span class="text-danger float-end">*</span></label>
            <div class="col-sm-10">
                <select class="form-control" id="category">
                    <option value="">@lang('layouts.select category')</option>
                    <option value="admin">@lang('layouts.admin')</option>
                    <option value="technical">@lang('layouts.technical')</option>
                </select>
                <div style="padding: 5px 7px; display: none" id="categoryErr" class="err-msg mt-2 alert alert-danger">
                </div>
            </div>
        </div><!-- /.my-2 -->
        
        <div class="my-2 row technical-options" style="display: none">
            <label for="role" class="col-sm-2 col-form-label">@lang('users.Role') <span class="text-danger float-end">*</span></label>
            <div class="col-sm-10">
                <select class="form-control" id="role"></select>
                <div style="padding: 5px 7px; display: none" id="roleErr" class="err-msg mt-2 alert alert-danger">
                </div>
            </div>
        </div><!-- /.my-2 -->
        
        <div class="my-2 row technical-options" style="display: none">
            <label for="permissions" class="col-sm-2 col-form-label">@lang('users.Permissions')</label>
           
            <div class="col-sm-6">
                <input type="hidden" name="permissions[]" id="permissions" style="display: none"></select>
                <input class="form-control" id="search-permission" placeholder="@lang('layouts.Search_Permissions')" disabled="disabled" />

                <div class="my-3" style="height: 200px; overflow-y: scroll">
                    <table class="table table-sm text-center">
                        <thead>
                            <tr>
                                <th>@lang('users.Permissions')</th>
                                <th>@lang('layouts.Actions')</th>
                            </tr>
                        </thead>
                        <tbody id="permissions-list"></tbody>
                    </table>
                </div><!-- /.my-3 -->
            </div>

            <label class="col-sm-3 pt-1">
                <span class="pr-2">@lang('users.Custome_Permissions')</span>
                <input type="hidden" id="is_custome_permissions" value="false">
                <input type="checkbox" id="is_custome_permissions_flag"> 
            </label>
            <div class="col-sm-1">
                <div id="spinner-border" style="display: none;" class="spinner-border" role="status">
                    <span class="sr-only">Loading...</span>
                </div>
            </div><!-- /.col-sm-1 -->
        </div><!-- /.my-2 -->
        
        <div class="my-2 row">
            <label for="password" class="col-sm-2 col-form-label">@lang('users.Password')</label>
            <div class="col-sm-10">
                <input type="text" class="form-control" id="password" placeholder="@lang('users.Password')">
            </div>
        </div><!-- /.my-2 -->

        <button class="create-object btn btn-primary float-end">@lang('users.Create User')</button>
        
        <button class="create-draft btn btn-secondary float-end mx-2">@lang('drafts.Save Draft')</button>
    </form>
</div>

@push('custome-js-2')
<script>
$(document).ready(function () {    
    
    const init = (async () => {

        const { renders, getters, setters } = window.permissionModule;
                
        $('#role').change(async function () {
            let role_id = $(this).val();

            if (Boolean(role_id)) {
                $('#spinner-border').show(500);

                let permissions = await getters.fetchRolePermission(role_id);
                
                renders.permissions(permissions, '', true);

                $('#spinner-border').hide(500);
            }
        });

        $('#category').change(function () {
            let target = $(this).val();

            if (target === 'admin') {
                $('.technical-options').slideUp(500);
            } else if (target === 'technical') {
                $('.technical-options').slideDown(500);
            }
        });
        
        $(`#search-permission`).on('keyup', async function () {
            let search_key = $(this).val();
            
            Boolean(window.search_permission) && clearInterval(window.search_permission);
            
            window.search_permission = setTimeout(async () => {
                renders.loadding({ is_show : true , prefix : '' });

                let permissions = await getters.fetchPermissions(search_key);

                renders.permissions(getters.getPermission(), prefix = '', is_disabled = false, selected_list = getters.getSelectedPermission());
                
                renders.loadding({ is_show : false , prefix : '' });
            }, 500);
        });

        $('#is_custome_permissions_flag').change(async function () {
            
            if ($(this).prop('checked')) {
                renders.loadding({ is_show : true , prefix : '' });
                renders.toggleLoading(true);
                                
                let permissions = await getters.fetchPermissions();

                renders.permissions(getters.getPermission(), prefix = '', is_disabled = false, selected_list = getters.getSelectedPermission());

                renders.loadding({ is_show : false , prefix : '' });

            } else {
                
                renders.toggleLoading(false);
                renders.permissions([], prefix = '', is_disabled = true);

            }
        });

        $('#permissions-list').on('click', '.permissions-checkbox', function () {
            let target_id  = $(this).data('id');
            let is_checked = $(this).prop('checked');
            
            if (target_id)
            Boolean(is_checked) 
            ? setters.selectPermission(target_id)
            : setters.removePermission(target_id);

            renders.permissions(getters.getPermission(), '', false, getters.getSelectedPermission());
        });

        $('.toggle-btn').on('click', function () {
            let target = $(this).data('target-card');

            // if the user is opening the create form and the form is empty
            // if form is empty means the user just submited the form
            // than clear the permissions
            if (!Boolean($('#name').val()) && target == '#createObjectCard') {
                setters.setSelectedPermissions([]);
                renders.permissions(getters.getPermission());    
            }
        });
        
    })();

});
</script>
@endpush