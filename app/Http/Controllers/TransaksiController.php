<?php

namespace App\Http\Controllers;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;

use App\Transaksi;
use App\User;
use Illuminate\Http\Request;

class TransaksiController extends Controller
{
	public function __construct()
    {
        $this->middleware('auth');
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\View\View
     */
    public function getTransaksi(Request $request)
    {
        $transaksis = DB::table('transaksi')->get();
        $transaksi = [];
        for(i=0; i<count($transaksis); i++){
            
        }
        return view('transaksi.index', compact('transaksi'));
    }
}
