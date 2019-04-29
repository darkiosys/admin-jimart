<?php

namespace App\Http\Controllers;

use App\Http\Requests;
use App\Http\Controllers\Controller;

use App\Saldo;
use Illuminate\Http\Request;

class SaldoController extends Controller
{
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
            $saldo = Saldo::where('user_id', 'LIKE', "%$keyword%")
                ->orWhere('admin_id', 'LIKE', "%$keyword%")
                ->orWhere('saldo', 'LIKE', "%$keyword%")
                ->orWhere('jumlah_transfer', 'LIKE', "%$keyword%")
                ->orWhere('no_rek', 'LIKE', "%$keyword%")
                ->orWhere('status', 'LIKE', "%$keyword%")
                ->latest()->paginate($perPage);
        } else {
            $saldo = Saldo::latest()->paginate($perPage);
        }

        return view('saldo.index', compact('saldo'));
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
}
