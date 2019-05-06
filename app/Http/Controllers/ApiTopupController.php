<?php

namespace App\Http\Controllers;
use App\Saldo;

use Illuminate\Http\Request;

class ApiTopupController extends Controller
{
	public function topupSaldo(Request $request)
	{
		$requestData = $request->all();
		$requestData['created_at'] = Date('Y-m-d H:i:s');
		$requestData['updated_at'] = Date('Y-m-d H:i:s');
		$requestData['jumlah_transfer'] = $requestData['saldo'];
		return Saldo::create($requestData);
	}
}