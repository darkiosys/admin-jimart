<?php

namespace App\Http\Controllers;
use App\Saldo;

use Illuminate\Http\Request;

class ApiTopupController extends Controller
{
	public function topupSaldo(Request $request)
	{
		$requestData = $request->all();
		$requestData['created_at'] = date();
		$requestData['updated_at'] = date();
		$requestData['jumlah_transfer'] = $requestData['saldo'];
		return Saldo::create($requestData);
	}
}