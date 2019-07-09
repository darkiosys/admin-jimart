<?php

namespace App\Http\Controllers;
use App\Saldo;
use App\User;

use Illuminate\Http\Request;

class ApiTopupController extends Controller
{
	public function topupSaldo(Request $request)
	{
		$requestData = $request->all();
		$requestData['created_at'] = Date('Y-m-d H:i:s');
		$requestData['updated_at'] = Date('Y-m-d H:i:s');
		return Saldo::create($requestData);
	}

	function topupBonus(Request $request)
	{
		$req = $request->all();
		$key = "7c12521b284b156ac567478e2d477e859da7167d";
		if($req['key'] != $key) {
			return array(
				'message' => 'invalid key!'
			);
		}
		$usr = User::where('username', '=', $req['username'])->first();
		if(!$usr) {
			return array(
				'message' => "user not found!"
			);
		}
		$arrUser = array("saldo" => $usr->saldo + (int)$req['bonus']);
		$usr->update($arrUser);
		return array(
			'message' => "add bonus success"
		);
	}
}