<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        if(auth()->user()->role != 1) {
            Auth::logout();
            return redirect('/login');
        }
        return view('home');
    }
    function banner(Request $request) {
        $banners = DB::table('sliders')->select('*')->paginate(25);
        return view('admin/banner', compact('banners'));
    }
}
