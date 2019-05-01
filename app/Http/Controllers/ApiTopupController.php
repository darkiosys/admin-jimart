<?php

namespace App\Http\Controllers;
use App\Saldo;

use Illuminate\Http\Request;

class ApiTopupController extends Controller
{
	public function topupSaldo(Request $request)
	{
		$requestData = $request->all();
		return Saldo::create($requestData);
	}
}