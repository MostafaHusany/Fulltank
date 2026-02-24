@extends('layouts.admin.app')

@push('title')
    <h1 class="h2">@lang('layouts.Roles')</h1>
@endpush

@section('content')
    <div id="objectsCard" class="card">
        <div class="card-header">
            <div class="row">
                <div class="col-6 pt-1">
                    @lang('roles.Title Adminstration')
                </div><!-- /.col-6 -->
                <div class="col-6 text-end">
                    
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
            @include('admin.roles.incs._search')

            <table id="dataTable" class="table text-center">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>@lang('roles.Name')</th>
                        <th>@lang('roles.Description')</th>
                        <th>@lang('roles.Users')</th>
                        <th>@lang('roles.Actions')</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div><!-- /.card-body -->
    </div><!-- /.card -->

    @if($permissions == 'admin' || in_array('roles_add', $permissions))
        @include('admin.roles.incs._create')
    @endif
    
    @if($permissions == 'admin' || in_array('roles_show', $permissions))
        @include('admin.roles.incs._show')
    @endif 
    
    @if($permissions == 'admin' || in_array('roles_edit', $permissions))
        @include('admin.roles.incs._edit')
    @endif 

@endSection

@push('custome-js')
<script>
    $('document').ready(function () {
            
        const objects_dynamic_table = new DynamicTable(
            {
                index_route   : "{{ route('admin.roles.index') }}",
                store_route   : "{{ route('admin.roles.store') }}",
                show_route    : "{{ route('admin.roles.index') }}",
                update_route  : "{{ route('admin.roles.index') }}",
                destroy_route : "{{ route('admin.roles.index') }}",
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
                fields_list     : ['id', 'name', 'description', 'users', 'permissions'],
                imgs_fields     : []
            },
            [
                { data: 'id', name: 'id' },
                { data: 'display_name', name: 'display_name' },
                { data: 'description', name: 'description' },
                { data: 'users', name: 'users' },
                { data: 'actions', name: 'actions' },
            ],
            function (d) {
                if ($('#s-name').length)
                d.name = $('#s-name').val();
                
                if ($('#s-users').length)
                d.users = $('#s-users').val();
            }
        );

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

            let response = await axios.get(`{{ url('admin/roles') }}/${target_role}`);

            let { data, success, msg } = response.data;
            
            if (success) {
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
            } else {
                Toastify({
                    text: msg,
                    className: "info",
                    offset: {
                        x: 20, // horizontal axis - can be a number or a string indicating unity. eg: '2em'
                        y: 50 // vertical axis - can be a number or a string indicating unity. eg: '2em'
                    },
                    style: {
                        color: '#842029', background: '#f8d7da', borderColor: '#f5c2c7'
                    }
                }).showToast();
            }

            return false;
        };
        
        objects_dynamic_table.addDataToForm = (fields_id_list, imgs_fields, data, prefix) => {
            $('#edit-id').val(data.id);

            fields_id_list = fields_id_list.filter(el_id => !imgs_fields.includes(el_id) );
            fields_id_list.forEach(el_id => {
                if (el_id == 'name') {
                    $(`#${prefix + el_id}`).val(data['display_name']).change();
                } else {
                    $(`#${prefix + el_id}`).val(data[el_id]).change();
                }
            });

            data.users.forEach(item => {
                let tmp = new Option(`${item.name} , email : (${item.email}) , phone : (${item.phone})`, item.id, false, true);
                $('#edit-users').append(tmp);
            });
            $('#edit-users').trigger('change');

            data.permissions.forEach(item => {
                let tmp = new Option(`${item.name}`, item.id, false, true);
                $('#edit-permissions').append(tmp);
            });
            $('#edit-permissions').trigger('change');
        };

        const init = (function () {
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

            $('#permissions, #edit-permissions').select2({
                allowClear: true,
                width: '100%',
                placeholder: '@lang("layouts.Select_Permissions")',
                ajax: {
                    url: '{{ url("admin/permissions-search") }}',
                    dataType: 'json',
                    delay: 150,
                    processResults: function (data) {
                        return {
                            results: $.map(data, function (item) {
                                return {
                                    text: `${item.display_name}`,
                                    id: item.id
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