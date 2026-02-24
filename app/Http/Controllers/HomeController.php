<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use Yajra\Datatables\Datatables;

use App\Models\User;


class HomeController extends Controller
{
    private $target_model;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
        $this->target_model = new User;
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index(Request $request)
    {
        if (in_array(auth()->user()->category, ['admin', 'technical'])) {
            return auth()->user()->category == 'admin' || auth()->user()->isAbleTo('dashboard') ?
                redirect()->route('admin.dashboard.index') :
                redirect()->route('admin.profile.index');
        }

        if (in_array(auth()->user()->category, ['student'])) {
            return redirect()->route('student.profile.index');
        }
        
        if (auth()->user()->category == 'employee') {
            $employee = auth()->user()->employee;

            if ($employee->category == 'teacher')
            return redirect()->route('teacher.profile.index');
        }
    }
}
