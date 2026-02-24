<?php

namespace App\Http\Traits;

use Exception;
use Illuminate\Support\Facades\DB;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

use App\Models\User;
use App\Models\Student;

trait StudentTrait
{

    private $userObje;
    private $studentObje;

    public function storeStudent (Request $request) {
        $validator = Validator::make($request->all(), [
            'name'          => 'required|max:255',
            'phone'         => 'required|max:255|unique:users,phone',
            'email'         => 'nullable|email|unique:users,email|max:255',
            'birth_date'    => 'required|date',
            'password'      => 'required|min:8',
            'gove_id'       => 'required|exists:districts,id',
            'cent_id'       => 'required|exists:districts,id',
            'parent_id'     => 'nullable|exists:users,id',
            'preferences'   => 'nullable|exists:course_categories,id',
            'picture'       => 'image|max:10000'
        ]);

        if ($validator->fails()) {
            return ['success' => false, 'user' => null, 'err' => $validator->errors()];
        }
        
        $this->userObje     = new User;
        $this->studentObje  = new Student; 

        $data = $request->only($this->userObje->getFillable());
        
        $data['category'] = 'student';
        $data['password'] = isset($request->password) 
            ? bcrypt($request->password)
            : bcrypt('12345678');
        
        try {
            DB::beginTransaction();

                $user = $this->userObje->create($data);
                
                if(isset($request->picture))
                $data['picture'] = str_replace('public/', '', $request->file('picture')->store('public/media/students_pictures'));

                $user->wallet()->create();
                $user->student()->create($request->only($this->studentObje->getFillable()));
            
                if (isset($request->preferences))
                $user->student->preferences()->sync(is_array($request->preferences) ? $request->preferences : explode(',', $request->preferences));


            DB::commit();
        } catch(Exception $exception) {
            DB::rollback();

            dd('storeStudent, Exception : ', $exception);
            
            return ['success' => false, 'user' => null, 'err' => [__('students.object_error')]];
            // return ['success' => false, 'user' => null, 'err' => $exception];
        }

        return ['success' => true, 'user' => $user, 'err' => null];
    }

    public function updateStudent (Request $request, $user) {
        
        $validator = Validator::make($request->all(), [
            'name'          => 'required|max:255',
            'phone'         => 'required|max:255|unique:users,phone,'. $user->id,
            'email'         => 'nullable|email|max:255|unique:users,email,'. $user->id,
            'birth_date'    => 'required|date',
            'password'      => 'nullable|max:8',
            'gove_id'       => 'required|exists:districts,id',
            'cent_id'       => 'required|exists:districts,id',
            'parent_id'     => 'nullable|exists:users,id',
            'preferences'   => 'nullable|exists:course_categories,id',
            'picture'       => 'image|max:10000'
        ]);

        if ($validator->fails()) {
            return ['success' => false, 'user' => null, 'err' => $validator->errors()];
        }
        
        $this->userObje     = new User;
        $this->studentObje  = new Student; 

        $data = $request->only($this->userObje->getFillable());
        
        if(isset($request->picture))
        $data['picture'] = str_replace('public/', '', $request->file('picture')->store('public/media/students_pictures'));

        $data['category'] = $user->category;
        $data['password'] = isset($request->password) 
            ? bcrypt($request->password)
            : $user->password;
        
        try {
            DB::beginTransaction();
            
            $user->update($data);

            $data = $request->only($this->studentObje->getFillable());
            $data['is_top_student'] = $user->student->is_top_student;// not hacks is allowed

            $user->student()->update($data);
            
            if (!isset($user->wallet))
            $user->wallet()->create();

            $preferences = isset($request->preferences)
                ? ( is_array($request->preferences) ? $request->preferences : explode(',', $request->preferences) )
                : [];

            $user->student->preferences()->sync($preferences);
        
            DB::commit();
        } catch(Exception $exception) {
            DB::rollback();

            dd('storeStudent, Exception : ', $exception);
            
            return ['success' => false, 'user' => null, 'err' => [__('students.object_error')]];
        }
        
        return ['success' => true, 'user' => $user, 'err' => null];
    }

}