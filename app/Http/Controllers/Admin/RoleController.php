<?php

namespace App\Http\Controllers\Admin;

use Log;
use Exception;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use Yajra\Datatables\Datatables;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

use App\Models\User;
use App\Models\Role;
use App\Models\RoleUser;
use App\Models\Permission;

use App\Services\RoleService;
use App\Http\Traits\ResponseTemplate;

class RoleController extends Controller
{
    use ResponseTemplate;

    protected RoleService $roleService;

    public function __construct(RoleService $roleService)
    {
        $this->roleService = $roleService;
    }

    public function index(Request $request)
    {
        $permissions = auth()->user()->category == 'admin' 
            ? 'admin' 
            : $this->getPermissions(['roles_add', 'roles_edit', 'roles_delete', 'roles_show']);

        if ($request->ajax()) {
            $model = Role::query()->with(['users', 'permissions']);

            if ($request->filled('name')) {
                $model->where(function ($q) use ($request) {
                    $q->where('name', 'like', '%' . $request->name . '%')
                      ->orWhere('display_name', 'like', '%' . $request->name . '%');
                });
            }

            if ($request->filled('users')) {
                $model->whereHas('users', function ($q) use ($request) {
                    $q->whereIn('users.id', $request->users);
                });
            }

            $datatable_model = Datatables::of($model)
                ->addColumn('checkbox_selector', function ($row_object) use ($permissions) {
                    return view('layouts.admin.incs._checkbox_selector', compact('row_object'));
                })
                ->addColumn('users', function ($row_object) {
                    return '<span class="badge bg-primary">' . $row_object->users->count() . '</span>';
                })
                ->addColumn('permissions_count', function ($row_object) {
                    return '<span class="badge bg-info">' . $row_object->permissions->count() . '</span>';
                })
                ->addColumn('is_protected', function ($row_object) {
                    if ($this->roleService->isProtectedRole($row_object)) {
                        return '<span class="badge bg-danger"><i class="fas fa-lock me-1"></i>' . __('roles.Protected') . '</span>';
                    }
                    return '<span class="badge bg-success"><i class="fas fa-unlock me-1"></i>' . __('roles.Editable') . '</span>';
                })
                ->addColumn('actions', function ($row_object) use ($permissions) {
                    return view('admin.users.roles.incs._actions', [
                        'row_object'   => $row_object,
                        'permissions'  => $permissions,
                        'is_protected' => $this->roleService->isProtectedRole($row_object),
                    ]);
                })
                ->rawColumns(['users', 'permissions_count', 'is_protected', 'actions']);

            return $datatable_model->make(true);
        }

        if ($request->get_permissions) {
            $model = Permission::query();

            if ($request->filled('q')) {
                $model->where(function ($q) use ($request) {
                    $q->where('display_name', 'like', "%{$request->q}%")
                      ->orWhere('name', 'like', "%{$request->q}%");
                });
            }

            $permissions = $model->get();
            return $this->responseTemplate($permissions, true, null);
        }

        if ($request->has('grouped_permissions')) {
            $grouped = $this->roleService->getPermissionsGrouped();
            return $this->responseTemplate($grouped, true, null);
        }

        return view('admin.users.roles.index', compact('permissions'));
    }
    
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name'        => 'required|max:255|unique:roles,display_name',
            'description' => 'required|max:1000',
        ]);

        if ($validator->fails()) {
            return $this->responseTemplate(null, false, $validator->errors());
        }

        try {
            $permissionIds = $request->filled('permissions') 
                ? array_map('intval', explode(',', $request->permissions)) 
                : null;

            $userIds = $request->filled('users') 
                ? array_map('intval', explode(',', $request->users)) 
                : null;

            $role = $this->roleService->createRole(
                [
                    'name'        => $request->name,
                    'description' => $request->description,
                ],
                $permissionIds,
                $userIds
            );

            return $this->responseTemplate($role->load(['permissions', 'users']), true, __('roles.role_was_created'));
        } catch (Exception $exception) {
            Log::error('RoleController@store Exception', ['error' => $exception->getMessage()]);
            return $this->responseTemplate(null, false, [__('roles.object_error')]);
        }
    }

    public function show($id)
    {
        $role = Role::with(['users', 'permissions'])->find($id);

        if (!$role) {
            return $this->responseTemplate(null, false, __('roles.role_not_found'));
        }

        $data = $role->toArray();
        $data['is_protected'] = $this->roleService->isProtectedRole($role);
        
        return $this->responseTemplate($data, true, null);
    }

    public function update(Request $request, $id)
    {
        $role = Role::find($id);

        if (!$role) {
            return $this->responseTemplate(null, false, __('roles.role_not_found'));
        }

        if ($this->roleService->isProtectedRole($role)) {
            return $this->responseTemplate(null, false, [__('roles.protected_role_error')]);
        }

        $validator = Validator::make($request->all(), [
            'name'        => "required|max:255|unique:roles,display_name,$id",
            'description' => 'required|max:1000'
        ]);

        if ($validator->fails()) {
            return $this->responseTemplate(null, false, $validator->errors());
        }

        try {
            $permissionIds = $request->filled('permissions') 
                ? array_map('intval', explode(',', $request->permissions)) 
                : [];

            $userIds = $request->filled('users') 
                ? array_map('intval', explode(',', $request->users)) 
                : [];

            $role = $this->roleService->updateRole(
                $role,
                [
                    'name'        => $request->name,
                    'description' => $request->description,
                ],
                $permissionIds,
                $userIds
            );

            return $this->responseTemplate($role, true, __('roles.role_was_updated'));
        } catch (Exception $exception) {
            Log::error('RoleController@update Exception', ['error' => $exception->getMessage()]);
            return $this->responseTemplate(null, false, [$exception->getMessage()]);
        }
    }

    public function destroy($id)
    {
        $role = Role::find($id);

        if (!$role) {
            return $this->responseTemplate(null, false, __('roles.role_not_found'));
        }

        if ($this->roleService->isProtectedRole($role)) {
            return $this->responseTemplate(null, false, [__('roles.protected_role_error')]);
        }

        try {
            $this->roleService->deleteRole($role);
            return $this->responseTemplate(null, true, __('roles.role_was_deleted'));
        } catch (Exception $exception) {
            Log::error('RoleController@destroy Exception', ['error' => $exception->getMessage()]);
            return $this->responseTemplate(null, false, [$exception->getMessage()]);
        }
    }

    public function roleAjax(Request $request)
    {
    	$data = [];

        if ($request->has('q')) {
            $search = $request->q;
            $data = Role::select("id", "name", "display_name")
                ->where('name', '!=', 'admin')
                ->where(function ($q) use ($search) {
                    $q->where('name', 'LIKE', "%$search%")
                      ->orWhere('display_name', 'LIKE', "%$search%");
                })->get();
        }

        return response()->json($data);
    }

    public function permissionAjax(Request $request)
    {
        $query = Permission::query();
        
        if ($request->has('q')) {
            $search = $request->q;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'LIKE', "%$search%")
                  ->orWhere('display_name', 'LIKE', "%$search%");
            });
        }

        $data = $query->get();
        
        return response()->json($data);
    }
}
