import axios from 'axios'; 

/**
 * This is genaric model used to handle the pemissions table listing and selection.
 * This logic uses ajax to query and search permissions and can be a genraic use.
 *  
 * This model can be used in those administrations :
 * # Super User Dashboard :
 * 1- Users Administration
 * 2- Models Administration
 * 
 * # Workshop Manager Dashboard :
 * 1- Employees Administration
 * 
 * # Trucker Manager Dashboard :
 * 1- Employees Administration 
 * 
 * # Rental Manager Dashboard :
 * 1- Employees Administration 
 * 
 */


let title = Boolean(window.lang) && window.lang == 'ar' ? ' أدارة' : ' Administration ';

class PermissionModule {
    constructor ({ url, permission_table, loader }) {
        this.url              = url;
        this.loader           = loader;
        this.permission_table = permission_table;
    }

    store () {
        const meta = {
            permissions          : [],
            selected_permissions : [],
        };

        const getters = {
            fetchPermissions : async (q = null) => {
                try {
                    let res = await axios.get(this.url, {
                        params : {
                            q,
                            get_permissions : true,
                        }
                    });

                    let { data, success, msg } = res.data;
                    
                    if (!success)
                    throw msg

                    meta.permissions = [...data];

                    return data;
                } catch (err) {
                    console.log(`fetchPermissions Err : ${err}`);
                    failerToast(`@lang('layouts.object_error')`)
                }
            },

            fetchRolePermission : async (role_id) => {
                try {
                    let res = await axios.get(`${this.url}/${role_id}`, {
                        params : {
                            'fast_acc': true
                        }
                    });

                    let { data, success, msg } = res.data;

                    if (!success)
                    throw msg;

                    return [...data.permissions]
                } catch (err) {
                    console.log(`fetchPermissions Err : ${err}`);
                    failerToast(`@lang('layouts.object_error')`)
                }

            },
            
            getPermission : () => {
                return [...meta.permissions]
            },

            getSelectedPermission : () => {
                return [...meta.selected_permissions]
            }
        };

        const setters = {
            selectPermission : (permission_id) => {
                !(meta.selected_permissions.find(p_id => p_id == permission_id)) &&
                meta.selected_permissions.push(permission_id);
            },

            removePermission : (permission_id) => {
                meta.selected_permissions = meta.selected_permissions.filter(p_id => p_id != permission_id)
            },

            setSelectedPermissions : (selected_permissions) => {
                meta.selected_permissions = [...selected_permissions];
            },
        };

        return {
            getters,
            setters
        }
    }

    view () {
        const helpers = {
            parsePermissionTitle (permission_name) {
                let str = permission_name.split(' ');
                
                str = str.length > 1 ? str[1] : str;

                let tmp = str[0].toUpperCase() + str.substring(1);
                return tmp + title;
            }
        };

        const renders = {
            
            permissions : (permissions, prefix = '', is_disabled, selected_list = []) => {
                let permissions_el = ``;
                
                let current_permission_name = '';

                permissions.forEach(function (permission) {
                    let tmp = helpers.parsePermissionTitle(permission.display_name);

                    if (current_permission_name != tmp) {
                        permissions_el +=
                        `<tr colspan="2" class="fw-bold table-secondary">
                            <td><b>${tmp}</b><td>
                        </tr>`;

                        current_permission_name = tmp;
                    }

                    permissions_el += `
                        <tr>
                            <td>${permission.display_name}</td>
                            <td>
                                <input 
                                    type="checkbox" 
                                    data-id="${permission.id}"
                                    class="permissions-checkbox" 
                                    ${is_disabled ? 'disabled="disabled"' : ''} 
                                    ${selected_list.includes(permission.id) ? 'checked="checked"' : ''}
                                /> 
                            </td>
                        </tr>
                    `;
                });

                $(`#${prefix}permissions`).val(selected_list);
                $(`#${prefix}permissions-list`).html(permissions_el);
            }, 
            
            loadding : ({is_show = true , prefix = ''}) => {
                is_show 
                ?   $(`#${prefix + this.loader}`).fadeIn(500)
                :   $(`#${prefix + this.loader}`).fadeOut(500);
            }
        };
        
        return {
            renders
        }
    }
}


window.PermissionModule = PermissionModule;