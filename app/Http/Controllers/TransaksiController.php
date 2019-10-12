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
        $transaksi = DB::table('transaksi')->get();
        return view('transaksi.index', compact('transaksi'));
    }

    public function getTransfer(Request $request){
        $transfers = DB::table('t_transfer_saldo')->orderBy('created_at', 'desc')->paginate(25);
        return view('saldo.historytransfer', compact('transfers'));
    }

    function verifikasiTransaksi(Request $request, $id)
    {
        $requestData = array(
            "status" => 1,
        );
        $transaksi = Transaksi::findOrFail($id);
        $transaksi->update($requestData);

        return redirect('transaksi');
    }
}
