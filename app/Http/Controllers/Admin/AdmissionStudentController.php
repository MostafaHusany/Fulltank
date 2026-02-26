<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use Log;
use Exception;
use LaravelLocalization;
use Yajra\Datatables\Datatables;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

use App\Models\User;
use App\Models\Semester;
use App\Models\Admission;
use App\Models\AdmissionStudent;

use App\Http\Traits\ResponseTemplate;

class AdmissionStudentController extends Controller
{
    use ResponseTemplate;

    private $userModel;
    private $semester;
    private $targetModel;

    public function __construct () {
        $this->userModel    = new User;
        $this->semester     = new Semester;
        $this->targetModel  = new AdmissionStudent;
    }

    public function index (Request $request) {

        $is_ar = LaravelLocalization::getCurrentLocale() == 'ar'; 

        $permissions = auth()->user()->category == 'admin' 
            ? 'admin' 
            : $this->getPermissions(['admissionStudents_add', 'admissionStudents_edit', 'admissionStudents_delete', 'admissionStudents_show']);

        $semester = $this->semester->query()
            ->where('is_active', 1)
            ->first();

        if (isset($request->fast_access)) {
            
            $students = $this->targetModel->query()
            ->with(['level', 'class'])
            ->where('class_id', '!=', null)
            ->whereHas('semester', fn ($q) => $q->where('is_active', true))
            ->orderBy('id', 'desc')
            ->adminFilter()
            ->get();

            return $this->responseTemplate($students, true);
        }

        if (isset($request->students_list)) {
            
            $query = $this->targetModel->query()
            ->with(['grade', 'level', 'semester'])
            ->whereIn('id', is_array($request->students_list) ? $request->students_list : explode(',', $request->students_list))
            ->orderBy('id', 'desc');

            $students = $query->get();

            return $this->responseTemplate($students, true);
        }

        if ($request->ajax()) {
            $model = $this->targetModel->query()
            ->with([
                'admission.semester', 'grade', 'level', 'semester', 'class',
                'payments' => fn ($q) => $q->whereHas('semester', fn ($q) => $q->where('is_active', 1))
            ])
            ->withCount(['payments' => fn ($q) => $q->whereHas('semester', fn ($q) => $q->where('is_active', 1)) ])
            ->addSelect([
                'admission_reference_number' => Admission::select('reference_number')
                ->whereColumn('admissions.id', 'admission_students.admission_id') // adjust relation key
                ->limit(1)
            ])
            ->orderBy('admission_reference_number')
            ->orderBy('id', 'desc')
            ->adminFilter();
            
            $datatable_model = Datatables::of($model)
            ->addColumn('checkbox_selector', function ($row_object) use ($permissions) {
                return view('layouts.admin.incs._checkbox_selector', compact('row_object'));
            })
            ->addColumn('semester', function ($row_object) use ($permissions, $semester) {
                if (!$semester || $row_object->semester_id != $semester->id) {
                    return view('admin.admission_students.incs._shift_btn', compact('row_object'));
                }
                return $row_object->semester?->title ?? '---';
            })
            ->addColumn('reports', function ($row_object) use ($permissions) {
                return view('admin.admission_students.incs._reports_btn', compact('row_object'));
            })
            ->addColumn('admission_number', function ($row_object) use ($permissions) {
                return isset($row_object->admission) ? $row_object->admission->reference_number : '---';
            })
            ->addColumn('grade', function ($row_object) use ($is_ar) {
                return isset($row_object->grade) ? ($is_ar ? $row_object->grade->ar_title : $row_object->grade->en_title) : '---';
            })
            ->addColumn('level', function ($row_object) use ($is_ar) {
                return isset($row_object->level) ? ($is_ar ? $row_object->level->ar_title : $row_object->level->en_title) : '---';
            })
            ->addColumn('class', function ($row_object) use ($is_ar) {
                return isset($row_object->class) ? $row_object->class->name : '---';
            })
            ->addColumn('status', function ($row_object) use ($permissions, $is_ar) {
                return view('admin.admission_students.incs._status', compact('row_object', 'permissions', 'is_ar'));
            })
            ->addColumn('guardian_name', function ($row_object) {
                return isset($row_object->admission) ? $row_object->admission->responsible_1_name : '---';
            })
            ->addColumn('guardian_phone', function ($row_object) {
                return isset($row_object->admission) ? $row_object->admission->responsible_1_phone_num : '---';
            })
            ->addColumn('semester_payment', function ($row_object) {
                return view('admin.admission_students.incs._semester_payment', compact('row_object'));
            })
            ->addColumn('bus', function ($row_object) use ($permissions) {
                return view('admin.admission_students.incs._bus_btn', compact('row_object', 'permissions'));
            })
            ->addColumn('account', function ($row_object) use ($permissions) {
                return view('admin.admission_students.incs._auth_btn', compact('row_object', 'permissions'));
            })
            ->addColumn('all_payments', function ($row_object) {
                $payments_count = isset($row_object->payments_count) ? $row_object->payments_count : 0;
                return view('admin.admission_students.incs._payment_btn', compact('row_object', 'payments_count'));
            })
            ->addColumn('actions', function ($row_object) use ($permissions) {
                return view('admin.admission_students.incs._actions', compact('row_object', 'permissions'));
            });

            return $datatable_model->make(true);
        }
        
        return view('admin.admission_students.index', compact('permissions', 'is_ar'));
    }
    
    public function store (Request $request) {
        $validator = Validator::make($request->all(), $this->getValidationRules());

        if ($validator->fails()) {
            return $this->responseTemplate(null, false, $validator->errors());
        }
        
        $data           = $request->only($this->targetModel->getFillable()); 
        $data['name']   = trim("{$data['first_name']} {$data['second_name']} {$data['third_name']}");
        $data['status'] = 'wating'; 
        
        try {
            DB::beginTransaction();
            
            $admissionStudent = $this->targetModel->create($data);
            $admissionStudent->load(['grade', 'level', 'semester']);

            DB::commit();
        } catch(Exception $exception) {
            DB::rollback();

            Log::error('AdmissionStudentController@store Exception', ['error' => $exception->getMessage()]);
            
            return $this->responseTemplate(null, false, [__('admission_students.object_error')]);
        }

        return $this->responseTemplate($admissionStudent, true, [__('admission_students.object_created')]);
    }

    public function show ($id) {
        $admissionStudent = $this->targetModel
        ->with(['semester', 'bus', 'admission.semester', 'grade', 'level', 'user'])->find($id);
        
        if (!$admissionStudent) {
            return $this->responseTemplate(null, false, __('admission_students.object_not_found'));
        }

        return $this->responseTemplate($admissionStudent, true);
    }

    public function update (Request $request, $id) {

        $admissionStudent = $this->targetModel->find($id);
        
        if (!$admissionStudent) {
            return $this->responseTemplate(null, false, __('admission_students.object_not_found'));
        }

        if (isset($request->update_user)) {
            return $this->updateUser($request, $admissionStudent);
        } elseif (isset($request->update_status)) {
            return $this->updateObjStatus($request, $admissionStudent);
        } elseif (isset($request->bus_data)) {
            return $this->updateBusData($request, $admissionStudent);
        } else {
            return $this->updateObj($request, $admissionStudent) ;
        }
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
        ->select(["id", "name", "is_shifted", "admission_id", "semester_id"])
        ->with(['admission', 'semester'])
        // ->whereHas('admission', function ($q) {
        //     $q->where('status', 'phase_3');
        // })
        ->where('status', 'accepted')
        ->whereHas('semester', function ($q) {
            $q->where('is_active', 1);
        });

        if(isset($search))
        $query->where(function ($qu) use ($search) {
            $qu->whereHas('admission', function ($q) use ($search) {
                $q->where('reference_number', 'LIKE', "%$search%");
                $q->orWhere('responsible_1_name', 'LIKE',"%$search%");
                $q->orWhere('responsible_1_phone_num', 'LIKE',"%$search%");
                $q->orWhere('responsible_1_email', 'LIKE',"%$search%");
            })->orWhere(function ($q) use ($search) {
                $q->where('name', 'LIKE', "%$search%");
            });
        });

        if (isset($request->grade_id))
        $query->where('grade_id', $request->grade_id);

        if (isset($request->level_id))
        $query->where('level_id', $request->level_id);
    
        if (isset($request->class_id))
        $query->where('class_id', $request->class_id);
    
        if (isset($request->grades))
        $query->where('grade_id', is_array($request->grades) ? $request->grades : explode(',', $request->grades));

        if (isset($request->levels))
        $query->where('level_id', is_array($request->levels) ? $request->levels : explode(',', $request->levels));
    
        if (isset($request->classes))
        $query->where('class_id', is_array($request->classes) ? $request->classes : explode(',', $request->classes));

        $data = $query->get();
    
        return response()->json($data);
    }

    public function shift (Request $request) {
        $validator = Validator::make($request->all(), [
            'semester_id'       => 'required|exists:semesters,id',
            'grade_id'          => 'required|exists:school_grades,id',
            'level_id'          => 'required|exists:school_grades,id',
            'students_list'     => 'required|array|exists:admission_students,id',
        ]);

        if ($validator->fails()) {
            return $this->responseTemplate(null, false, $validator->errors());
        }

        try {
            DB::beginTransaction();
            
            $data = [
                'semester_id' => $request->semester_id,
                'grade_id'    => $request->grade_id,
                'level_id'    => $request->level_id,
                'is_shifted'  => 1,
                'class_id'    => null
            ];

            $this->targetModel
                ->whereIn('id', is_array($request->students_list) ? $request->students_list : explode(',', $request->students_list))
                ->update($data);

            DB::commit();
        } catch(Exception $exception) {
            DB::rollback();

            Log::error('AdmissionStudentController@shift Exception', ['error' => $exception->getMessage()]);

            return $this->responseTemplate(null, false, [__('admission_students.object_error')]);
        }

        return $this->responseTemplate(null, true, __('admission_students.object_updated'));
    }

    //  HELPER METHODS

    /**
     * Convert a name (possibly Arabic) to a safe email local part (Latin, lowercase, no spaces).
     * If the name contains Arabic letters, transliterate to Latin equivalents.
     */
    private function nameToEmailLocalPart(string $name): string
    {
        $name = trim($name);
        if ($name === '') {
            return 'student';
        }
        // Check if string contains Arabic (Unicode range \u0600-\u06FF, and Arabic Supplement)
        if (preg_match('/[\x{0600}-\x{06FF}\x{0750}-\x{077F}\x{08A0}-\x{08FF}]/u', $name)) {
            $name = $this->transliterateArabicToLatin($name);
        }
        $name = mb_strtolower($name, 'UTF-8');
        // Allow only a-z, 0-9; replace spaces and other chars with nothing or single dot
        $local = preg_replace('/[^a-z0-9]+/', '', $name);
        return $local !== '' ? $local : 'student';
    }

    /**
     * Transliterate Arabic script to Latin (approximate phonetic).
     */
    private function transliterateArabicToLatin(string $text): string
    {
        $map = [
            'ء' => 'e', 'آ' => 'a', 'أ' => 'a', 'ؤ' => 'o', 'إ' => 'i', 'ئ' => 'e', 'ا' => 'a', 'ب' => 'b',
            'ة' => 'h', 'ت' => 't', 'ث' => 'th', 'ج' => 'j', 'ح' => 'h', 'خ' => 'kh', 'د' => 'd', 'ذ' => 'dh',
            'ر' => 'r', 'ز' => 'z', 'س' => 's', 'ش' => 'sh', 'ص' => 's', 'ض' => 'd', 'ط' => 't', 'ظ' => 'z',
            'ع' => 'a', 'غ' => 'gh', 'ف' => 'f', 'ق' => 'q', 'ك' => 'k', 'ل' => 'l', 'م' => 'm', 'ن' => 'n',
            'ه' => 'h', 'و' => 'w', 'ى' => 'a', 'ي' => 'y',
            '٠' => '0', '١' => '1', '٢' => '2', '٣' => '3', '٤' => '4', '٥' => '5', '٦' => '6', '٧' => '7', '٨' => '8', '٩' => '9',
        ];
        $result = '';
        $len = mb_strlen($text, 'UTF-8');
        for ($i = 0; $i < $len; $i++) {
            $char = mb_substr($text, $i, 1, 'UTF-8');
            $result .= $map[$char] ?? $char;
        }
        return $result;
    }

    private function getValidationRules(): array {
        return [
            'first_name'                     => 'required|max:99', 
            'second_name'                    => 'required|max:99', 
            'third_name'                     => 'required|max:99', 
            'birth_date'                     => 'required|date|before:-4 years|after:-21 years',
            'gender'                         => 'required|in:male,female',

            'admission_id'                   => 'required|exists:admissions,id',
            'grade_id'                       => 'required|exists:school_grades,id',
            'level_id'                       => 'required|exists:school_grades,id',
            'bus'                            => 'required|in:no_bus,two_direction,pickup_trip,drop_trip',
            'semester_id'                    => 'required|exists:semesters,id',
        ];
    }

    private function updateUser (Request $request, AdmissionStudent $admissionStudent) {
        $user = $this->userModel->find($admissionStudent->user_id);

        if (!isset($user)) {
            return $this->responseTemplate(null, false, __('admission_students.object_not_found'));
        }

        $validator = Validator::make($request->all(), [
            'email'     => 'required|unique:users,email,' . $admissionStudent->user_id,
            'password'  => 'nullable|min:6|max:20'
        ]);
        
        if ($validator->fails()) {
            return $this->responseTemplate(null, false, $validator->errors());
        }

        $data = [
            'email'     => $request->email,
            'password'  => isset($request->password)
                ? bcrypt($request->password)
                : $user->password
        ];
        
        try {
            DB::beginTransaction();
            
            $user->update($data);
            $admissionStudent->load(['user']);
        
            DB::commit();
        } catch(Exception $exception) {
            DB::rollback();

            Log::error('AdmissionStudentController@updateUser Exception', ['error' => $exception->getMessage()]);
            
            return $this->responseTemplate(null, false, [__('admission_students.object_error')]);
        }

        return $this->responseTemplate($admissionStudent, true, [__('admission_students.object_updated')]);
    }

    private function updateObj (Request $request, AdmissionStudent $admissionStudent) {
        $validator = Validator::make($request->all(), $this->getValidationRules());

        if ($validator->fails()) {
            return $this->responseTemplate(null, false, $validator->errors());
        }
        
        $data           = $request->only($this->targetModel->getFillable());
        $data['name']   = trim("{$data['first_name']} {$data['second_name']} {$data['third_name']}");
        
        try {
            DB::beginTransaction();
            
            $admissionStudent->update($data);
            $admissionStudent->load(['grade', 'level']);

            DB::commit();
        } catch(Exception $exception) {
            DB::rollback();

            Log::error('AdmissionStudentController@updateObj Exception', ['error' => $exception->getMessage()]);
            
            return $this->responseTemplate(null, false, [__('admission_students.object_error')]);
        }

        return $this->responseTemplate($admissionStudent, true, [__('admission_students.object_updated')]);
    }
    
    private function updateObjStatus (Request $request, AdmissionStudent $admissionStudent) {
        $validator = Validator::make($request->all(), [
            'status' => 'required|in:wating,accepted,rejected', 
        ]);

        if ($validator->fails()) {
            return $this->responseTemplate(null, false, [__('admission_students.object_error')]);
        }

        try {
            DB::beginTransaction();
            
            $admissionStudent->status = $request->status;
            $admissionStudent->save();

            // If student accepted, create an account for the student on the system
            if ($request->status == 'accepted' && !isset($admissionStudent->user_id)) {
                $nameForEmail = $admissionStudent->first_name ?? $admissionStudent->name ?? 'student';
                $localPart = $this->nameToEmailLocalPart($nameForEmail);
                $userData = [
                    'name'      => $admissionStudent->name,
                    'category'  => 'student',
                    'email'     => $localPart . $admissionStudent->id . '@goo.com',
                    'password'  => bcrypt('12345678'),
                    'is_active' => true
                ];
           
                $user = $this->userModel->create($userData);

                $admissionStudent->user_id = $user->id;
                $admissionStudent->save();
            }

            DB::commit();
        } catch(Exception $exception) {
            DB::rollback();

            Log::error('AdmissionStudentController@updateObjStatus Exception', ['error' => $exception->getMessage()]);

            return $this->responseTemplate(null, false, [__('admission_students.object_error')]);
        }

        return $this->responseTemplate($admissionStudent, true, __('admission_students.object_updated'));
    }

    private function updateBusData (Request $request, AdmissionStudent $admissionStudent) {
        $validator = Validator::make($request->all(), [
            'bus_address' => 'required|max:200',
            'bus_lat'     => 'required|numeric',
            'bus_lng'     => 'required|numeric',
        ]);

        if ($validator->fails()) {
            return $this->responseTemplate(null, false, $validator->errors());
        }
        
        $data = $request->only(['bus_address', 'bus_lat', 'bus_lng']);
        
        try {
            DB::beginTransaction();
            
            $admissionStudent->update($data);

            DB::commit();
        } catch(Exception $exception) {
            DB::rollback();
            Log::error('AdmissionStudentController@updateBusData Exception', ['error' => $exception->getMessage()]);
            return $this->responseTemplate(null, false, [__('admission_students.object_error')]);
        }
        return $this->responseTemplate($admissionStudent, true, [__('admission_students.object_updated')]);
    }

    private function bulkDelete (Request $request, $id) {
        $this->targetModel
        ->whereIn('id', is_array($request->selected_ids) ? $request->selected_ids : explode(',', $request->selected_ids))
        ->delete();
        
        return $this->responseTemplate(null, true, __('admission_students.object_deleted'));
    }

    private function delete ($id) {
        $admissionStudent = $this->targetModel->find($id);

        if (!$admissionStudent)
        return $this->responseTemplate(null, false, __('admission_students.object_not_found'));
        
        $admissionStudent->delete();

        return $this->responseTemplate($admissionStudent, true, __('admission_students.object_deleted'));
    }

}
