<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\Hash;
use App\Transaksi;

use Illuminate\Http\Request;

class ApiTransaksiController extends Controller
{
	public function createTransaksi(Request $request)
	{
		$req = $request->all();

		$productPayload = array(
			'store_id' => $req['store_id'],
			'member_id' => $req['member_id'],
			'no_rek' => $req['no_rek'],
			'total_transfer' => $req['total_transfer'],
			'bukti_transfer' => $req['bukti_transfer'],
		);

		$id = Transaksi::create($productPayload);
		return $id->id;
	}

	function addCartKurir(Request $request) {
		$req = $request->all();

		$kurirPayload = array(
			"store_id" => $req["store_id"],
			"kurir" => $req["kurir"],
			"waktu" => $req["waktu"],
			"biaya" => $req["biaya"]
		);
		$kurir = TEcommerce::create($kurirPayload);
		return $kurir->id;
	}
}