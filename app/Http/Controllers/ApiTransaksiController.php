<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\Hash;
use App\Transaksi;
use App\User;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ApiTransaksiController extends Controller
{
	function getTransaksi(Request $request)
	{
		$res = DB::select('
			SELECT td_ecommerce.transaksi_id, products.product_name FROM 
			RIGHT JOIN td_ecommerce
			ON products.id = td_ecommerce.products_id
		');
		// INNER JOIN transaksi
		// ON td_ecommerce.transaksi_id = transaksi.id
		return $res;
	}

	public function createTransaksi(Request $request)
	{
		$req = $request->all();
		$productPayload = array(
			'id' => $req['id'],
			'member_id' => $req['member_id'],
			'no_rek' => $req['no_rek'],
			'jenis_transfer' => $req['jenis_transfer'],
			'total_transfer' => $req['total_transfer'],
		);
		if($req['jenis_transfer'] == "1") {
			$member = User::findOrFail($req['member_id']);
			$pay = $member->saldo - $req['total_transfer'];
			$payload = array(
				'saldo' => $pay
			);
			$member->update($payload);
			$productPayload['status'] = 1;
		}
		$id = Transaksi::create($productPayload);
		return $id->id;
	}

	// User
	function totalKonfirmasiPembayaran(Request $request) {
		return Transaksi::where('status', '=', '0')->where('member_id', '=', $request->get('id'))->count();
	}

	function totalPesananDiProses(Request $request) {
		return Transaksi::where('status', '=', '1')->where('member_id', '=', $request->get('id'))->count();
	}

	function totalPesananDikirim(Request $request) {
		return Transaksi::where('status', '=', '2')->where('member_id', '=', $request->get('id'))->count();
	}

	function totalPesananTerkirim(Request $request) {
		return Transaksi::where('status', '=', '3')->where('member_id', '=', $request->get('id'))->count();
	}

	// Store
	function totalMenungguPembayaran(Request $request) {
		$res = DB::select('
			SELECT transaksi_detail.transaksi_id FROM transaksi
			INNER JOIN transaksi_detail
			ON transaksi.id = transaksi_detail.transaksi_id
			WHERE transaksi.status = 0
			AND transaksi_detail.store_id = '.$request->get('store_id').'
			GROUP BY transaksi_id
		');
		return count($res);
	}

	function totalSudahDibayar(Request $request) {
		$res = DB::select('
			SELECT transaksi_detail.transaksi_id FROM transaksi
			INNER JOIN transaksi_detail
			ON transaksi.id = transaksi_detail.transaksi_id
			WHERE transaksi.status = 1
			AND transaksi_detail.store_id = '.$request->get('store_id').'
			GROUP BY transaksi_id
		');
		return count($res);
	}

	function invoiceSudahDibayar(Request $request) {
		$res = DB::select('
			SELECT
				transaksi.id as transaksi_id,
				transaksi.total_transfer,
				members.first_name,
				members.last_name,
				transaksi.created_at
			FROM transaksi
			INNER JOIN transaksi_detail
			ON transaksi.id = transaksi_detail.transaksi_id
			INNER JOIN members
			ON transaksi.member_id = members.id
			WHERE transaksi.status = 1
			AND transaksi_detail.store_id = '.$request->get('store_id').'
			GROUP BY transaksi_id, total_transfer, first_name, last_name, created_at
		');
		$data = array(
			"data" => $res
		);
		return $data;
	}
	function detailInvoiceSudahDibayar(Request $request) {
		$res = DB::select('
			SELECT
				transaksi.id as transaksi_id,
				products.id as product_id,
				products.product_name,
				transaksi_detail.qty,
				transaksi_detail.note,
				member_addresses.nama_penerima,
				member_addresses.alamat,
				member_addresses.no_penerima,
				products.price_discount as harga,
				transaksi.total_transfer,
				transaksi_kurir.kurir,
				transaksi_kurir.waktu,
				transaksi_kurir.biaya,
				transaksi_kurir.store_id,
				transaksi.created_at
			FROM transaksi
			INNER JOIN transaksi_detail
			ON transaksi.id = transaksi_detail.transaksi_id
			INNER JOIN members
			ON transaksi.member_id = members.id
			INNER JOIN member_addresses
			ON members.id = member_addresses.members_id
			INNER JOIN products
			ON transaksi_detail.product_id = products.id
			INNER JOIN transaksi_kurir
			ON transaksi.id = transaksi_kurir.transaksi_id
			WHERE transaksi.status = 1
			AND transaksi_detail.store_id = '.$request->get("store_id").'
			AND transaksi_kurir.store_id = '.$request->get("store_id").'
			AND transaksi.id = "'.$request->get('id').'"
			GROUP BY
				transaksi_id,
				product_id,
				store_id,
				product_name,
				qty,
				note,
				nama_penerima,
				alamat,
				no_penerima,
				harga,
				total_transfer,
				kurir,
				waktu,
				biaya,
				created_at
		');
		for($i=0; $i<count($res); $i++){
			$qu = DB::select('
			SELECT
				image_url
			FROM product_images
			WHERE products_id = '.$res[$i]->product_id.'
		');
			$res[$i]->image = $qu[0]->image_url;
		}
		$data = array(
			"data" => $res
		);
		return $data;
	}

	function totalPesanansDikirim(Request $request) {
		$res = DB::select('
			SELECT transaksi_detail.transaksi_id FROM transaksi
			INNER JOIN transaksi_detail
			ON transaksi.id = transaksi_detail.transaksi_id
			WHERE transaksi.status = 2
			AND transaksi_detail.store_id = '.$request->get('store_id').'
			GROUP BY transaksi_id
		');
		return count($res);
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