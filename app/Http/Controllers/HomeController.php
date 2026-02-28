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
        $user = auth()->user();
        $category = $user->category;

        if (in_array($category, ['admin', 'technical'])) {
            return $category === 'admin' || $user->isAbleTo('dashboard')
                ? redirect()->route('admin.dashboard.index')
                : redirect()->route('admin.profile.index');
        }

        if ($category === 'client') {
            return redirect()->route('client.dashboard');
        }

        if ($category === 'station_manager') {
            return redirect()->route('station.dashboard');
        }

        if ($category === 'worker') {
            return redirect()->route('worker.dashboard');
        }

        if ($category === 'driver') {
            return redirect()->route('driver.dashboard');
        }

        return redirect()->route('login');
    }
}
