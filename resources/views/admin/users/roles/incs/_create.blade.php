
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
        
        <div class="my-3 row">
            <label for="dreafted_data" class="col-sm-2 col-form-label">@lang('drafts.Drafted Data')</label>
            <div class="col-sm-10">
                <select class="form-control" id="dreafted_data"></select>
            </div>
        </div><!-- /.my-3 -->

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
                <input type="hidden" name="permissions[]" id="permissions" style="display: none"></select>
                
                <div class="row">
                    <div class="col-10">
                        <input class="form-control" id="search-permission" placeholder="@lang('layouts.Search_Permissions')" />
                    </div><!-- /.col-8 -->
                    <div class="col-2">
                        <div id="spinner-border" style="display: none;" class="spinner-border" role="status">
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
                        <tbody id="permissions-list"></tbody>
                    </table>
                </div><!-- /.my-3 -->
            
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
        
        <button class="create-draft btn btn-secondary float-end mx-2">@lang('drafts.Save Draft')</button>
    </form>
</div>

@push('custome-js-2')
<script>
$(document).ready(function () {    
    
    const init = (async () => {
    
        const { renders, getters, setters } = window.permissionModule;
        
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

        $('#dreafted_data').select2({
            allowClear: true,
            width: '100%',
            placeholder: `@lang('layouts.Select_Draft')`,
            ajax: {
                url: '{{ route("admin.search.drafts") }}',
                dataType: 'json',
                delay: 150,
                data : function (params) {
                    var query = {
                        q  : params.term,
                        section_flag : 'admin.roles'
                    }
                    return query;
                },
                processResults : function(data) {
                    return {
                        results: $.map(data, function(item) {
                            return {
                                text    : item.title,
                                id      : item.id
                            }
                        })
                    };
                },
                cache: true
            }
        });
        
    })();

});
</script>
@endpush