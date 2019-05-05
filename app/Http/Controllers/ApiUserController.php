<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\Hash;
use App\User;
use App\OldUser;

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
		$user = User::where('email', '=', $requestData['email'])->first();
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
			$user = OldUser::where('email', '=', $requestData['email'])->where('pass', '=', md5($requestData['password']))->first();
			if($user) {
				// $timetgl = strtotime('10/16/2003');
				// $timetglaktif = strtotime('10/16/2003');
				// $newformat = date('Y-m-d',$time);
				$userPayload = array(
					"first_name" => $user->nama,
					"last_name" => $user->nama,
					"sponsor" => $user->sponsor,
					"username" => $user->username,
					"password" => Hash::make($requestData['password']),
					"email" => $user->email,
					"phone" => $user->phone,
					"tgl_lahir" => $user->tgl_lahir,
					"jenis_kelamin" => $user->jenis_kelamin,
					"photo" => $user->foto,
					"saldo" => $user->saldo,
					"points" => $user->points,
					"subdistrict_id" => 307,
					"ip_address" => $user->ip_address,
					"last_login" => $user->last_login,
					"status" => 0,
					"token" => $user->token,
					"upline" => $user->upline,
					"posisi" => $user->posisi,
					"kota" => $user->kota,
					"bank" => $user->bank,
					"norek" => $user->norek,
					"an" => $user->an,
					"adminrp" => $user->adminrp,
					"tgl" => $user->tgl,
					"tglaktif" => $user->tglaktif,
					"paket" => $user->paket,
					"blokir" => $user->blokir,
					"membership" => $user->membership,
					"fo" => $user->fo,
					"stocklist" => $user->stocklist,
					"reward1" => $user->reward1,
					"reward2" => $user->reward2,
					"reward3" => $user->reward3,
					"reward4" => $user->reward4,
					"reward5" => $user->reward5,
					"reward6" => $user->reward6,
					"jabatan" => $user->jabatan
				);
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
}