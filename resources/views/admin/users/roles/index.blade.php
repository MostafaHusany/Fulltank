@extends('layouts.admin.app')

@push('title')
    <h1 class="h2">@lang('layouts.Roles')</h1>
@endpush

@section('content')
    <div id="objectsCard" class="card">
        <div class="card-header">
            <div class="row">
                <div class="col-6 pt-1">
                    @lang('roles.Title Administration')
                </div><!-- /.col-6 -->
                <div class="col-6 text-end">
                    
                    @if($permissions == 'admin' || in_array('roles_delete', $permissions))
                    <button class="bulk-delete-btn btn btn-sm btn-outline-dark">
                        <i class="fas fa-trash-alt"></i>
                    </button>
                    @endif

                    <button class="relode-btn btn btn-sm btn-outline-dark">
                        <i class="relode-btn-icon fas fa-sync-alt"></i>
                        <span class="relode-btn-loader spinner-grow spinner-grow-sm" style="display: none;" role="status" aria-hidden="true"></span>
                    </button>

                    <button class="btn btn-sm btn-outline-dark toggle-search">
                        <i class="fas fa-search"></i>
                    </button>
                    
                    @if($permissions == 'admin' || in_array('roles_add', $permissions))
                    <button class="btn btn-sm btn-outline-primary toggle-btn" data-current-card="#objectsCard" data-target-card="#createObjectCard">
                        <i class="fas fa-plus"></i>
                    </button>
                    @endif
                </div><!-- /.col-6 -->
            </div><!-- /.row -->
        </div><!-- /.card-header -->

        
        <div class="card-body custome-table">
            @include('admin.users.roles.incs._search')

            <table id="dataTable" class="table text-center">
                <thead>
                    <tr>
                        <th>@include('layouts.admin.incs._checkbox_select_all')</th>
                        <th>#</th>
                        <th>@lang('roles.Name')</th>
                        <th>@lang('roles.Description')</th>
                        <th>@lang('roles.Users')</th>
                        <th>@lang('roles.Permissions')</th>
                        <th>@lang('roles.Status')</th>
                        <th>@lang('roles.Actions')</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div><!-- /.card-body -->
    </div><!-- /.card -->

    @if($permissions == 'admin' || in_array('roles_add', $permissions))
        @include('admin.users.roles.incs._create')
    @endif
    
    @if($permissions == 'admin' || in_array('roles_show', $permissions))
        @include('admin.users.roles.incs._show')
    @endif 
    
    @if($permissions == 'admin' || in_array('roles_edit', $permissions))
        @include('admin.users.roles.incs._edit')
    @endif 

@endSection

@push('custome-js')
<script>
    $('document').ready(function () {
        
        // Start MagicTable
        const objects_dynamic_table = new DynamicTable(
            {
                index_route   : "{{ route('admin.roles.index') }}",
                store_route   : "{{ route('admin.roles.store') }}",
                show_route    : "{{ route('admin.roles.index') }}",
                update_route  : "{{ route('admin.roles.index') }}",
                destroy_route : "{{ route('admin.roles.index') }}",
                draft           : {
                    route : "{{ route('admin.draft.store') }}",
                    flag  : 'admin.roles'
                }
            },
            '#dataTable',
            {
                success_el : '#successAlert',
                danger_el  : '#dangerAlert',
                warning_el : '#warningAlert'
            },
            {
                table_id        : '#dataTable',
                toggle_btn      : '.toggle-btn',
                create_obj_btn  : '.create-object',
                update_obj_btn  : '.update-object',
                draft_obj_btn   : '.create-draft',
                fields_list     : ['id', 'name', 'description', 'users', 'permissions'],
                imgs_fields     : []
            },
            [
                { data: 'checkbox_selector',    name: 'checkbox_selector', 'orderable': false },
                { data: 'id',                   name: 'id' },
                { data: 'display_name',         name: 'display_name' },
                { data: 'description',          name: 'description' },
                { data: 'users',                name: 'users', 'orderable': false },
                { data: 'permissions_count',    name: 'permissions_count', 'orderable': false },
                { data: 'is_protected',         name: 'is_protected', 'orderable': false },
                { data: 'actions',              name: 'actions', 'orderable': false },
            ],
            function (d) {
                if ($('#s-name').length)
                d.name = $('#s-name').val();
                
                if ($('#s-users').length)
                d.users = $('#s-users').val();
            }
        );

        objects_dynamic_table.getDraftData = (data) => {

            data.append('users_label',    $('#users option:selected').text());

            return data;
        }

        objects_dynamic_table.validateData = (data, prefix = '') => {
            // inite validation flag
            let is_valide = true;

            // clear old validation session
            $('.err-msg').slideUp(500);

            if (data.get('name') === '') {
                is_valide = false;
                let err_msg = '@lang("roles.name_is_required")';
                $(`#${prefix}nameErr`).text(err_msg);
                $(`#${prefix}nameErr`).slideDown(500);
            }

            if (data.get('description') === '') {
                is_valide = false;
                let err_msg = '@lang("roles.description_is_required")';
                $(`#${prefix}descriptionErr`).text(err_msg);
                $(`#${prefix}descriptionErr`).slideDown(500);
            }

            if (data.get('users') === '') {
                data.delete('users');
            }
            
            if (data.get('permissions') === '') {
                data.delete('permissions');
            }

            return is_valide;
        };

        objects_dynamic_table.showDataForm = async (targetBtn) => {
        
            function renderUsers (users) {
                let usersEls = '';

                users.forEach(user => {
                    usersEls += `
                        <tr>
                            <td>${ user.id }</td>
                            <td>${Boolean(user.name) ? user.name : '---'}</td>
                            <td>${Boolean(user.category) ? user.category : '---'}</td>
                            <td>${Boolean(user.email) ? user.email : '---'}</td>
                            <td>${Boolean(user.phone) ? user.phone : '---'}</td>
                        </tr>
                    `;
                });

                $('#show-users').html(usersEls);
            }
            
            function clearForm() {
                let keys = ['name', 'description', 'permissions'];

                keys.forEach(key => {
                    $(`#show-${key}`).text('---');
                });

                $('#show-users').html('');
            }

            let keys        = ['name', 'description', 'permissions'];
            let target_role = $(targetBtn).data('object-id');
            
            clearForm();

            try {
                let response = await axios.get(`{{ url('admin/roles') }}/${target_role}`);

                let { data, success, msg } = response.data;

                if (!success)
                throw msg;

                keys.forEach(key => {
                    if (Boolean(data[key]) && key == 'permissions') {
                        let permissions = '';
                        data[key].forEach(permission => {
                            permissions += `<span class="badge bg-primary mx-1 rounded-pill">${permission.display_name}</span>`;
                        });
                        $(`#show-${key}`).html(permissions);
                    } else if (Boolean(data[key])) {
                        $(`#show-${key}`).text(data[key]);
                    } else {
                        $(`#show-${key}`).text('---');
                    }
                });

                renderUsers(data.users);

                return true;
            } catch (err) {
                window.failerToast(err);
            }

            return false;
        };
        
        objects_dynamic_table.addDataToForm = (fields_id_list, imgs_fields, data, prefix) => {

            // Call permissions module to render role permissions ...
            const { renders, getters, setters } = window.permissionModule;

            $('#edit-id').val(data.id);

            fields_id_list = fields_id_list.filter(el_id => !imgs_fields.includes(el_id) );
            fields_id_list.forEach(el_id => {
                if (el_id == 'name') {
                    $(`#${prefix + el_id}`).val(Boolean(data.display_name) ? data.display_name : data.name).change();
                } else {
                    $(`#${prefix + el_id}`).val(data[el_id]).change();
                }
            });

            if (Boolean(data.users) && Boolean(data.users_label))  {
                
                let option_el = new Option(data.users_label, data.users, true, true);
                $(`#${prefix}users`).append(option_el).trigger('change');
            } else if (Boolean(data.users)) {
                data.users?.forEach(item => {
                    let tmp = new Option(`${item.name} , email : (${item.email}) , phone : (${item.phone})`, item.id, false, true);
                    $(`#${prefix}users`).append(tmp);
                });                

                $(`#${prefix}users`).trigger('change');
            }

            let permissions = typeof data.permissions == 'string' 
            ? data.permissions.split(',').map(val => Number(val))
            : data.permissions.map(permission => permission.id);;

            setters.setSelectedPermissions(permissions);
            renders.permissions(getters.getPermission(), prefix, false, getters.getSelectedPermission());
        };

        const init = (async function () {
                    
            // This is a genaric class, that is defined in app.js

            // Start PermissionsModule
            const permissionModule = new PermissionModule({
                url              : `{{ route('admin.roles.index') }}`,
                loader           : 'spinner-border',
                permission_table : 'permissions-list'
            });
            
            const { renders }          = permissionModule.view();
            const { getters, setters } = permissionModule.store();
                
            // Make permission module global    
            window.permissionModule = { renders, getters, setters };
            
            renders.permissions(getters.getPermission(), prefix = '', is_disabled = false, selected_list = getters.getSelectedPermission());
            renders.permissions(getters.getPermission(), prefix = 'edit-', is_disabled = false, selected_list = getters.getSelectedPermission());

            let permissions = await getters.fetchPermissions();


            $('#users, #edit-users').select2({
                allowClear: true,
                width: '100%',
                placeholder: '@lang("layouts.Select_Users")',
                ajax: {
                    url: '{{ url("admin/users-search") }}?category=technical',
                    dataType: 'json',
                    delay: 150,
                    processResults: function (data) {
                        return {
                            results:  $.map(data, function (item) {
                                return {
                                    text: `${item.name} , email : (${item.email}) , phone : (${item.phone})`,
                                    id: item.id
                                }
                            })
                        };
                    },
                    cache: true
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