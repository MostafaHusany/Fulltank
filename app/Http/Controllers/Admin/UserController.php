<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use Yajra\Datatables\Datatables;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

// use Illuminate\Support\Facades\Mail;

use App\Models\User;

use App\Http\Traits\ResponseTemplate;

class UserController extends Controller
{
    use ResponseTemplate;

    private $targetModel;

    public function __construct () {
        $this->targetModel = new User;
    }

    public function index (Request $request) {

        $permissions = auth()->user()->category == 'admin' 
            ? 'admin' 
            : $this->getPermissions(['users_add', 'users_edit', 'users_delete', 'users_show']);
        
        if ($request->ajax()) {
            $model = $this->targetModel->query()
            ->whereIn('category', ['admin', 'technical'])
            ->with(['roles'])
            ->orderBy('id', 'desc')
            ->adminFilter();
            
            $datatable_model = Datatables::of($model)
            ->addColumn('checkbox_selector', function ($row_object) use ($permissions) {
                return view('layouts.admin.incs._checkbox_selector', compact('row_object'));
            })
            ->addColumn('roles', function ($row_object) {
                return view('admin.users.users.incs._roles', compact('row_object'));
            })
            ->addColumn('activation', function ($row_object) use ($permissions) {
                return view('admin.users.users.incs._active', compact('row_object', 'permissions'));
            })
            ->addColumn('actions', function ($row_object) use ($permissions) {
                return view('admin.users.users.incs._actions', compact('row_object', 'permissions'));
            });

            return $datatable_model->make(true);
        }
        
        return view('admin.users.users.index', compact('permissions'));
    }

    public function store (Request $request) {
        $validator = Validator::make($request->all(), [
            'name'     => 'required|max:255',
            'email'    => 'required|unique:users,email|max:255',
            'phone'    => 'required|max:255|unique:users,phone', 
            'category' => 'required|in:admin,technical'
        ]);

        if ($validator->fails()) {
            return $this->responseTemplate(null, false, $validator->errors());
        }
        
        $data = $request->only($this->targetModel->getFillable());
        
        $data['password'] = isset($request->password) 
            ? bcrypt($request->password)
            : bcrypt('12345678');
    
        try {
            DB::beginTransaction();
            
                $user = $this->targetModel->create($data);
                
                // attach role or permissions to user
                if ($request->category == 'admin') {
                    $user->syncRoles([1]);
                } else if ($request->is_custome_permissions && isset($request->permissions)) {
                    $permissions = explode(',', $request->permissions);
                    $user->syncPermissions($permissions);
                } else if (isset($request->role)) {
                    $user->syncRoles([$request->role]);
                }

            DB::commit();
        } catch(Exception $exception) {
            DB::rollback();

            return $this->responseTemplate(null, false, [__('users.object_error')]);
        }

        return $this->responseTemplate($user, true, [__('users.new_user_was_created')]);
    }

    public function show ($id) {
        $user = $this->targetModel->with(['roles' => fn ($q) => $q->with(['permissions']), 'permissions'])->find($id);
        
        if (!isset($user)) {
            return $this->responseTemplate(null, false, __('users.not found user'));
        }

        return $this->responseTemplate($user, true);
    }

    public function update (Request $request, $id) {

        $user = $this->targetModel->find($id);
        
        if (!isset($user)) {
            return $this->responseTemplate(null, false, __('users.user not found'));
        }

        return isset($request->activate_object) 
            ? $this->activateUser($request, $user) 
            : $this->updateUser($request, $user) ;
    }

    public function destroy (Request $request, $id) {
        return $id == 0 && isset($request->selected_ids)
        ? $this->bulkDelete($request, $id)
        : $this->delete($id);
    }

    public function dataAjax (Request $request) {
    	$data = [];

        $search = $request->q;
        $query = $this->targetModel->query()
        ->select("id", "name", "phone", "email", "category")
        ->where(function ($q) use ($search) {
            $q->orWhere('name','LIKE',"%$search%");
            $q->orWhere('email','LIKE',"%$search%");
            $q->orWhere('phone','LIKE',"%$search%");
        });

        if (isset($request->category)) {
            $query->where('category', $request->category);
        }

        if (isset($request->categories)) {
            $query->whereIn('category', is_array($request->categories) ? $request->categories: explode(',', $request->categories));
        }

        $data = $query->get();
    
        return response()->json($data);
    }

    public function dataWalletUserAjax (Request $request) {
    	$data = [];
        
        if($request->has('q')){
            $search   = $request->q;
            $category = $request->category;
            
            $query = $this->targetModel->query()
            ->select("id", "name", "phone", "email", "category")
            ->where(function ($q) use ($search) {
                $q->orWhere('name',  'like', "%$search%");
                $q->orWhere('phone', 'like', "%$search%");
                // $q->orWhere('email', 'like', "%$search%");
            });

            if ($category == '') {
                $query->whereIn('category', ['client', 'workshop_manager']);
            } elseif ($category == 'client') {
                $query->where('category', 'client');
            } elseif ($category == 'workshop_manager') {
                $query->where('category', 'workshop_manager');
            }

            $data = $query->get();
        }
        
        return response()->json($data);
    }

    //  HELPER METHODS
    private function updateUser (Request $request, User $user) {
        $validator = Validator::make($request->all(), [
            'name'     => 'required|max:255',
            'phone'    => 'required|max:255|unique:users,phone,'.$user->id,
            'email'    => 'required|max:255|unique:users,email,'.$user->id,
            'category' => 'required|in:admin,technical'
        ]);

        if ($validator->fails()) {
            return $this->responseTemplate(null, false, $validator->errors());
        }
        
        $data = $request->only($this->targetModel->getFillable());
       
        $data['password'] = isset($request->password) 
            ? bcrypt($request->password)
            : $user->password;
        
        try {
            DB::beginTransaction();
            
            $user->update($data);
            
            // attach role or permissions to user
            if ($request->category == 'admin') {
                $user->syncRoles([1]);
            } else if ($request->is_custome_permissions && isset($request->permissions)) {
                $user->syncRoles([]);
                $permissions = explode(',', $request->permissions);
                $user->syncPermissions($permissions);
            } else if (isset($request->role)) {
                $user->syncPermissions([]);
                $user->syncRoles([$request->role]);
            } else {
                $user->syncRoles([]);
                $user->syncPermissions([]);
            }   

            DB::commit();
        } catch(Exception $exception) {
            DB::rollback();

            return $this->responseTemplate(null, false, [__('users.object_error')]);
        }

        return $this->responseTemplate($user, true, [__('users.user_was_updated')]);
    }
    
    private function activateUser (Request $request, User $user) {
        try {
            DB::beginTransaction();
            
            $user->is_active = !$user->is_active;
            $user->save();

            DB::commit();
        } catch(Exception $exception) {
            DB::rollback();

            return $this->responseTemplate(null, false, [__('users.object_error')]);
        }

        return $this->responseTemplate($user, true, __('users.user_was_updated'));
    }

    private function bulkDelete (Request $request, $id) {
        $this->targetModel->query()
        ->whereIn('id', is_array($request->selected_ids) ? $request->selected_ids : explode(',', $request->selected_ids))
        ->delete();
        
        return $this->responseTemplate(null, true, __('users.object_deleted'));
    }

    private function delete ($id) {
        $user = $this->targetModel->query()->find($id);

        if (!isset($user))
        return $this->responseTemplate(null, false, __('users.object_not_found'));
        
        $user->delete();

        return $this->responseTemplate($user, true, __('users.object_deleted'));
    } 

    // CUSTOME ROUTES FOR PROFILE
    public function myProfile () {
        $target_user = User::find(auth()->user()->id);

        return view('admin.users.profiles.index', compact('target_user'));
    }

    public function updateProfile (Request $request) {
        $target_user = auth()->user();
        
        $validator = Validator::make($request->all(), [
            'email'        => 'required|max:255|unique:users,email,' . $target_user->id,
            'phone'        => 'required|unique:users,phone,' . $target_user->id,
            'password_old' => 'required',
            'password'     => 'required|string|min:8|confirmed'
        ]);

        if ($validator->fails()) {
            return response()->json(['data' => null, 'success' => false, 'msg' => $validator->errors()]); 
        } else if (!Hash::check($request->password_old, auth()->user()->password)) {
            return response()->json(['data' => null, 'success' => false, 'msg' => ['password' => ['your password is not correct !']]]); 
        }

        $data = $request->except('password_old');
        $data['password'] = bcrypt($request->password);

        try {
            DB::beginTransaction();
            
            $target_user->update($data);
            
            DB::commit();
        } catch(Exception $exception) {
            DB::rollback();

            return $this->responseTemplate(null, false, [__('users.object_error')]);
        }

        return $this->responseTemplate($target_user, true, null);
    }

}
