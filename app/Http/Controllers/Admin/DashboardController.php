<?php

namespace App\Http\Controllers\Admin;

use LaravelLocalization;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use App\Models\User;
use App\Models\Semester;
use App\Models\Employee;
use App\Models\SchoolGrade;
use App\Models\StudentPayment;
use App\Models\AdmissionStudent;

// use App\Models\District;
// use App\Models\Participant;

use App\Http\Traits\ResponseTemplate;

class DashboardController extends Controller
{
    use ResponseTemplate;

    public function index (Request $request) {
        // get main data in static and ajax
        
        if ($request->get_counts) {
            $is_ar = LaravelLocalization::getCurrentLocale() == 'ar'; 

            $semester  = Semester::where('is_active', 1)->first();
            $employees = Employee::whereNot('category', 'teacher')->whereHas('user', fn ($q) => $q->where('is_active', 1))->count();
            $teachers  = Employee::where('category', 'teacher')->whereHas('user', fn ($q) => $q->where('is_active', 1))->count();
            
            if (isset($semester)) {
                $male_students   = AdmissionStudent::where('gender', 'male')->where('semester_id', $semester->id)->where('status', 'accepted')->count();
                $female_students = AdmissionStudent::where('gender', 'female')->where('semester_id', $semester->id)->where('status', 'accepted')->count();
                
                $student_counts = AdmissionStudent::with('level')
                ->selectRaw('level_id, COUNT(*) as total_students')
                ->where('semester_id', $semester->id)
                ->where('status', 'accepted')
                ->groupBy('level_id')
                ->get()
                ->map(function ($student) use ($is_ar) {
                    return [
                        'level_name'     => $is_ar ? $student->level->ar_title : $student->level->en_title,
                        'total_students' => $student->total_students,
                    ];
                });
            } else {
                $male_students   = 0; 
                $female_students = 0;
                $student_counts  = [];
            }

            $total_expected = StudentPayment::where('status', '!=', 'canceled')->where('is_installment', 0)->adminFilter()->sum('amount');
            $total_real     = StudentPayment::where('status', 'paied')->where('is_installment', 0)->adminFilter()->sum('amount');
            $total_cash     = StudentPayment::where('status', 'paied')->where('is_installment', 0)->where('payment_method', 'cash')->adminFilter()->sum('amount');
            $total_online   = StudentPayment::where('status', 'paied')->where('is_installment', 0)->where('payment_method', '!=', 'cash')->adminFilter()->sum('amount');

            $incomes = StudentPayment::with('semester')
            ->selectRaw('semester_id, SUM(amount) as total_income')
            ->groupBy('semester_id')
            ->get()
            ->map(function ($payment) {
                return [
                    'academic_year' => $payment->semester->title,
                    'total_income' => $payment->total_income,
                ];
            });

            $employee_counts = Employee::select('category')
            ->selectRaw('COUNT(*) as total')
            ->groupBy('category')
            ->get();

            $teacher_counts = SchoolGrade::has('teachers')
            ->withCount('teachers')
            ->get()
            ->map(function ($level) use ($is_ar) {
                return [
                    'level_name'    => $is_ar ? $level->ar_title : $level->en_title,
                    'teacher_count' => $level->teachers_count,
                ];
            });

            $data = [
                'employees'         => $employees, 
                'teachers'          => $teachers,
                'male_students'     => $male_students,
                'female_students'   => $female_students,
                
                'expected'          => $total_expected,
                'real'              => $total_real, 
                'cash'              => $total_cash, 
                'online'            => $total_online,

                'incomes'           => $incomes,
                'student_counts'    => $student_counts,

                'employee_counts'   => $employee_counts,
                'teacher_counts'    => $teacher_counts
            ];

            return $this->responseTemplate($data, true, null);
        }

        if ($request->get_participants) {
            
            $participants = Participant::query()
            ->with(['gove'])
            ->select(['id', 'name', 'gove_id', 'gove_id', 'participant_type_id', 'implementation_type_id', 'umbrella_initiative_id', 'dawwie_activitie_id', 'created_at'])
            ->adminFilter()
            ->get();
            
            if (isset($request->gove_id)) $gove = District::find($request->gove);
            
            return $this->responseTemplate(['participants' => $participants, 'gove' => isset($gove) ? $gove : null], true, null);
        }

        return view('admin.dashboard.index');
    }

}
