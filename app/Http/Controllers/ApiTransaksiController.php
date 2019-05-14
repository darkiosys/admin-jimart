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

	// User
	function totalKonfirmasiPembayaran(Request $request) {
		return Transaksi::where('status', '=', '0')->where('member_id', '=', $request->get('id'))->count();
	}

	function totalPesananDiProses() {
		return Transaksi::where('status', '=', '1')->where('member_id', '=', $request->get('id'))->count();
	}

	function totalPesananDikirim() {
		return Transaksi::where('status', '=', '2')->where('member_id', '=', $request->get('id'))->count();
	}

	function totalPesananTerkirim() {
		return Transaksi::where('status', '=', '3')->where('member_id', '=', $request->get('id'))->count();
	}

	// Store
	function totalMenungguPembayaran() {
		return Transaksi::where('status', '=', '3')->where('store_id', '=', $request->get('id'))->count();
	}

	function totalSudahDibayar() {
		return Transaksi::where('status', '=', '3')->where('store_id', '=', $request->get('id'))->count();
	}

	function totalPesanansDikirim() {
		return Transaksi::where('status', '=', '3')->where('store_id', '=', $request->get('id'))->count();
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