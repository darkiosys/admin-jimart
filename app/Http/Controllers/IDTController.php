<?php

namespace App\Http\Controllers;
use DB;
use App\Saldo;
use App\User;
use App\T_transaction;
use App\T_bonusgenerasi;
use Illuminate\Support\Facades\Hash;

use Illuminate\Http\Request;

class IDTController extends Controller
{
	function Login(Request $request) {
        $req = $request->all();
		$signature  = md5('7-3dbf#n4rT');

		$url = "http://36.79.180.2:62455/edc/devel/sim_mlm?EDC=LOGIN.SP1911199562.4de480f38b17e801fc9e22a0e44af051.EDC.18%23%2A217%23%2A205%23%2A148";
		$ch  = curl_init();
		curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_POST, 0);
		curl_setopt($ch, CURLOPT_POSTFIELDS, null);
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

		$data = curl_exec($ch);
		curl_close($ch);
		return $data;
    }
}