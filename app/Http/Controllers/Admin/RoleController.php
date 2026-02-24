<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use Yajra\Datatables\Datatables;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

use App\Models\User;
use App\Models\Role;
use App\Models\RoleUser;
use App\Models\Permission;

use App\Http\Traits\ResponseTemplate;

class RoleController extends Controller
{
    use ResponseTemplate;

    public function index (Request $request) {

        $permissions = auth()->user()->category == 'admin' 
            ? 'admin' 
            : $this->getPermissions(['roles_add', 'roles_edit', 'roles_delete', 'roles_show']);

        if ($request->ajax()) {
            $model = Role::query()->with(['users']);

            if (isset($request->name)) {
                $model->where('name', 'like', '%' . $request->name . '%');
            }

            if (isset($request->users)) {
                $model->whereHas('users', function ($q) use ($request) {
                    $q->whereIn('users.id', $request->users);
                });
            }

            $datatable_model = Datatables::of($model)
            ->addColumn('checkbox_selector', function ($row_object) use ($permissions) {
                return view('layouts.admin.incs._checkbox_selector', compact('row_object'));
            })
            ->addColumn('users', function ($row_object) {
                return $row_object->users()->count();
            })
            ->addColumn('actions', function ($row_object) use ($permissions) {
                return view('admin.users.roles.incs._actions', compact('row_object', 'permissions'));
            });

            return $datatable_model->make(true);
        } else if ($request->get_permissions) {
            $model = Permission::query();

            if (isset($request->q))
            $model->where('display_name', 'like', "%$request->q%");

            $permissions = $model->get();

            return $this->responseTemplate($permissions, true, null);
        }

        return view('admin.users.roles.index', compact('permissions'));
    }
    
    public function store (Request $request) {
        $validator = Validator::make($request->all(), [
            'name'        => 'required|max:255|unique:roles,name',
            'description' => 'required|max:1000',
        ]);

        if ($validator->fails()) {
            return $this->responseTemplate(null, false, $validator->errors());
        }

        try {
            DB::beginTransaction();
            
            $role = Role::query()->create([
                'name'         => join('_', explode(' ', strtolower($request->name))),
                'display_name' => $request->name,
                'description'  => $request->description
            ]);

            // assign permissions to roles
            if (isset($request->permissions)) {
                $permissions = explode(',', $request->permissions);
                $role->syncPermissions($permissions);
            }
            
            // assign users to roles
            if (isset($request->users)) {
                $user = User::whereIn('id', explode(',', $request->users))->pluck('id')->toArray();
                
                // delete old user role, in this system user can has one role !
                RoleUser::query()->whereIn('user_id', $user)->delete();

                $user = array_map(fn ($user_id) => ['role_id' => $role->id, 'user_id' => $user_id, 'user_type' => 'App\Models\User'], $user);
                RoleUser::query()->insert($user);
            }
            DB::commit();
        } catch(Exception $exception) {
            DB::rollback();

            return $this->responseTemplate(null, false, [__('roles.object_error')]);
        }

        return $this->responseTemplate($role, true, __('roles.role_was_created'));
    }

    public function show ($id) {

        $role = Role::with(['users', 'permissions'])->find($id);

        if (!isset($role)) {
            return $this->responseTemplate(null, false, __('roles.role not found'));
        }
        
        return $this->responseTemplate($role, true, null);
    }

    public function update (Request $request, $id) {
        $validator = Validator::make($request->all(), [
            'name'        => "required|max:255|unique:roles,name,$id",
            'description' => 'required|max:1000'
        ]);

        if ($validator->fails()) {
            return $this->responseTemplate(null, false, $validator->errors());
        }

        $role = Role::find($id);

        $role->update([
            'name'         => join('_', explode(' ', strtolower($request->name))),
            'display_name' => $request->name,
            'description'  => $request->description
        ]);

        // assign permissions to roles
        if(isset($request->permissions)) {
            $permissions = explode(',', $request->permissions);
            $role->syncPermissions($permissions);
        }

        // assign users to roles
        if(isset($request->users)) {
            $users = User::whereIn('id', explode(',', $request->users))->pluck('id')->toArray();

            // delete old user role, in this system user can has one role !
            RoleUser::query()->whereIn('user_id', $users)->delete();

            $users = array_map(fn ($user_id) => ['role_id' => $role->id, 'user_id' => $user_id, 'user_type' => 'App\Models\User'], $users);
            RoleUser::query()->insert($users);
        }

        return $this->responseTemplate($role, true, __('roles.role was updated'));
    }

    public function destroy ($id) {
        $role = Role::with(['users', 'permissions'])->find($id);

        if (!isset($role)) {
            return $this->responseTemplate(null, false, __('roles.role not found'));
        }

        $role->delete();

        return $this->responseTemplate($role, true, __('roles.role was deleted'));
    }

    public function roleAjax (Request $request) {
    	$data = [];

        if($request->has('q')){
            $search = $request->q;
            $data = Role::select("id", "name", "display_name")
                    ->where('name', '!=', 'admin')
                    ->where(function ($q) use ($search){
                        $q->orWhere('name','LIKE',"%$search%")
                        ->orWhere('display_name','LIKE',"%$search%");
                    })->get();
        }

        return response()->json($data);
    }

    public function permissionAjax (Request $request) {
    	$data = [];
        
        if ($request->has('q')) {
            $search = $request->q;
            $data = Permission::select("id", "name", "display_name")
                    ->where(function ($q) use ($search){
                        $q->orWhere('name','LIKE',"%$search%")
                        ->orWhere('display_name','LIKE',"%$search%");
                    })
            		->get();
        } else {
            $data = Permission::all();
        }
        
        return response()->json($data);
    }

}
