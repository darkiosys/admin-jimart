<?php

namespace App\Http\Controllers;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;

use App\Saldo;
use App\User;
use Illuminate\Http\Request;

class SaldoController extends Controller
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
    public function index(Request $request)
    {
        $keyword = $request->get('search');
        $perPage = 25;

        if (!empty($keyword)) {
			if(auth()->user()->role == 0) {
				$saldo = DB::table('saldos')
				->select(
					'saldos.id',
					'members.first_name',
					'members.last_name',
					'saldos.user_id',
					'saldos.admin_id',
					'saldos.saldo',
					'saldos.saldo',
					'saldos.no_rek',
					'saldos.jumlah_transfer',
					'saldos.status',
					'saldos.created_at',
					'saldos.updated_at')
				->join('members', 'saldos.user_id', '=', 'members.id')
				->where('saldos.user_id', '=', auth()->user()->id)
				->orWhere('saldos.admin_id', 'LIKE', "%$keyword%")
                ->orWhere('saldos.saldo', 'LIKE', "%$keyword%")
                ->orWhere('saldos.jumlah_transfer', 'LIKE', "%$keyword%")
                ->orWhere('saldos.no_rek', 'LIKE', "%$keyword%")
                ->orWhere('saldos.status', 'LIKE', "%$keyword%")
				->orderBy('saldos.created_at', 'desc')->paginate($perPage);
			} else {
				$saldo = DB::table('saldos')
				->select(
					'saldos.id',
					'members.first_name',
					'members.last_name',
					'saldos.user_id',
					'saldos.admin_id',
					'saldos.saldo',
					'saldos.saldo',
					'saldos.no_rek',
					'saldos.jumlah_transfer',
					'saldos.status',
					'saldos.created_at',
					'saldos.updated_at')
				->join('members', 'saldos.user_id', '=', 'members.id')
				->orWhere('saldos.admin_id', 'LIKE', "%$keyword%")
                ->orWhere('saldos.saldo', 'LIKE', "%$keyword%")
                ->orWhere('saldos.jumlah_transfer', 'LIKE', "%$keyword%")
                ->orWhere('saldos.no_rek', 'LIKE', "%$keyword%")
                ->orWhere('saldos.status', 'LIKE', "%$keyword%")
				->orderBy('saldos.created_at', 'desc')->paginate($perPage);
			}
        } else {
			if(auth()->user()->role == 0) {
				$saldo = DB::table('saldos')
				->select(
					'saldos.id',
					'members.first_name',
					'members.last_name',
					'saldos.user_id',
					'saldos.admin_id',
					'saldos.saldo',
					'saldos.saldo',
					'saldos.no_rek',
					'saldos.jumlah_transfer',
					'saldos.status',
					'saldos.created_at',
					'saldos.updated_at')
				->where('saldos.user_id', '=', auth()->user()->id)
				->join('members', 'saldos.user_id', '=', 'members.id')->orderBy('saldos.created_at', 'desc')->paginate($perPage);
			} else {
				$saldo = DB::table('saldos')
				->select(
					'saldos.id',
					'members.first_name',
					'members.last_name',
					'saldos.user_id',
					'saldos.admin_id',
					'saldos.saldo',
					'saldos.saldo',
					'saldos.no_rek',
					'saldos.jumlah_transfer',
					'saldos.status',
					'saldos.created_at',
					'saldos.updated_at')
				->join('members', 'saldos.user_id', '=', 'members.id')->orderBy('saldos.created_at', 'desc')->paginate($perPage);
			}
        }

        return view('saldo.index', compact('saldo'));
    }

    function ppob(Request $request) {
        $keyword = $request->get('search');
        $perPage = 25;
        $ppob = DB::table('t_ppob')
            ->select('*')->orderBy('trx_date', 'desc')->paginate($perPage);
        return view('saldo.ppob', compact('ppob'));
    }
	
	function verifikasiTopup($id)
	{
		$arr = array("status" => 1);
		$saldo = Saldo::findOrFail($id);
		$member = User::findOrFail($saldo->user_id);
		$arrUser = array("saldo" => $member->saldo + $saldo->jumlah_transfer);
		$member->update($arrUser);
        $saldo->update($arr);
		return redirect('saldo')->with('flash_message', 'Request saldo terverifikasi!');
	}

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        return view('saldo.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    public function store(Request $request)
    {
        
        $requestData = $request->all();
        
        Saldo::create($requestData);

        return redirect('saldo')->with('flash_message', 'Saldo added!');
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     *
     * @return \Illuminate\View\View
     */
    public function show($id)
    {
        $saldo = Saldo::findOrFail($id);

        return view('saldo.show', compact('saldo'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     *
     * @return \Illuminate\View\View
     */
    public function edit($id)
    {
        $saldo = Saldo::findOrFail($id);

        return view('saldo.edit', compact('saldo'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param  int  $id
     *
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    public function update(Request $request, $id)
    {
        
        $requestData = $request->all();
        
        $saldo = Saldo::findOrFail($id);
        $saldo->update($requestData);

        return redirect('saldo')->with('flash_message', 'Saldo updated!');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     *
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    public function destroy($id)
    {
        Saldo::destroy($id);

        return redirect('saldo')->with('flash_message', 'Saldo deleted!');
    }
    
    public function memberSaldo() {
        $members = DB::table('members')
        ->orderBy('saldo', 'desc')
        ->get();
        return view('saldo.membersaldo', compact('members'));
    }

    public function hapusMember($id) {
        User::destroy($id);
        return redirect('/member-saldo');
    }

    public function kosongSaldo($id) {
        $saldo = User::findOrFail($id);
        $saldo->update(array('saldo' => 0));
        return redirect('/member-saldo');
    }
}
