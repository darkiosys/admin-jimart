<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\Hash;
use App\User;
use App\OldUser;
use App\TdEcommerce;
use App\TEcommerce;
use App\TransaksiDetail;

use Illuminate\Http\Request;

class ApiUserController extends Controller
{
	public function login(Request $request)
	{
		$requestData = $request->all();
		$failedLogin = array(
			"api_status" => 0,
			"api_message" => "Invalid credentials. Check your username and password."
		);
		$user = User::where('email', '=', $requestData['email'])->orWhere('username', '=', $requestData['email'])->first();
		if($user){
			$res = Hash::check($requestData['password'], $user->password);
			if($res){
				$arr = array(
					"api_status" => 1,
					"api_message" => "success",
					"api_response_fields" => array(
						"id",
						"nik",
						"first_name",
						"last_name",
						"sponsor",
						"username",
						"password",
						"email",
						"phone",
						"tgl_lahir",
						"jenis_kelamin",
						"photo",
						"saldo",
						"points",
						"store_name",
						"store_saldo",
						"store_image",
						"store_note",
						"store_opened",
						"store_address",
						"store_kode_pos",
						"store_status",
						"subdistrict_id",
						"ip_address",
						"last_login",
						"status"
					),
					"id" => $user->id,
					"nik" => $user->nik,
					"first_name" => $user->first_name,
					"last_name" => $user->last_name,
					"sponsor" => $user->sponsor,
					"username" => $user->username,
					"password" => $user->password,
					"email" => $user->email,
					"phone" => $user->phone,
					"tgl_lahir" => $user->tgl_lahir,
					"jenis_kelamin" => $user->jenis_kelamin,
					"photo" => $user->photo,
					"saldo" => $user->saldo,
					"points" => $user->points,
					"store_name" => $user->store_name,
					"store_saldo" => $user->store_saldo,
					"store_image" => $user->store_image,
					"store_note" => $user->store_note,
					"store_opened" => $user->store_opened,
					"store_address" => $user->store_address,
					"store_kode_pos" => $user->store_kode_pos,
					"store_status" => $user->store_status,
					"subdistrict_id" => $user->subdistrict_id,
					"ip_address" => $user->ip_address,
					"last_login" => $user->last_login,
					"status" => $user->status
				);
				return $arr;
			} else {
				return $failedLogin;
			}
		} else {
			$user = OldUser::where('username', '=', $requestData['email'])->where('pass', '=', md5($requestData['password']))->first();
			if($user) {
				$userPayload = array();
				if($user->nama){
					$userPayload['first_name'] = $user->nama;
					$userPayload['last_name'] = $user->nama;
				}
				if($user->sponsor) {
					$userPayload['sponsor'] = $user->sponsor;
				}
				if($user->username) {
					$userPayload['username'] = $user->username;
				}
				if($requestData['password']) {
					$userPayload['password'] = Hash::make($requestData['password']);
				}
				if($user->email) {
					$userPayload['email'] = $user->email;
				}
				if($user->phone) {
					$userPayload['phone'] = $user->phone;
				}
				if($user->tgl_lahir) {
					$userPayload['tgl_lahir'] = $user->tgl_lahir;
				}
				if($user->jenis_kelamin) {
					$userPayload['jenis_kelamin'] = $user->jenis_kelamin;
				}
				if($user->foto) {
					$userPayload['foto'] = $user->foto;
				}
				if($user->saldo) {
					$userPayload['saldo'] = $user->saldo;
				}
				if($user->points) {
					$userPayload['points'] = $user->points;
				}
				$userPayload['subdistrict_id'] = 307;
				if($user->ip_address) {
					$userPayload['ip_address'] = $user->ip_address;
				}
				if($user->last_login) {
					$userPayload['last_login'] = $user->last_login;
				}
				$userPayload['status'] = 0;
				if($user->token) {
					$userPayload['token'] = $user->token;
				}
				if($user->upline) {
					$userPayload['upline'] = $user->upline;
				}
				if($user->posisi) {
					$userPayload['posisi'] = $user->posisi;
				}
				if($user->kota) {
					$userPayload['kota'] = $user->kota;
				}
				if($user->bank) {
					$userPayload['bank'] = $user->bank;
				}
				if($user->norek) {
					$userPayload['norek'] = $user->norek;
				}
				if($user->an) {
					$userPayload['an'] = $user->an;
				}
				if($user->adminrp) {
					$userPayload['adminrp'] = $user->adminrp;
				}
				if($user->tgl) {
					$userPayload['tgl'] = $user->tgl;
				}
				if($user->tglaktif) {
					$userPayload['tglaktif'] = $user->tglaktif;
				}
				if($user->paket) {
					$userPayload['paket'] = $user->paket;
				}
				if($user->blokir) {
					$userPayload['blokir'] = $user->blokir;
				}
				if($user->membership) {
					$userPayload['membership'] = $user->membership;
				}
				if($user->fo) {
					$userPayload['fo'] = $user->fo;
				}
				if($user->stocklist) {
					$userPayload['stocklist'] = $user->stocklist;
				}
				if($user->reward1) {
					$userPayload['reward1'] = $user->reward1;
				}
				if($user->reward2) {
					$userPayload['reward2'] = $user->reward2;
				}
				if($user->reward3) {
					$userPayload['reward3'] = $user->reward3;
				}
				if($user->reward4) {
					$userPayload['reward4'] = $user->reward4;
				}
				if($user->reward5) {
					$userPayload['reward5'] = $user->reward5;
				}
				if($user->reward6) {
					$userPayload['reward6'] = $user->reward6;
				}
				if($user->jabatan) {
					$userPayload['jabatan'] = $user->jabatan;
				}
				$newuser = User::create($userPayload);
				$arr = array(
					"api_status" => 1,
					"api_message" => "success",
					"api_response_fields" => array(
						"id",
						"nik",
						"first_name",
						"last_name",
						"sponsor",
						"username",
						"password",
						"email",
						"phone",
						"tgl_lahir",
						"jenis_kelamin",
						"photo",
						"saldo",
						"points",
						"store_name",
						"store_saldo",
						"store_image",
						"store_note",
						"store_opened",
						"store_address",
						"store_kode_pos",
						"store_status",
						"subdistrict_id",
						"ip_address",
						"last_login",
						"status"
					),
					"id" => $newuser->id,
					"nik" => $newuser->nik,
					"first_name" => $newuser->first_name,
					"last_name" => $newuser->last_name,
					"sponsor" => $newuser->sponsor,
					"username" => $newuser->username,
					"password" => $newuser->password,
					"email" => $newuser->email,
					"phone" => $newuser->phone,
					"tgl_lahir" => $newuser->tgl_lahir,
					"jenis_kelamin" => $newuser->jenis_kelamin,
					"photo" => $newuser->photo,
					"saldo" => $newuser->saldo,
					"points" => $newuser->points,
					"store_name" => $newuser->store_name,
					"store_saldo" => $newuser->store_saldo,
					"store_image" => $newuser->store_image,
					"store_note" => $newuser->store_note,
					"store_opened" => $newuser->store_opened,
					"store_address" => $newuser->store_address,
					"store_kode_pos" => $newuser->store_kode_pos,
					"store_status" => $newuser->store_status,
					"subdistrict_id" => $newuser->subdistrict_id,
					"ip_address" => $newuser->ip_address,
					"last_login" => $newuser->last_login,
					"status" => $newuser->status
				);
				return $arr;
			} else {
				return $failedLogin;
			}
		}
	}
	public function buyProduct(Request $request)
	{
		$req = $request->all();
		$detailPayload = array(
			'store_id' => $req['store_id'],
			'transaksi_id' => $req['transaksi_id'],
			'product_id' => $req['product_id'],
			'harga' => $req['price'],
			'qty' => $req['qty'],
			'weight' => $req['weight'],
			'note' => $req['note']
		);
		TransaksiDetail::create($detailPayload);
		return "success";
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