<?php

namespace App\Http\Controllers;
use DB;
use App\Saldo;
use App\User;
use App\T_transaction;
use App\T_bonusgenerasi;
use Illuminate\Support\Facades\Hash;

use Illuminate\Http\Request;

class ApiTopupController extends Controller
{
	function callback(Request $request) {
		$data = file_get_contents('php://input');
		$o = json_decode($data);
		$token = explode("/", $o->data->sn);
		DB::table('mb_callback')->insert(
			[
				"ref_id" => $o->data->ref_id,
				"status" => $o->data->status,
				"code" => $o->data->code,
				"hp" => $o->data->hp,
				"price" => $o->data->price,
				"message" => $o->data->message,
				"sn" => $o->data->sn,
				"balance" => $o->data->balance,
				"tr_id" => $o->data->tr_id,
				"rc" => $o->data->rc
			]
		);
		if($o->data->status == 2) {
			$trx = DB::table('t_ppob')->where('trx_id', '=', $o->data->ref_id)->update(['token'  => $token[0], 'status' => 'Gagal' ]);
			$x = DB::select('SELECT * FROM t_ppob WHERE trx_id = "'.$o->data->ref_id.'"');
			DB::update('UPDATE members SET saldo=saldo+? WHERE id=?', [$x[0]->total_tagihan, $x[0]->members_id]);
			return array("message" => "success");
		} else {
			$trx = DB::table('t_ppob')->where('trx_id', '=', $o->data->ref_id)->update(['token'  => $token[0] ]);
			return array("message" => "failed");
		}
	}
	function PostpaidInquiry(Request $request) {
		$req = $request->all();
		$username   = "089687271843";
		$apiKey = "7285d8726bcde318728";
		// $apiKey = "6845d79e9afc378c";
		$ref_id  = uniqid('');
		$month = "";
		if(isset($req['month'])){
			$month = $req['month'];
		}
		$signature  = md5($username.$apiKey.$ref_id);
		$json = '{
				"commands"    : "inq-pasca",
				"username"    : "089687271843",
				"ref_id"      : "'.$ref_id.'",
				"hp"          : "'.$req['hp'].'",
				"code"  	  : "'.$req['code'].'",
				"sign"        : "'.md5($username.$apiKey.$ref_id).'",
				"month"		  : "'.$month.'"
				}';
		// $url = "https://testpostpaid.mobilepulsa.net/api/v1/bill/check";
		$url = "https://mobilepulsa.net/api/v1/bill/check";
		$ch  = curl_init();
		curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $json);
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		$data = curl_exec($ch);
		curl_close($ch);
		return $data;
	}
	function PostpaidPay(Request $request) {
		$req = $request->all();
		$username   = "089687271843";
		$members_id = $req['member_id'];
		$password = $req['password'];
		$apiKey = "7285d8726bcde318728";
		// $apiKey = "6845d79e9afc378c";
		$tr_id  = $req['tr_id'];
		$ref_id  = $req['ref_id'];
		$signature  = md5($username.$apiKey.$tr_id);
		$json = '{	
				"commands"		: "pay-pasca",
				"username"		: "089687271843",
				"tr_id"			: "'.$tr_id.'",
				"sign"			: "'.$signature.'"
				}';
		// $url = "https://testpostpaid.mobilepulsa.net/api/v1/bill/check";
		$url = "https://mobilepulsa.net/api/v1/bill/check";
		if($members_id == "" || $members_id == null) {
			return '{
				"data": {
					"trx_id": "",
					"saldo": "",
					"rc": "0",
					"desc": "Data members_id kosong, Hubungi Admin!",
					"bit11": "",
					"bit12": "",
					"bit48": "",
					"bit62": ""
				}
			}';
		}
		$member = DB::select('SELECT id, saldo, sponsor, username FROM members WHERE id='.$members_id.' AND password ="'.$password.'"');
		if(empty($member)){
			return '{
				"data": {
					"trx_id": "",
					"saldo": "",
					"rc": "0",
					"desc": "Data member tidak terdaftar, Hubungi Admin!",
					"bit11": "",
					"bit12": "",
					"bit48": "",
					"bit62": ""
				}
			}';
		}
		$ch  = curl_init();
		curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $json);
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		$data = curl_exec($ch);
		curl_close($ch);
		$rd = json_decode($data);
		if($rd->data->response_code != "00"){
			return $data;
		}
		$ha = 3000 * (25/100);
		$vshare = 3000 - $ha;
		$v10 = $vshare * (10/100);
		$v15 = $vshare * (15/100);
		$v5 = $vshare * (5/100);
		// CASHBACK
		$cb = DB::update('UPDATE members SET saldo=saldo+? WHERE id=?', [$v10,$members_id]);
		$pcb = array(
			'reff_id' => $ref_id,
			'username' => $member[0]->username,
			'level' => 0,
			'amount' => $v10
		);
		T_bonusgenerasi::create($pcb);
		// LEVEL 1
		$cm1 = DB::select('SELECT username,sponsor FROM members WHERE username=?', [$member[0]->sponsor]);
		if(count($cm1) > 0) {
			$cb1 = DB::update('UPDATE members SET saldo=saldo+? WHERE username=?', [$v15,$cm1[0]->username]);
			$pbg1 = array(
				'reff_id' => $ref_id,
				'username' => $cm1[0]->username,
				'level' => 1,
				'amount' => $v15
			);
			T_bonusgenerasi::create($pbg1);
			// LEVEL 2
			$cm2 = DB::select('SELECT username,sponsor FROM members WHERE username=?', [$cm1[0]->sponsor]);
			if(count($cm2) > 0) {
				$cb2 = DB::update('UPDATE members SET saldo=saldo+? WHERE username=?', [$v15,$cm2[0]->username]);
				$pbg2 = array(
					'reff_id' => $ref_id,
					'username' => $cm2[0]->username,
					'level' => 2,
					'amount' => $v15
				);
				T_bonusgenerasi::create($pbg2);
				// LEVEL 3
				$cm3 = DB::select('SELECT username,sponsor FROM members WHERE username=?', [$cm2[0]->sponsor]);
				if(count($cm3) > 0) {
					$cb3 = DB::update('UPDATE members SET saldo=saldo+? WHERE username=?', [$v10,$cm3[0]->username]);
					$pbg3 = array(
						'reff_id' => $ref_id,
						'username' => $cm3[0]->username,
						'level' => 3,
						'amount' => $v10
					);
					T_bonusgenerasi::create($pbg3);
					// LEVEL 4
					$cm4 = DB::select('SELECT username,sponsor FROM members WHERE username=?', [$cm3[0]->sponsor]);
					if(count($cm4) > 0) {
						$cb4 = DB::update('UPDATE members SET saldo=saldo+? WHERE username=?', [$v10,$cm4[0]->username]);
						$pbg4 = array(
							'reff_id' => $ref_id,
							'username' => $cm4[0]->username,
							'level' => 4,
							'amount' => $v10
						);
						T_bonusgenerasi::create($pbg4);
						// LEVEL 5
						$cm5 = DB::select('SELECT username,sponsor FROM members WHERE username=?', [$cm4[0]->sponsor]);
						if(count($cm5) > 0) {
							$cb5 = DB::update('UPDATE members SET saldo=saldo+? WHERE username=?', [$v10,$cm5[0]->username]);
							$pbg5 = array(
								'reff_id' => $ref_id,
								'username' => $cm5[0]->username,
								'level' => 5,
								'amount' => $v10
							);
							T_bonusgenerasi::create($pbg5);
							// LEVEL 6
							$cm6 = DB::select('SELECT username,sponsor FROM members WHERE username=?', [$cm5[0]->sponsor]);
							if(count($cm6) > 0) {
								$cb6 = DB::update('UPDATE members SET saldo=saldo+? WHERE username=?', [$v10,$cm6[0]->username]);
								$pbg6 = array(
									'reff_id' => $ref_id,
									'username' => $cm6[0]->username,
									'level' => 6,
									'amount' => $v10
								);
								T_bonusgenerasi::create($pbg6);
								// LEVEL 7
								$cm7 = DB::select('SELECT username,sponsor FROM members WHERE username=?', [$cm6[0]->sponsor]);
								if(count($cm7) > 0) {
									$cb7 = DB::update('UPDATE members SET saldo=saldo+? WHERE username=?', [$v5,$cm7[0]->username]);
									$pbg7 = array(
										'reff_id' => $ref_id,
										'username' => $cm7[0]->username,
										'level' => 7,
										'amount' => $v5
									);
									T_bonusgenerasi::create($pbg7);
									// LEVEL 8
									$cm8 = DB::select('SELECT username,sponsor FROM members WHERE username=?', [$cm7[0]->sponsor]);
									if(count($cm8) > 0) {
										$cb8 = DB::update('UPDATE members SET saldo=saldo+? WHERE username=?', [$v5,$cm8[0]->username]);
										$pbg8 = array(
											'reff_id' => $ref_id,
											'username' => $cm8[0]->username,
											'level' => 8,
											'amount' => $v10
										);
										T_bonusgenerasi::create($pbg8);
										// LEVEL 9
										$cm9 = DB::select('SELECT username,sponsor FROM members WHERE username=?', [$cm8[0]->sponsor]);
										if(count($cm9) > 0) {
											$cb9 = DB::update('UPDATE members SET saldo=saldo+? WHERE username=?', [$v5,$cm9[0]->username]);
											$pbg9 = array(
												'reff_id' => $ref_id,
												'username' => $cm9[0]->username,
												'level' => 9,
												'amount' => $v5
											);
											T_bonusgenerasi::create($pbg9);
											// LEVEL 10
											$cm10 = DB::select('SELECT username,sponsor FROM members WHERE username=?', [$cm9[0]->sponsor]);
											if(count($cm10) > 0) {
												$cb10 = DB::update('UPDATE members SET saldo=saldo+? WHERE username=?', [$v5,$cm10[0]->username]);
												$pbg10 = array(
													'reff_id' => $ref_id,
													'username' => $cm10[0]->username,
													'level' => 10,
													'amount' => $v5
												);
												T_bonusgenerasi::create($pbg10);
											}
										}
									}
								}
							}
						}
					}
				}
			}
		}
		
		$ns = $member[0]->saldo - (int)$req['price'];
		$xusr = User::where('id', '=', $member[0]->id)->first();
		$xusr->update(array('saldo' => $ns));
		$trxname = 'PLN POSTPAID';
		if(isset($req['trxname'])) {
			$trxname = $req['trxname'];
		}
		DB::table('t_ppob')->insert(
			[
				'members_id' => $members_id,
				'trx_id' => $ref_id,
				'trx_date' => Date('Y-m-d H:i:s'),
				'trx_name' => $trxname,
				'no_hp' => $req['hp'],
				'tagihan' => (int)$req['price'], 
				'fee_admin' => 3000,
				'total_tagihan' => (int)$req['price']+3000,
				'ending_saldo' => $ns,
				'product_code' => $trxname,
				'status' => "Berhasil",
				'status_bonus' => '0'
			]
		);
		return $data;
	}
	function PostpaidInquiryDev(Request $request) {
		$req = $request->all();
		$username   = "089687271843";
		// $apiKey = "7285d8726bcde318728";
		$apiKey = "6845d79e9afc378c";
		$ref_id  = uniqid('');
		$month = "";
		if(isset($req['month'])){
			$month = $req['month'];
		}
		$signature  = md5($username.$apiKey.$ref_id);
		$json = '{
				"commands"    : "inq-pasca",
				"username"    : "089687271843",
				"ref_id"      : "'.$ref_id.'",
				"hp"          : "'.$req['hp'].'",
				"code"  	  : "'.$req['code'].'",
				"sign"        : "'.md5($username.$apiKey.$ref_id).'",
				"month"		  : "'.$month.'"
				}';
		$url = "https://testpostpaid.mobilepulsa.net/api/v1/bill/check";
		// $url = "https://mobilepulsa.net/api/v1/bill/check";
		$ch  = curl_init();
		curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $json);
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		$data = curl_exec($ch);
		curl_close($ch);
		return $data;
	}
	function PostpaidPayDev(Request $request) {
		// $req = $request->all();
		// $username   = "089687271843";
		// // $apiKey = "7285d8726bcde318728";
		// $apiKey = "6845d79e9afc378c";
		// $tr_id  = $req['tr_id'];
		// $signature  = md5($username.$apiKey.$tr_id);
		// $json = '{	
		// 		"commands"		: "pay-pasca",
		// 		"username"		: "089687271843",
		// 		"tr_id"			: "'.$tr_id.'",
		// 		"sign"			: "'.$signature.'"
		// 		}';
		// $url = "https://testpostpaid.mobilepulsa.net/api/v1/bill/check";
		// // $url = "https://mobilepulsa.net/api/v1/bill/check";
		// $ch  = curl_init();
		// curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
		// curl_setopt($ch, CURLOPT_URL, $url);
		// curl_setopt($ch, CURLOPT_POSTFIELDS, $json);
		// curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
		// curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		// $data = curl_exec($ch);
		// curl_close($ch);
		// return $data;
		$req = $request->all();
		$username   = "089687271843";
		$members_id = $req['member_id'];
		$password = $req['password'];
		$apiKey = "6845d79e9afc378c";
		// $apiKey = "7285d8726bcde318728"; // prod
		$tr_id  = $req['tr_id'];
		$ref_id  = $req['ref_id'];
		$signature  = md5($username.$apiKey.$tr_id);
		$json = '{	
				"commands"		: "pay-pasca",
				"username"		: "089687271843",
				"tr_id"			: "'.$tr_id.'",
				"sign"			: "'.$signature.'"
				}';
		$url = "https://testpostpaid.mobilepulsa.net/api/v1/bill/check";
		// $url = "https://mobilepulsa.net/api/v1/bill/check"; // prod
		if($members_id == "" || $members_id == null) {
			return '{
				"data": {
					"trx_id": "",
					"saldo": "",
					"rc": "0",
					"desc": "Data members_id kosong, Hubungi Admin!",
					"bit11": "",
					"bit12": "",
					"bit48": "",
					"bit62": ""
				}
			}';
		}
		$member = DB::select('SELECT id, saldo, sponsor, username FROM members WHERE id='.$members_id.' AND password ="'.$password.'"');
		if(empty($member)){
			return '{
				"data": {
					"trx_id": "",
					"saldo": "",
					"rc": "0",
					"desc": "Data member tidak terdaftar, Hubungi Admin!",
					"bit11": "",
					"bit12": "",
					"bit48": "",
					"bit62": ""
				}
			}';
		}
		$ch  = curl_init();
		curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $json);
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		$data = curl_exec($ch);
		curl_close($ch);
		$rd = json_decode($data);
		if($rd->data->response_code != "00"){
			return $data;
		}
		$ha = 3000 * (25/100);
		$vshare = 3000 - $ha;
		$v10 = $vshare * (10/100);
		$v15 = $vshare * (15/100);
		$v5 = $vshare * (5/100);
		// CASHBACK
		$cb = DB::update('UPDATE members SET saldo=saldo+? WHERE id=?', [$v10,$members_id]);
		$pcb = array(
			'reff_id' => $ref_id,
			'username' => $member[0]->username,
			'level' => 0,
			'amount' => $v10
		);
		T_bonusgenerasi::create($pcb);
		// LEVEL 1
		$cm1 = DB::select('SELECT username,sponsor FROM members WHERE username=?', [$member[0]->sponsor]);
		if(count($cm1) > 0) {
			$cb1 = DB::update('UPDATE members SET saldo=saldo+? WHERE username=?', [$v15,$cm1[0]->username]);
			$pbg1 = array(
				'reff_id' => $ref_id,
				'username' => $cm1[0]->username,
				'level' => 1,
				'amount' => $v15
			);
			T_bonusgenerasi::create($pbg1);
			// LEVEL 2
			$cm2 = DB::select('SELECT username,sponsor FROM members WHERE username=?', [$cm1[0]->sponsor]);
			if(count($cm2) > 0) {
				$cb2 = DB::update('UPDATE members SET saldo=saldo+? WHERE username=?', [$v15,$cm2[0]->username]);
				$pbg2 = array(
					'reff_id' => $ref_id,
					'username' => $cm2[0]->username,
					'level' => 2,
					'amount' => $v15
				);
				T_bonusgenerasi::create($pbg2);
				// LEVEL 3
				$cm3 = DB::select('SELECT username,sponsor FROM members WHERE username=?', [$cm2[0]->sponsor]);
				if(count($cm3) > 0) {
					$cb3 = DB::update('UPDATE members SET saldo=saldo+? WHERE username=?', [$v10,$cm3[0]->username]);
					$pbg3 = array(
						'reff_id' => $ref_id,
						'username' => $cm3[0]->username,
						'level' => 3,
						'amount' => $v10
					);
					T_bonusgenerasi::create($pbg3);
					// LEVEL 4
					$cm4 = DB::select('SELECT username,sponsor FROM members WHERE username=?', [$cm3[0]->sponsor]);
					if(count($cm4) > 0) {
						$cb4 = DB::update('UPDATE members SET saldo=saldo+? WHERE username=?', [$v10,$cm4[0]->username]);
						$pbg4 = array(
							'reff_id' => $ref_id,
							'username' => $cm4[0]->username,
							'level' => 4,
							'amount' => $v10
						);
						T_bonusgenerasi::create($pbg4);
						// LEVEL 5
						$cm5 = DB::select('SELECT username,sponsor FROM members WHERE username=?', [$cm4[0]->sponsor]);
						if(count($cm5) > 0) {
							$cb5 = DB::update('UPDATE members SET saldo=saldo+? WHERE username=?', [$v10,$cm5[0]->username]);
							$pbg5 = array(
								'reff_id' => $ref_id,
								'username' => $cm5[0]->username,
								'level' => 5,
								'amount' => $v10
							);
							T_bonusgenerasi::create($pbg5);
							// LEVEL 6
							$cm6 = DB::select('SELECT username,sponsor FROM members WHERE username=?', [$cm5[0]->sponsor]);
							if(count($cm6) > 0) {
								$cb6 = DB::update('UPDATE members SET saldo=saldo+? WHERE username=?', [$v10,$cm6[0]->username]);
								$pbg6 = array(
									'reff_id' => $ref_id,
									'username' => $cm6[0]->username,
									'level' => 6,
									'amount' => $v10
								);
								T_bonusgenerasi::create($pbg6);
								// LEVEL 7
								$cm7 = DB::select('SELECT username,sponsor FROM members WHERE username=?', [$cm6[0]->sponsor]);
								if(count($cm7) > 0) {
									$cb7 = DB::update('UPDATE members SET saldo=saldo+? WHERE username=?', [$v5,$cm7[0]->username]);
									$pbg7 = array(
										'reff_id' => $ref_id,
										'username' => $cm7[0]->username,
										'level' => 7,
										'amount' => $v5
									);
									T_bonusgenerasi::create($pbg7);
									// LEVEL 8
									$cm8 = DB::select('SELECT username,sponsor FROM members WHERE username=?', [$cm7[0]->sponsor]);
									if(count($cm8) > 0) {
										$cb8 = DB::update('UPDATE members SET saldo=saldo+? WHERE username=?', [$v5,$cm8[0]->username]);
										$pbg8 = array(
											'reff_id' => $ref_id,
											'username' => $cm8[0]->username,
											'level' => 8,
											'amount' => $v10
										);
										T_bonusgenerasi::create($pbg8);
										// LEVEL 9
										$cm9 = DB::select('SELECT username,sponsor FROM members WHERE username=?', [$cm8[0]->sponsor]);
										if(count($cm9) > 0) {
											$cb9 = DB::update('UPDATE members SET saldo=saldo+? WHERE username=?', [$v5,$cm9[0]->username]);
											$pbg9 = array(
												'reff_id' => $ref_id,
												'username' => $cm9[0]->username,
												'level' => 9,
												'amount' => $v5
											);
											T_bonusgenerasi::create($pbg9);
											// LEVEL 10
											$cm10 = DB::select('SELECT username,sponsor FROM members WHERE username=?', [$cm9[0]->sponsor]);
											if(count($cm10) > 0) {
												$cb10 = DB::update('UPDATE members SET saldo=saldo+? WHERE username=?', [$v5,$cm10[0]->username]);
												$pbg10 = array(
													'reff_id' => $ref_id,
													'username' => $cm10[0]->username,
													'level' => 10,
													'amount' => $v5
												);
												T_bonusgenerasi::create($pbg10);
											}
										}
									}
								}
							}
						}
					}
				}
			}
		}
		
		$ns = $member[0]->saldo - (int)$req['price'];
		$xusr = User::where('id', '=', $member[0]->id)->first();
		$xusr->update(array('saldo' => $ns));
		$trxname = 'PLN POSTPAID';
		if(isset($req['trxname'])) {
			$trxname = $req['trxname'];
		}
		DB::table('t_ppob')->insert(
			[
				'members_id' => $members_id,
				'trx_id' => $ref_id,
				'trx_date' => Date('Y-m-d H:i:s'),
				'trx_name' => $trxname,
				'no_hp' => $req['hp'],
				'tagihan' => (int)$req['price'], 
				'fee_admin' => 3000,
				'total_tagihan' => (int)$req['price']+3000,
				'ending_saldo' => $ns,
				'product_code' => $trxname,
				'status' => "Berhasil",
				'status_bonus' => '0'
			]
		);
		return $data;
	}
	function topupRelease(Request $request) {
		$req = $request->all();
		$username   = "089687271843";
		// $apiKey   = "7285d8726bcde318728";
		$apiKey = "6845d79e9afc378c";
		$ref_id  = uniqid('');
		$code = $req['code'];
		$signature  = md5($username.$apiKey.$ref_id);
		$json = '{
				"commands"    : "topup",
				"username"    : "089687271843",
				"ref_id"      : "'.$ref_id.'",
				"hp"          : "'.$req['hp'].'",
				"pulsa_code"  : "'.$code.'",
				"sign"        : "'.md5($username.$apiKey.$ref_id).'"
				}';
		// $url = "https://api.mobilepulsa.net/v1/legacy/index";
		$url = "https://testprepaid.mobilepulsa.net/v1/legacy/index";
		$ch  = curl_init();
		curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $json);
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		$data = curl_exec($ch);
		curl_close($ch);
		return $data;
	}
	function index(Request $request) {
		$req = $request->all();
		$members_id = $req['member_id'];
		$password = $req['password'];
		$username   = "089687271843";
		$apiKey   = "6845d79e9afc378c";
		$ref_id  = uniqid('');
		$code = $req['code'];
		$signature  = md5($username.$apiKey.$ref_id);
		$json = '{
				"commands"    : "topup",
				"username"    : "089687271843",
				"ref_id"      : "'.$ref_id.'",
				"hp"          : "'.$req['hp'].'",
				"pulsa_code"  : "'.$code.'",
				"sign"        : "'.md5($username.$apiKey.$ref_id).'"
				}';
		$url = "https://testprepaid.mobilepulsa.net/v1/legacy/index";
		if($members_id == "" || $members_id == null) {
			return '{
				"data": {
					"trx_id": "",
					"saldo": "",
					"rc": "0",
					"desc": "Data members_id kosong, Hubungi Admin!",
					"bit11": "",
					"bit12": "",
					"bit48": "",
					"bit62": ""
				}
			}';
		}
		$member = DB::select('SELECT id, saldo FROM members WHERE id='.$members_id.' AND password ="'.$password.'"');
		if(empty($member)){
			return '{
				"data": {
					"trx_id": "",
					"saldo": "",
					"rc": "0",
					"desc": "Data member tidak terdaftar, Hubungi Admin!",
					"bit11": "",
					"bit12": "",
					"bit48": "",
					"bit62": ""
				}
			}';
		}
		$lamount = array(
			"htelkomsel1000" => array(1000,1900),
			"htelkomsel2000" => array(2000, 3200),
			"htelkomsel3000" => array(3000, 4800), 
			"htelkomsel5000" => array(5000, 5900),
			"htelkomsel10000" => array(10000, 10850),
			"htelkomsel15000" => array(15000, 15500),
			"htelkomsel20000" => array(20000, 20500),
			"htelkomsel25000" => array(25000, 25000),
			"htelkomsel40000" => array(40000, 40000),
			"htelkomsel50000" => array(50000, 49750),
			"htelkomsel100000" => array(100000, 98500),
			"htelkomsel150000" => array(150000, 148750),
			"htelkomsel200000" => array(200000, 198000),
			"htelkomsel300000" => array(300000, 297500),
			"htelkomsel500000" => array(500000, 495000),
			"htelkomsel1000000" => array(1000000, 987500),
			"hindosat5000" => array(5000, 5990),
			"hindosat10000" => array(10000, 10990),
			"hindosat12000" => array(12000, 12500),
			"hindosat20000" => array(20000, 20200),
			"hindosat25000" => array(25000, 24900),
			"hindosat30000" => array(30000, 30550),
			"hindosat50000" => array(50000, 49250),
			"hindosat60000" => array(60000, 58800),
			"hindosat80000" => array(80000, 78000),
			"hindosat100000" => array(100000, 98000),
			"hindosat150000" => array(150000, 143000),
			"hindosat200000" => array(200000, 185500),
			"hindosat250000" => array(250000, 232000),
			"hindosat500000" => array(500000, 463000),
			"hindosat1000000" => array(1000000, 926000),
			"xld5000" => array(5000, 5800),
			"xld10000" => array(10000, 10800),
			"xld15000" => array(15000, 15300),
			"xld25000" => array(25000, 24900),
			"xld30000" => array(30000, 29900),
			"xld50000" => array(50000, 49700),
			"xld100000" => array(100000, 99250),
			"xld150000" => array(150000, 150000),
			"xld200000" => array(200000, 198500),
			"xld300000" => array(300000, 298500),
			"xld500000" => array(500000, 495000),
			"xld1000000" => array(1000000, 990000),
			"haxis5000" => array(5000, 5800),
			"haxis10000" => array(10000, 10800),
			"haxis15000" => array(15000, 14925),
			"haxis25000" => array(25000, 24900),
			"haxis50000" => array(50000, 49700),
			"haxis100000" => array(100000, 99250),
			"haxis200000" => array(200000, 198500),
			"hthree1000"  => array(1000, 1300),
			"hthree2000"  => array(2000, 2250),
			"hthree3000"  => array(3000, 3450),
			"hthree5000"  => array(5000, 5400),
			"hthree10000"  => array(10000, 10400),
			"hthree15000"  => array(15000, 15000),
			"hthree20000"  => array(20000, 19700),
			"hthree25000"  => array(25000, 24625),
			"hthree30000"  => array(30000, 30000),
			"hthree50000"  => array(50000, 49000),
			"hthree100000"  => array(100000, 98500),
			"hthree150000"  => array(150000, 148500),
			"hthree200000"  => array(200000, 199000),
			"hthree300000"  => array(300000, 297000),
			"hthree500000"  => array(500000, 495000),
			"hthree1000000"  => array(1000000, 990000),
			"hsmart5000" => array(5000, 5175),
			"hsmart10000" => array(10000, 10100),
			"hsmart20000" => array(20000, 19800),
			"hsmart25000" => array(25000, 24800),
			"hsmart50000" => array(50000, 49500),
			"hsmart60000" => array(60000, 60000),
			"hsmart100000" => array(100000, 97550),
			"hsmart150000" => array(150000, 147000),
			"hsmart200000" => array(200000, 196000),
			"hsmart300000" => array(300000, 294000),
			"hsmart500000" => array(500000, 490000),
			"hsmart1000000" => array(1000000, 980000),
			"hceria50000" => array(50000, 50000),
			"hceria100000" => array(100000, 100000),
			"hceria200000" => array(200000, 200000),
			"tseldata10000" => array(10700, 10700),
			"tseldata100000" => array(99500, 99500),
			"tseldata1gb" => array(21000, 21000),
			"tseldata20000" => array(20750, 20750),
			"tseldata200000" => array(198500, 198500),
			"tseldata25000" => array(25750, 25750),
			"tseldata2gb" => array(45000, 45000),
			"tseldata3gb" => array(70000, 70000),
			"tseldata5000" => array(5900, 5900),
			"tseldata50000" => array(50750, 50750),
			"tseldata500MB" => array(14800, 14800),
			"tseldata750MB" => array(22500, 22500),
			"tseldataGM25000" => array(24950, 24950),
			"tseldataVM10000" => array(10500, 10500),
			"tseldataVM20000" => array(20900, 20900),
			"tseldataVM50000" => array(50000, 50000),
			"indosatPHD10" => array(232000, 232000),
			"indosatPHD20" => array(349900, 349900),
			"indosatPHD40" => array(549900, 549900),
			"indosatPHI30" => array(189400, 189400),
			"indosatPHK20" => array(449900, 449900),
			"indosatPHK40" => array(649900, 649900),
			"indosatPHV40" => array(199900, 199900),
			"isatdata100mb" => array(3500, 3500),
			"isatdata1gb" => array(13000, 13000),
			"isatdata200mb" => array(5500, 5500),
			"isatdata2gb" => array(24500, 24500),
			"isatdata300mb" => array(6200, 6200),
			"isatdata3gb" => array(36200, 36200),
			"isatdata4gb" => array(48100, 48100),
			"isatdata500mb" => array(10100, 10100),
			"isatdata5gb" => array(59900, 59900),
			"isatdata700mb" => array(12100, 12100),
			"isatdataex2gb" => array(38000, 38000),
			"isatdataex4gb" => array(57000, 57000),
			"isatdataex6gb" => array(76000, 76000),
			"isatdataF1" => array(24000, 24000),
			"isatdataF2" => array(34000, 34000),
			"isatdataF5" => array(98000, 98000),
			"isatdataF6" => array(147000, 147000),
			"isatdataF7" => array(195000, 195000),
			"isatdataFDRAA3D" => array(243000, 243000),
			"isatdataFDRAA5D" => array(292500, 292500),
			"isatdataFDRAA7D" => array(439000, 439000),
			"isatdataFDRAE3D" => array(534000, 534000),
			"isatdataFDRAE5D" => array(877000, 877000),
			"isatdataFDRAE7D" => array(1219000, 1219000),
			"isatdataFIP130" => array(130000, 130000),
			"isatdataFIP40" => array(40000, 40000),
			"isatdataFIP60" => array(60000, 60000),
			"isatdataFIP80" => array(80000, 80000),
			"isatdataFL" => array(89000, 89000),
			"isatdataFM" => array(56050, 56050),
			"isatdataFXL" => array(115000, 115000),
			"isatdataFXXL" => array(148000, 148000),
			"isatdataHAJI10" => array(245000, 245000),
			"isatdataHAJI15" => array(325000, 325000),
			"isatdataHAJI20" => array(350000, 350000),
			"isatdataHAJI30" => array(500000, 500000),
			"isatdataHAJI45" => array(650000, 650000),
			"isatdataHAJICOMBO10" => array(325000, 325000),
			"isatdataHAJICOMBO15" => array(450000, 450000),
			"isatdataHAJICOMBO20" => array(500000, 500000),
			"isatdataHAJICOMBO30" => array(600000, 600000),
			"isatdataHAJICOMBO45" => array(750000, 750000),
			"isatdataHAJITELP10" => array(200000, 200000),
			"isatdataHAJITELP15" => array(245000, 245000),
			"isatdataHAJITELP20" => array(275000, 275000),
			"isatdataHAJITELP30" => array(325000, 325000),
			"isatdataHAJITELP45" => array(375000, 375000),
			"isatdataHII1GB" => array(9500, 9500),
			"isatdataHIU10GB" => array(94090, 94090),
			"isatdataHIU15GB" => array(120280, 120280),
			"isatdataHIU18GB" => array(260000, 260000),
			"isatdataHIU1GB" => array(26000, 26000),
			"isatdataHIU2GB" => array(40000, 40000),
			"isatdataHIU36GB" => array(10100, 10100),
			"isatdataHIU3GB" => array(10100, 10100),
			"isatdataHIU5GB" => array(10100, 10100),
			"isatdataHIU7GB" => array(10100, 10100),
			"isatdataHIUJ" => array(10100, 10100),
			"isatdataMY150" => array(10100, 10100),
			"isatdataMY300" => array(10100, 10100),
			"isatdataSG150" => array(10100, 10100),
			"isatdataSG300" => array(10100, 10100),
			"isatdataY15D" => array(10100, 10100),
			"isatdataY1D" => array(10100, 10100),
			"isatdataY3D" => array(10100, 10100),
			"xldataCUH10" => array(299000, 299000),
			"xldataCUH20" => array(399000, 399000),
			"xldataCUH40" => array(549000, 549000),
			"xldataCVIPA" => array(69000, 69000),
			"xldataCVIPB" => array(99000, 99000),
			"xldataCVIPC" => array(139000, 139000),
			"xldataCVIPD" => array(189000, 189000),
			"xldataCVIPE" => array(249000, 249000),
			"xldataHR100" => array(98000, 98000),
			"xldataHR130" => array(125800, 125800),
			"xldataHR180" => array(173800, 173800),
			"xldataHR220" => array(212200, 212200),
			"xldataHR30" => array(30000, 30000),
			"xldataHR60" => array(58600, 58600),
			"xldataIUH10" => array(199000, 199000),
			"xldataIUH20" => array(249000, 249000),
			"xldataIUH40" => array(379000, 379000),
			"xldataNSUH10" => array(149000, 149000),
			"xldataNSUH20" => array(179000, 179000),
			"xldataNSUH40" => array(199000, 199000),
			"xldataPASS1D" => array(85000, 85000),
			"xldataPASS30D" => array(750000, 750000),
			"xldataPASS3D" => array(150000, 150000),
			"xldataPASS7D" => array(200000, 200000),
			"xldataRC1" => array(160000, 160000),
			"xldataRC3" => array(225000, 225000),
			"xldataRC30" => array(825000, 825000),
			"xldataRC7" => array(425000, 425000),
			"xldataUCL" => array(438000, 438000),
			"xldataUCM" => array(300000, 300000),
			"xldataUCS" => array(194000, 194000),
			"xldataXCA12X" => array(599000, 599000),
			"xldataXCB12X" => array(799000, 799000),
			"xldataXCC12X" => array(999000, 999000),
			"xldataXCD12X" => array(1299000, 1299000),
			"xldataXCE12X" => array(1399000, 1399000),
			"xldataXT129" => array(124840, 124840),
			"xldataXT12912X" => array(1248000, 1248000),
			"xldataXT179" => array(172840, 172840),
			"xldataXT17912X" => array(1628000, 1628000),
			"xldataXT239" => array(230440, 230440),
			"xldataXT23912X" => array(2168000, 2168000),
			"xldataXT59" => array(57640, 57640),
			"xldataXT5912X" => array(588000, 588000),
			"xldataXT89" => array(86440, 86440),
			"xldataXT8912X" => array(868000, 868000),
			"xldataXTB1" => array(35000, 35000),
			"xldataXTB2" => array(30000, 30000),
			"xldataXTK30GB" => array(11900, 11900),
			"axisdata1gb" => array(22000, 22000),
			"axisdata2gb" => array(30000, 30000),
			"axisdata3gb" => array(39000, 39000),
			"axisdata5gb" => array(59000, 59000),
			"axisdataAIGO1" => array(16000, 16000),
			"axisdataAIGO2" => array(29000, 29000),
			"axisdataAIGO3" => array(32800, 32800),
			"axisdataAIGO5" => array(46800, 46800),
			"axisdataAIGOM1" => array(9000, 9000),
			"axisdataAIGOM2" => array(16000, 16000),
			"axisdataAIGOM3" => array(20500, 20500),
			"axisdataAIGOM5" => array(31700, 31700),
			"axisdataKZLCH30" => array(14500, 14500),
			"axisdataKZLCH7" => array(4750, 4750),
			"axisdataKZLCOMBO30" => array(19400, 19400),
			"axisdataKZLCOMBO7" => array(6650, 6650),
			"axisdataKZLGA30" => array(9650, 9650),
			"axisdataKZLGA7" => array(4750, 4750),
			"axisdataKZLGV30" => array(9650, 9650),
			"axisdataKZLGV7" => array(4750, 4750),
			"axisdataKZLSM30" => array(14500, 14500),
			"axisdataKZLSM7" => array(4750, 4750),
			"axisdataOM12" => array(69400, 69400),
			"axisdataOM2" => array(24500, 24500),
			"axisdataOM4" => array(34400, 34400),
			"axisdataOM8" => array(54400, 54400),
			"threedata100GB7" => array(88500, 88500),
			"threedata1250mb" => array(33000, 33000),
			"threedata2250mb" => array(50000, 50000),
			"threedata4G10GB" => array(49500, 49500),
			"threedata4G30GB" => array(99000, 99000),
			"threedata4G32GB" => array(59500, 59500),
			"threedata4GDP375GB14D" => array(24750, 24750),
			"threedata4GL" => array(89100, 89100),
			"threedataAEROPASS10USSD" => array(742500, 742500),
			"threedataAEROPASS5USSD" => array(396000, 396000),
			"threedataIbadah10D" => array(186165, 186165),
			"threedataIbadah40D" => array(320125, 320125),
			"threedataIS275GB" => array(9950, 9950),
			"threedataIS3GB3D" => array(19900, 19900),
			"threedataIS5GB1D" => array(4975, 4975),
			"threedataMP2GB20M" => array(34650, 34650),
			"threedataNS4G" => array(59500, 59500),
			"threedataNS8G" => array(125000, 125000),
			"threedataSEAPASS1USSD" => array(44550, 44550),
			"threedataSEAPASS3USSD" => array(118800, 118800),
			"threedataSEAPASS7USSD" => array(217800, 217800),
			"threedataTAP1" => array(99000, 99000),
			"threedataTAP3" => array(173250, 173250),
			"threedataTAP30" => array(396000, 396000),
			"threedataTAP7" => array(277200, 277200),
			"smartdataVOL10" => array(9950, 9950),
			"smartdataVOL100" => array(98000, 98000),
			"smartdataVOL150" => array(147000, 147000),
			"smartdataVOL20" => array(19800, 19800),
			"smartdataVOL200" => array(196000, 196000),
			"smartdataVOL30" => array(29700, 29700),
			"smartdataVOL60" => array(59100, 59100)
		);
		$actualprice = $lamount[$req['code']][1] + 1000;
		if($member[0]->saldo < $actualprice) {
			$tp = array(
				'member_id' => $members_id,
				'log_id' => '0',
				'target' => $req['code'],
				'reff_id' => $ref_id,
				'prodname' => $req['hp'],
				'amount' => $actualprice,
				'status' => 'FAILED',
				'message' => 'Insufficient balance',
				'time' => date('Y-m-d H:i:s'),
				'payload' => json_encode($json)
			);
			T_transaction::create($tp);
			return '{
				"data": {
					"trx_id": "",
					"saldo": "",
					"rc": "0",
					"desc": "Saldo Tidak Cukup!",
					"bit11": "",
					"bit12": "",
					"bit48": "",
					"bit62": ""
				}
			}';
		}
		$tp = array(
			'member_id' => $members_id,
			'log_id' => '0',
			'target' => $req['hp'],
			'reff_id' => $ref_id,
			'prodname' => $code,
			'amount' => $actualprice,
			'status' => 'SUCCESS',
			'message' => 'Inquiry Pulsa Berhasil',
			'time' => date('Y-m-d H:i:s'),
			'payload' => json_encode($json)
		);
		T_transaction::create($tp);
		$data = '{"data":{
			"ref_id":"'.$ref_id.'",
			"status":1,
			"code":"'.$code.'",
			"hp":"'.$req['hp'].'",
			"pulsa": "'.$lamount[$req['code']][0].'",
			"price": '.$actualprice.',
			"message":"INQUIRY",
			"balance":0,
			"tr_id":"'.$ref_id.'",
			"rc":"0001"
		}}';
		return $data;
	}
	function pay(Request $request) {
		$req = $request->all();
		$members_id = $req['member_id'];
		$lr = DB::table('t_ppob')->where('members_id', '=', $members_id)->orderBy('trx_date', 'desc')->first();
		if($lr->trx_date == Date('Y-m-d H:i:s')) {
			return "{data: {ref_id: '', status: 0, code: '', hp: '', price: '', message: '', balance: '', tr_id: '', rc: }}";
		}
		$password = $req['password'];
		$username   = "089687271843";
		$apiKey   = "7285d8726bcde318728";
		$ref_id  = $req['reff_id'];
		$code = $req['code'];
		$signature  = md5($username.$apiKey.$ref_id);
		$json = '{
				"commands"    : "topup",
				"username"    : "089687271843",
				"ref_id"      : "'.$ref_id.'",
				"hp"          : "'.$req['hp'].'",
				"pulsa_code"  : "'.$code.'",
				"sign"        : "'.md5($username.$apiKey.$ref_id).'"
				}';
		$banlist = DB::select('SELECT * FROM hp_ban WHERE no='.$req['hp']);
		if(!empty($banlist)) {
			$apiKey   = "6845d79e9afc378c";
			$signature  = md5($username.$apiKey.$ref_id);
			$json = '{
				"commands"    : "topup",
				"username"    : "089687271843",
				"ref_id"      : "'.$ref_id.'",
				"hp"          : "'.$req['hp'].'",
				"pulsa_code"  : "'.$code.'",
				"sign"        : "'.md5($username.$apiKey.$ref_id).'"
				}';
			$url = "https://testprepaid.mobilepulsa.net/v1/legacy/index";
		} else {
			$url = "https://api.mobilepulsa.net/v1/legacy/index";
		}
		if($members_id == "" || $members_id == null) {
			return '{
				"data": {
					"trx_id": "",
					"saldo": "",
					"rc": "0",
					"message": "Data members_id kosong, Hubungi Admin!",
					"bit11": "",
					"bit12": "",
					"bit48": "",
					"bit62": ""
				}
			}';
		}
		$member = DB::select('SELECT id, saldo, sponsor, username, password FROM members WHERE id='.$members_id);
		if(empty($member)){
			return '{
				"data": {
					"trx_id": "",
					"saldo": "",
					"rc": "0",
					"message": "Data member tidak terdaftar, Hubungi Admin!",
					"bit11": "",
					"bit12": "",
					"bit48": "",
					"bit62": ""
				}
			}';
		}
		$chkPwd = Hash::check($password, $member[0]->password);
		if(!$chkPwd) {
			return '{
				"data": {
					"trx_id": "",
					"saldo": "",
					"rc": "0",
					"message": "Password Salah!",
					"bit11": "",
					"bit12": "",
					"bit48": "",
					"bit62": ""
				}
			}';
		}
		$lamount = array(
			"htelkomsel1000" => array(1900, 1900),
			"htelkomsel2000" => array(3200, 3200), //, 
			"htelkomsel3000" => array(4800, 4800), //4800, 
			"htelkomsel5000" => array(5900, 5900), //5900,
			"htelkomsel10000" => array(10850, 10850), //10850,
			"htelkomsel15000" => array(15500, 15500), //15500,
			"htelkomsel20000" => array(20500, 20500), //20500,
			"htelkomsel25000" => array(25000, 25000), //25000,
			"htelkomsel40000" => array(40000, 40000), //40000,
			"htelkomsel50000" => array(49750, 49750), //49750,
			"htelkomsel100000" => array(98500, 98500), //98500,
			"htelkomsel150000" => array(148750, 148750), //148750,
			"htelkomsel200000" => array(198000, 198000), //198000,
			"htelkomsel300000" => array(297500, 297500), //297500,
			"htelkomsel500000" => array(495000, 495000), //495000,
			"htelkomsel1000000" => array(987500, 987500), //987500,
			"hindosat5000" => array(5990, 5990), //5990,
			"hindosat10000" => array(10990, 10990), //10990,
			"hindosat12000" => array(12500, 12500), //12500,
			"hindosat20000" => array(20200, 20200), //20200,
			"hindosat25000" => array(24900, 24900), //24900,
			"hindosat30000" => array(30550, 30550), //30550,
			"hindosat50000" => array(49250, 49250), //49250,
			"hindosat60000" => array(58800, 58800), //58800,
			"hindosat80000" => array(78000, 78000), //78000,
			"hindosat100000" => array(98000, 98000), //98000,
			"hindosat150000" => array(143000, 143000), //143000,
			"hindosat200000" => array(185500, 185500), //185500,
			"hindosat250000" => array(232000, 232000), //232000,
			"hindosat500000" => array(463000, 463000), //463000,
			"hindosat1000000" => array(926000, 926000), //926000,
			"xld5000" => array(5800, 5800), //5800,
			"xld10000" => array(10800, 10800), //10800,
			"xld15000" => array(15300, 15300), //15300,
			"xld25000" => array(24900, 24900), //24900,
			"xld30000" => array(29900, 29900), //29900,
			"xld50000" => array(49700, 49700), //49700,
			"xld100000" => array(99250, 99250), //99250,
			"xld150000" => array(150000, 150000), //150000,
			"xld200000" => array(198500, 198500), //198500,
			"xld300000" => array(298500, 298500), //298500,
			"xld500000" => array(495000, 495000), //495000,
			"xld1000000" => array(990000, 990000), //990000,
			"haxis5000" => array(5800, 5800), //5800,
			"haxis10000" => array(10800, 10800), //10800,
			"haxis15000" => array(14925, 14925), //14925,
			"haxis25000" => array(24900, 24900), //24900,
			"haxis50000" => array(49700, 49700), //49700,
			"haxis100000" => array(99250, 99250), //99250,
			"haxis200000" => array(198500, 198500), //198500,
			"hthree1000"  => array(1300, 1300), //1300,
			"hthree2000"  => array(2250, 2250), //2250,
			"hthree3000"  => array(3450, 3450), //3450,
			"hthree5000"  => array(5400, 5400), //5400,
			"hthree10000"  => array(10400, 10400), //10400,
			"hthree15000"  => array(15000, 15000), //15000,
			"hthree20000"  => array(19700, 19700), //19700,
			"hthree25000"  => array(24625, 24625), //24625,
			"hthree30000"  => array(30000, 30000), //30000,
			"hthree50000"  => array(49000, 49000), //49000,
			"hthree100000"  => array(98500, 98500), //98500,
			"hthree150000"  => array(148500, 148500), //148500,
			"hthree200000"  => array(199000, 199000), //199000,
			"hthree300000"  => array(297000, 297000), //297000,
			"hthree500000"  => array(495000, 495000), //495000,
			"hthree1000000"  => array(990000, 990000), //990000,
			"hsmart5000" => array(5175, 5175), //5175,
			"hsmart10000" => array(10100, 10100), //10100,
			"hsmart20000" => array(19800, 19800), //19800,
			"hsmart25000" => array(24800, 24800), //24800,
			"hsmart50000" => array(49500, 49500), //49500,
			"hsmart60000" => array(60000, 60000), //60000,
			"hsmart100000" => array(97550, 97550), //97550,
			"hsmart150000" => array(147000, 147000), //147000,
			"hsmart200000" => array(196000, 196000), //196000,
			"hsmart300000" => array(294000, 294000), //294000,
			"hsmart500000" => array(490000, 490000), //490000,
			"hsmart1000000" => array(980000, 980000), //980000,
			"hceria50000" => array(50000, 50000), //50000,
			"hceria100000" => array(100000, 100000), //100000,
			"hceria200000" => array(200000, 200000), //200000,
			"tseldata10000" => array(10700, 10700),
			"tseldata100000" => array(99500, 99500),
			"tseldata1gb" => array(21000, 21000),
			"tseldata20000" => array(20750, 20750),
			"tseldata200000" => array(198500, 198500),
			"tseldata25000" => array(25750, 25750),
			"tseldata2gb" => array(45000, 45000),
			"tseldata3gb" => array(70000, 70000),
			"tseldata5000" => array(5900, 5900),
			"tseldata50000" => array(50750, 50750),
			"tseldata500MB" => array(14800, 14800),
			"tseldata750MB" => array(22500, 22500),
			"tseldataGM25000" => array(24950, 24950),
			"tseldataVM10000" => array(10500, 10500),
			"tseldataVM20000" => array(20900, 20900),
			"tseldataVM50000" => array(50000, 50000),
			"indosatPHD10" => array(232000, 232000),
			"indosatPHD20" => array(349900, 349900),
			"indosatPHD40" => array(549900, 549900),
			"indosatPHI30" => array(189400, 189400),
			"indosatPHK20" => array(449900, 449900),
			"indosatPHK40" => array(649900, 649900),
			"indosatPHV40" => array(199900, 199900),
			"isatdata100mb" => array(3500, 3500),
			"isatdata1gb" => array(13000, 13000),
			"isatdata200mb" => array(5500, 5500),
			"isatdata2gb" => array(24500, 24500),
			"isatdata300mb" => array(6200, 6200),
			"isatdata3gb" => array(36200, 36200),
			"isatdata4gb" => array(48100, 48100),
			"isatdata500mb" => array(10100, 10100),
			"isatdata5gb" => array(59900, 59900),
			"isatdata700mb" => array(12100, 12100),
			"isatdataex2gb" => array(38000, 38000),
			"isatdataex4gb" => array(57000, 57000),
			"isatdataex6gb" => array(76000, 76000),
			"isatdataF1" => array(24000, 24000),
			"isatdataF2" => array(34000, 34000),
			"isatdataF5" => array(98000, 98000),
			"isatdataF6" => array(147000, 147000),
			"isatdataF7" => array(195000, 195000),
			"isatdataFDRAA3D" => array(243000, 243000),
			"isatdataFDRAA5D" => array(292500, 292500),
			"isatdataFDRAA7D" => array(439000, 439000),
			"isatdataFDRAE3D" => array(534000, 534000),
			"isatdataFDRAE5D" => array(877000, 877000),
			"isatdataFDRAE7D" => array(1219000, 1219000),
			"isatdataFIP130" => array(130000, 130000),
			"isatdataFIP40" => array(40000, 40000),
			"isatdataFIP60" => array(60000, 60000),
			"isatdataFIP80" => array(80000, 80000),
			"isatdataFL" => array(89000, 89000),
			"isatdataFM" => array(56050, 56050),
			"isatdataFXL" => array(115000, 115000),
			"isatdataFXXL" => array(148000, 148000),
			"isatdataHAJI10" => array(245000, 245000),
			"isatdataHAJI15" => array(325000, 325000),
			"isatdataHAJI20" => array(350000, 350000),
			"isatdataHAJI30" => array(500000, 500000),
			"isatdataHAJI45" => array(650000, 650000),
			"isatdataHAJICOMBO10" => array(325000, 325000),
			"isatdataHAJICOMBO15" => array(450000, 450000),
			"isatdataHAJICOMBO20" => array(500000, 500000),
			"isatdataHAJICOMBO30" => array(600000, 600000),
			"isatdataHAJICOMBO45" => array(750000, 750000),
			"isatdataHAJITELP10" => array(200000, 200000),
			"isatdataHAJITELP15" => array(245000, 245000),
			"isatdataHAJITELP20" => array(275000, 275000),
			"isatdataHAJITELP30" => array(325000, 325000),
			"isatdataHAJITELP45" => array(375000, 375000),
			"isatdataHII1GB" => array(9500, 9500),
			"isatdataHIU10GB" => array(94090, 94090),
			"isatdataHIU15GB" => array(120280, 120280),
			"isatdataHIU18GB" => array(260000, 260000),
			"isatdataHIU1GB" => array(26000, 26000),
			"isatdataHIU2GB" => array(40000, 40000),
			"isatdataHIU36GB" => array(10100, 10100),
			"isatdataHIU3GB" => array(10100, 10100),
			"isatdataHIU5GB" => array(10100, 10100),
			"isatdataHIU7GB" => array(10100, 10100),
			"isatdataHIUJ" => array(10100, 10100),
			"isatdataMY150" => array(10100, 10100),
			"isatdataMY300" => array(10100, 10100),
			"isatdataSG150" => array(10100, 10100),
			"isatdataSG300" => array(10100, 10100),
			"isatdataY15D" => array(10100, 10100),
			"isatdataY1D" => array(10100, 10100),
			"isatdataY3D" => array(10100, 10100),
			"xldataCUH10" => array(299000, 299000),
			"xldataCUH20" => array(399000, 399000),
			"xldataCUH40" => array(549000, 549000),
			"xldataCVIPA" => array(69000, 69000),
			"xldataCVIPB" => array(99000, 99000),
			"xldataCVIPC" => array(139000, 139000),
			"xldataCVIPD" => array(189000, 189000),
			"xldataCVIPE" => array(249000, 249000),
			"xldataHR100" => array(98000, 98000),
			"xldataHR130" => array(125800, 125800),
			"xldataHR180" => array(173800, 173800),
			"xldataHR220" => array(212200, 212200),
			"xldataHR30" => array(30000, 30000),
			"xldataHR60" => array(58600, 58600),
			"xldataIUH10" => array(199000, 199000),
			"xldataIUH20" => array(249000, 249000),
			"xldataIUH40" => array(379000, 379000),
			"xldataNSUH10" => array(149000, 149000),
			"xldataNSUH20" => array(179000, 179000),
			"xldataNSUH40" => array(199000, 199000),
			"xldataPASS1D" => array(85000, 85000),
			"xldataPASS30D" => array(750000, 750000),
			"xldataPASS3D" => array(150000, 150000),
			"xldataPASS7D" => array(200000, 200000),
			"xldataRC1" => array(160000, 160000),
			"xldataRC3" => array(225000, 225000),
			"xldataRC30" => array(825000, 825000),
			"xldataRC7" => array(425000, 425000),
			"xldataUCL" => array(438000, 438000),
			"xldataUCM" => array(300000, 300000),
			"xldataUCS" => array(194000, 194000),
			"xldataXCA12X" => array(599000, 599000),
			"xldataXCB12X" => array(799000, 799000),
			"xldataXCC12X" => array(999000, 999000),
			"xldataXCD12X" => array(1299000, 1299000),
			"xldataXCE12X" => array(1399000, 1399000),
			"xldataXT129" => array(124840, 124840),
			"xldataXT12912X" => array(1248000, 1248000),
			"xldataXT179" => array(172840, 172840),
			"xldataXT17912X" => array(1628000, 1628000),
			"xldataXT239" => array(230440, 230440),
			"xldataXT23912X" => array(2168000, 2168000),
			"xldataXT59" => array(57640, 57640),
			"xldataXT5912X" => array(588000, 588000),
			"xldataXT89" => array(86440, 86440),
			"xldataXT8912X" => array(868000, 868000),
			"xldataXTB1" => array(35000, 35000),
			"xldataXTB2" => array(30000, 30000),
			"xldataXTK30GB" => array(11900, 11900),
			"axisdata1gb" => array(22000, 22000),
			"axisdata2gb" => array(30000, 30000),
			"axisdata3gb" => array(39000, 39000),
			"axisdata5gb" => array(59000, 59000),
			"axisdataAIGO1" => array(16000, 16000),
			"axisdataAIGO2" => array(29000, 29000),
			"axisdataAIGO3" => array(32800, 32800),
			"axisdataAIGO5" => array(46800, 46800),
			"axisdataAIGOM1" => array(9000, 9000),
			"axisdataAIGOM2" => array(16000, 16000),
			"axisdataAIGOM3" => array(20500, 20500),
			"axisdataAIGOM5" => array(31700, 31700),
			"axisdataKZLCH30" => array(14500, 14500),
			"axisdataKZLCH7" => array(4750, 4750),
			"axisdataKZLCOMBO30" => array(19400, 19400),
			"axisdataKZLCOMBO7" => array(6650, 6650),
			"axisdataKZLGA30" => array(9650, 9650),
			"axisdataKZLGA7" => array(4750, 4750),
			"axisdataKZLGV30" => array(9650, 9650),
			"axisdataKZLGV7" => array(4750, 4750),
			"axisdataKZLSM30" => array(14500, 14500),
			"axisdataKZLSM7" => array(4750, 4750),
			"axisdataOM12" => array(69400, 69400),
			"axisdataOM2" => array(24500, 24500),
			"axisdataOM4" => array(34400, 34400),
			"axisdataOM8" => array(54400, 54400),
			"threedata100GB7" => array(88500, 88500),
			"threedata1250mb" => array(33000, 33000),
			"threedata2250mb" => array(50000, 50000),
			"threedata4G10GB" => array(49500, 49500),
			"threedata4G30GB" => array(99000, 99000),
			"threedata4G32GB" => array(59500, 59500),
			"threedata4GDP375GB14D" => array(24750, 24750),
			"threedata4GL" => array(89100, 89100),
			"threedataAEROPASS10USSD" => array(742500, 742500),
			"threedataAEROPASS5USSD" => array(396000, 396000),
			"threedataIbadah10D" => array(186165, 186165),
			"threedataIbadah40D" => array(320125, 320125),
			"threedataIS275GB" => array(9950, 9950),
			"threedataIS3GB3D" => array(19900, 19900),
			"threedataIS5GB1D" => array(4975, 4975),
			"threedataMP2GB20M" => array(34650, 34650),
			"threedataNS4G" => array(59500, 59500),
			"threedataNS8G" => array(125000, 125000),
			"threedataSEAPASS1USSD" => array(44550, 44550),
			"threedataSEAPASS3USSD" => array(118800, 118800),
			"threedataSEAPASS7USSD" => array(217800, 217800),
			"threedataTAP1" => array(99000, 99000),
			"threedataTAP3" => array(173250, 173250),
			"threedataTAP30" => array(396000, 396000),
			"threedataTAP7" => array(277200, 277200),
			"smartdataVOL10" => array(9950, 9950),
			"smartdataVOL100" => array(98000, 98000),
			"smartdataVOL150" => array(147000, 147000),
			"smartdataVOL20" => array(19800, 19800),
			"smartdataVOL200" => array(196000, 196000),
			"smartdataVOL30" => array(29700, 29700),
			"smartdataVOL60" => array(59100, 59100)
		);
		$actualprice = $lamount[$req['code']][1] + 1000;
		if($member[0]->saldo < $actualprice) {
			$tp = array(
				'member_id' => $members_id,
				'log_id' => '0',
				'target' => $req['code'],
				'reff_id' => $ref_id,
				'prodname' => $req['hp'],
				'amount' => $actualprice,
				'status' => 'FAILED',
				'message' => 'Insufficient balance',
				'time' => date('Y-m-d H:i:s'),
				'payload' => json_encode($json)
			);
			T_transaction::create($tp);
			return '{
				"data": {
					"trx_id": "",
					"saldo": "",
					"rc": "0",
					"desc": "Saldo Tidak Cukup!",
					"bit11": "",
					"bit12": "",
					"bit48": "",
					"bit62": ""
				}
			}';
		}
		$ch  = curl_init();
		curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $json);
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

		$data = curl_exec($ch);
		curl_close($ch);
		$rd = json_decode($data);
		if($rd->data->status == 2){
			return $data;
		}
		$tp = array(
			'member_id' => $members_id,
			'log_id' => $rd->data->status,
			'target' => $req['hp'],
			'reff_id' => $ref_id,
			'prodname' => $code,
			'amount' => $actualprice,
			'status' => 'SUCCESS',
			'message' => 'Pembayaran '.$req['trxtype'].' Berhasil',
			'time' => date('Y-m-d H:i:s'),
			'payload' => json_encode($json)
		);
		T_transaction::create($tp);

		$ha = 1000 * (25/100);
		$vshare = 1000 - $ha;
		$v10 = $vshare * (10/100);
		$v15 = $vshare * (15/100);
		$v5 = $vshare * (5/100);
		// CASHBACK
		$cb = DB::update('UPDATE members SET saldo=saldo+? WHERE id=?', [$v10,$members_id]);
		$pcb = array(
			'reff_id' => $ref_id,
			'username' => $member[0]->username,
			'level' => 0,
			'amount' => $v10
		);
		T_bonusgenerasi::create($pcb);
		// LEVEL 1
		$cm1 = DB::select('SELECT username,sponsor FROM members WHERE username=?', [$member[0]->sponsor]);
		if(count($cm1) > 0) {
			$cb1 = DB::update('UPDATE members SET saldo=saldo+? WHERE username=?', [$v15,$cm1[0]->username]);
			$pbg1 = array(
				'reff_id' => $ref_id,
				'username' => $cm1[0]->username,
				'level' => 1,
				'amount' => $v15
			);
			T_bonusgenerasi::create($pbg1);
			// LEVEL 2
			$cm2 = DB::select('SELECT username,sponsor FROM members WHERE username=?', [$cm1[0]->sponsor]);
			if(count($cm2) > 0) {
				$cb2 = DB::update('UPDATE members SET saldo=saldo+? WHERE username=?', [$v15,$cm2[0]->username]);
				$pbg2 = array(
					'reff_id' => $ref_id,
					'username' => $cm2[0]->username,
					'level' => 2,
					'amount' => $v15
				);
				T_bonusgenerasi::create($pbg2);
				// LEVEL 3
				$cm3 = DB::select('SELECT username,sponsor FROM members WHERE username=?', [$cm2[0]->sponsor]);
				if(count($cm3) > 0) {
					$cb3 = DB::update('UPDATE members SET saldo=saldo+? WHERE username=?', [$v10,$cm3[0]->username]);
					$pbg3 = array(
						'reff_id' => $ref_id,
						'username' => $cm3[0]->username,
						'level' => 3,
						'amount' => $v10
					);
					T_bonusgenerasi::create($pbg3);
					// LEVEL 4
					$cm4 = DB::select('SELECT username,sponsor FROM members WHERE username=?', [$cm3[0]->sponsor]);
					if(count($cm4) > 0) {
						$cb4 = DB::update('UPDATE members SET saldo=saldo+? WHERE username=?', [$v10,$cm4[0]->username]);
						$pbg4 = array(
							'reff_id' => $ref_id,
							'username' => $cm4[0]->username,
							'level' => 4,
							'amount' => $v10
						);
						T_bonusgenerasi::create($pbg4);
						// LEVEL 5
						$cm5 = DB::select('SELECT username,sponsor FROM members WHERE username=?', [$cm4[0]->sponsor]);
						if(count($cm5) > 0) {
							$cb5 = DB::update('UPDATE members SET saldo=saldo+? WHERE username=?', [$v10,$cm5[0]->username]);
							$pbg5 = array(
								'reff_id' => $ref_id,
								'username' => $cm5[0]->username,
								'level' => 5,
								'amount' => $v10
							);
							T_bonusgenerasi::create($pbg5);
							// LEVEL 6
							$cm6 = DB::select('SELECT username,sponsor FROM members WHERE username=?', [$cm5[0]->sponsor]);
							if(count($cm6) > 0) {
								$cb6 = DB::update('UPDATE members SET saldo=saldo+? WHERE username=?', [$v10,$cm6[0]->username]);
								$pbg6 = array(
									'reff_id' => $ref_id,
									'username' => $cm6[0]->username,
									'level' => 6,
									'amount' => $v10
								);
								T_bonusgenerasi::create($pbg6);
								// LEVEL 7
								$cm7 = DB::select('SELECT username,sponsor FROM members WHERE username=?', [$cm6[0]->sponsor]);
								if(count($cm7) > 0) {
									$cb7 = DB::update('UPDATE members SET saldo=saldo+? WHERE username=?', [$v5,$cm7[0]->username]);
									$pbg7 = array(
										'reff_id' => $ref_id,
										'username' => $cm7[0]->username,
										'level' => 7,
										'amount' => $v5
									);
									T_bonusgenerasi::create($pbg7);
									// LEVEL 8
									$cm8 = DB::select('SELECT username,sponsor FROM members WHERE username=?', [$cm7[0]->sponsor]);
									if(count($cm8) > 0) {
										$cb8 = DB::update('UPDATE members SET saldo=saldo+? WHERE username=?', [$v5,$cm8[0]->username]);
										$pbg8 = array(
											'reff_id' => $ref_id,
											'username' => $cm8[0]->username,
											'level' => 8,
											'amount' => $v10
										);
										T_bonusgenerasi::create($pbg8);
										// LEVEL 9
										$cm9 = DB::select('SELECT username,sponsor FROM members WHERE username=?', [$cm8[0]->sponsor]);
										if(count($cm9) > 0) {
											$cb9 = DB::update('UPDATE members SET saldo=saldo+? WHERE username=?', [$v5,$cm9[0]->username]);
											$pbg9 = array(
												'reff_id' => $ref_id,
												'username' => $cm9[0]->username,
												'level' => 9,
												'amount' => $v5
											);
											T_bonusgenerasi::create($pbg9);
											// LEVEL 10
											$cm10 = DB::select('SELECT username,sponsor FROM members WHERE username=?', [$cm9[0]->sponsor]);
											if(count($cm10) > 0) {
												$cb10 = DB::update('UPDATE members SET saldo=saldo+? WHERE username=?', [$v5,$cm10[0]->username]);
												$pbg10 = array(
													'reff_id' => $ref_id,
													'username' => $cm10[0]->username,
													'level' => 10,
													'amount' => $v5
												);
												T_bonusgenerasi::create($pbg10);
											}
										}
									}
								}
							}
						}
					}
				}
			}
		}
		$mp = $actualprice;
		$ns = $member[0]->saldo - $mp;
		$xusr = User::where('id', '=', $member[0]->id)->first();
		$xusr->update(array('saldo' => $ns));
		$trxname = 'PULSA';
		if(isset($req['trxtype'])) {
			$trxname = $req['trxtype'];
		}
		DB::table('t_ppob')->insert(
			[
				'members_id' => $members_id,
				'trx_id' => $ref_id,
				'trx_date' => Date('Y-m-d H:i:s'),
				'trx_name' => $trxname,
				'no_hp' => $req['hp'],
				'tagihan' => $lamount[$req['code']][1], 
				'fee_admin' => 1000,
				'total_tagihan' => $actualprice,
				'ending_saldo' => $ns,
				'product_code' => $code,
				'status' => "Berhasil",
				'status_bonus' => '0'
			]
		);
		return $data;
	}
	function inquiryPasca(Request $request) {
		$req = $request->all();
		$members_id = $req['member_id'];
		$password = $req['password'];
		if($members_id == "" || $members_id == null) {
			return '{
				"data": {
					"trx_id": "",
					"saldo": "",
					"rc": "0",
					"desc": "Data members_id kosong, Hubungi Admin!",
					"bit11": "",
					"bit12": "",
					"bit48": "",
					"bit62": ""
				}
			}';
		}
		$member = DB::select('SELECT id, saldo FROM members WHERE id='.$members_id.' AND password ="'.$password.'"');
		if(empty($member)){
			return '{
				"data": {
					"trx_id": "",
					"saldo": "",
					"rc": "0",
					"desc": "Data member tidak terdaftar, Hubungi Admin!",
					"bit11": "",
					"bit12": "",
					"bit48": "",
					"bit62": ""
				}
			}';
		}
		if($member[0]->saldo < $req['amount']) {
			$tp = array(
				'member_id' => $members_id,
				'log_id' => '0',
				'target' => $req['target'],
				'reff_id' => $req['reffid'],
				'prodname' => $req['prod'],
				'amount' => 0,
				'status' => 'FAILED',
				'message' => 'Saldo tidak cukup',
				'time' => date('Y-m-d H:i:s'),
				'payload' => json_encode($payload)
			);
			T_transaction::create($tp);
			return '{
				"data": {
					"trx_id": "",
					"saldo": "",
					"rc": "0",
					"desc": "Saldo Tidak Cukup!",
					"bit11": "",
					"bit12": "",
					"bit48": "",
					"bit62": ""
				}
			}';
		}
		$username   = "089687271843";
		$apiKey   	= "6845d79e9afc378c";
		$ref_id  	= uniqid('');
		$signature  = md5($username.$apiKey.$ref_id);
		$code  		= $req['code'];
		$hp  		= $req['hp'];
		$month  	= $req['month'];

		$json = '{
				"commands" : "inq-pasca",
				"username" : "'.$username.'",
				"code"     : "'.$code.'",
				"ref_id"   : "'.$ref_id.'",
				"hp"       : "'.$hp.'",
				"sign"     : "'.md5($username.$apiKey.$ref_id).'",
				"month"    : "'.$month.'"
			}';

		$url = "https://testpostpaid.mobilepulsa.net/api/v1/bill/check";

		$ch  = curl_init();
		curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $json);
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

		$data = curl_exec($ch);
		curl_close($ch);
		return $data;
	}
	function inquiryPay(Request $request) {
		$req = $request->all();
		$username   = "089687271843";
		$apiKey   	= "6845d79e9afc378c";
		$tr_id  	= $req['tr_id'];
		$signature  = md5($username.$apiKey.$tr_id);

		$json = '{
				"commands" : "pay-pasca",
				"username" : "'.$username.'",
				"tr_id"    : "'.$tr_id.'",
				"sign"     : "'.$signature.'"
				}';

		$url = "https://testpostpaid.mobilepulsa.net/api/v1/bill/check";

		$ch  = curl_init();
		curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $json);
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

		$data = curl_exec($ch);
		curl_close($ch);
		return $data;
	}
	function topupSaldo(Request $request) {
		$requestData = $request->all();
		$requestData['created_at'] = Date('Y-m-d H:i:s');
		$requestData['updated_at'] = Date('Y-m-d H:i:s');
		return Saldo::create($requestData);
	}
	function topupBonus(Request $request) {
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
		$pl = array(
			"members_id" => 569,
			"sender" => "starpreneur",
			"receiver" => $req['username'],
			"nominal" => $req['bonus'],
			"ending_saldo" => $usr->saldo + (int)$req['bonus'],
			"date" => date("Y-m-d h:i:s"),
			"status" => "bonus",
			"created_at" => date("Y-m-d h:i:s"),
			"updated_at" => date("Y-m-d h:i:s")
		);
		DB::table('t_transfer_saldo')->insert($pl);
		$arrUser = array("saldo" => $usr->saldo + (int)$req['bonus']);
		$usr->update($arrUser);
		return array(
			'message' => "add bonus success"
		);
	}
	function indextest(Request $request) {
		$req = $request->all();
		$members_id = $req['member_id'];
		$password = $req['password'];
		$username   = "089687271843";
		$apiKey   = "6845d79e9afc378c";
		$ref_id  = uniqid('');
		$markup = 0;
		$code = $req['code'];
		$signature  = md5($username.$apiKey.$ref_id);
		$json = '{
			"commands"    : "topup",
			"username"    : "089687271843",
			"ref_id"      : "'.$ref_id.'",
			"hp"          : "'.$req['hp'].'",
			"pulsa_code"  : "'.$code.'",
			"sign"        : "'.md5($username.$apiKey.$ref_id).'"
			}';
		$url = "https://testprepaid.mobilepulsa.net/v1/legacy/index";
		$ch  = curl_init();
		curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $json);
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

		$data = curl_exec($ch);
		curl_close($ch);
		$rd = json_decode($data);
		return $data;
		if($members_id == "" || $members_id == null) {
			return '{
				"data": {
					"trx_id": "",
					"saldo": "",
					"rc": "0",
					"desc": "Data members_id kosong, Hubungi Admin!",
					"bit11": "",
					"bit12": "",
					"bit48": "",
					"bit62": ""
				}
			}';
		}
		$member = DB::select('SELECT id, saldo FROM members WHERE id='.$members_id.' AND password ="'.$password.'"');
		if(empty($member)){
			return '{
				"data": {
					"trx_id": "",
					"saldo": "",
					"rc": "0",
					"desc": "Data member tidak terdaftar, Hubungi Admin!",
					"bit11": "",
					"bit12": "",
					"bit48": "",
					"bit62": ""
				}
			}';
		}
		$lamount = array(
			"hpln20000" =>	array(20000, 20500, 2500),
			"hpln50000" =>  array(50000,50500, 2500),
			"hpln100000" => array(100000, 100500, 2500),
			"hpln200000" => array(200000, 200500, 2500),
			"hpln500000" => array(500000, 500500, 2500),
			"hpln1000000" => array(100000, 1000500, 2500),
		);

		$actualprice = $lamount[$req['code']][1] + $lamount[$req['code']][2];

		if($member[0]->saldo < $actualprice) {
			$tp = array(
				'member_id' => $members_id,
				'log_id' => '0',
				'target' => $req['code'],
				'reff_id' => $ref_id,
				'prodname' => $req['hp'],
				'amount' => $actualprice,
				'status' => 'FAILED',
				'message' => 'Insufficient balance',
				'time' => date('Y-m-d H:i:s'),
				'payload' => json_encode($json)
			);
			// T_transaction::create($tp);
			return '{
				"data": {
					"trx_id": "",
					"saldo": "",
					"rc": "0",
					"desc": "Saldo Tidak Cukup!",
					"bit11": "",
					"bit12": "",
					"bit48": "",
					"bit62": ""
				}
			}';
		}
		$tp = array(
			'member_id' => $members_id,
			'log_id' => '0',
			'target' => $req['hp'],
			'reff_id' => $ref_id,
			'prodname' => $code,
			'amount' => $actualprice,
			'status' => 'SUCCESS',
			'message' => 'Inquiry Pulsa Berhasil',
			'time' => date('Y-m-d H:i:s'),
			'payload' => json_encode($json)
		);
		// T_transaction::create($tp);
		$data = '{"data":{
			"ref_id":"'.$ref_id.'",
			"status":1,
			"code":"'.$code.'",
			"hp":"'.$req['hp'].'",
			"pulsa": "'.$lamount[$req['code']][0].'",
			"price": '.$actualprice.',
			"message":"INQUIRY",
			"balance":0,
			"tr_id":"'.$ref_id.'",
			"rc":"0001"
		}}';
		return $data;
	}
	function paytest(Request $request) {
		$req = $request->all();
		$members_id = $req['member_id'];
		$password = $req['password'];
		$username   = "089687271843";
		$apiKey   = "6845d79e9afc378c";
		$ref_id  = uniqid('');
		$code = $req['code'];
		$signature  = md5($username.$apiKey.$ref_id);
		$json = '{
				"commands"    : "topup",
				"username"    : "089687271843",
				"ref_id"      : "'.$ref_id.'",
				"hp"          : "'.$req['hp'].'",
				"pulsa_code"  : "'.$code.'",
				"sign"        : "'.md5($username.$apiKey.$ref_id).'"
				}';
		return $json;
		$url = "https://testprepaid.mobilepulsa.net/v1/legacy/index";
		if($members_id == "" || $members_id == null) {
			return '{
				"data": {
					"trx_id": "",
					"saldo": "",
					"rc": "0",
					"desc": "Data members_id kosong, Hubungi Admin!",
					"bit11": "",
					"bit12": "",
					"bit48": "",
					"bit62": ""
				}
			}';
		}
		$member = DB::select('SELECT id, saldo, sponsor, username FROM members WHERE id='.$members_id.' AND password ="'.$password.'"');
		if(empty($member)){
			return '{
				"data": {
					"trx_id": "",
					"saldo": "",
					"rc": "0",
					"desc": "Data member tidak terdaftar, Hubungi Admin!",
					"bit11": "",
					"bit12": "",
					"bit48": "",
					"bit62": ""
				}
			}';
		}
		$lamount = array(
			"hpln20000" =>	array(20000, 20500, 2500),
			"hpln50000" =>  array(50000,50500, 2500),
			"hpln100000" => array(100000, 100500, 2500),
			"hpln200000" => array(200000, 200500, 2500),
			"hpln500000" => array(500000, 500500, 2500),
			"hpln1000000" => array(100000, 1000500, 2500),
		);

		$actualprice = $lamount[$req['code']][1] + $lamount[$req['code']][2];

		if($member[0]->saldo < $actualprice) {
			$tp = array(
				'member_id' => $members_id,
				'log_id' => '0',
				'target' => $req['code'],
				'reff_id' => $ref_id,
				'prodname' => $req['hp'],
				'amount' => $actualprice,
				'status' => 'FAILED',
				'message' => 'Insufficient balance',
				'time' => date('Y-m-d H:i:s'),
				'payload' => json_encode($json)
			);
			T_transaction::create($tp);
			return '{
				"data": {
					"trx_id": "",
					"saldo": "",
					"rc": "0",
					"desc": "Saldo Tidak Cukup!",
					"bit11": "",
					"bit12": "",
					"bit48": "",
					"bit62": ""
				}
			}';
		}

		$ch  = curl_init();
		curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $json);
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

		$data = curl_exec($ch);
		curl_close($ch);
		$rd = json_decode($data);
		$tp = array(
			'member_id' => $members_id,
			'log_id' => '0',
			'target' => $req['hp'],
			'reff_id' => $ref_id,
			'prodname' => $code,
			'amount' => $actualprice,
			'status' => 'SUCCESS',
			'message' => 'Pembayaran PLN Berhasil',
			'time' => date('Y-m-d H:i:s'),
			'payload' => json_encode($json)
		);
		T_transaction::create($tp);

		$ha = $lamount[$req['code']][2] * (25/100);
		$vshare = $lamount[$req['code']][2] - $ha;
		$v10 = $vshare * (10/100);
		$v15 = $vshare * (15/100);
		$v5 = $vshare * (5/100);
		// CASHBACK
		$cb = DB::update('UPDATE members SET saldo=saldo+? WHERE id=?', [$v10,$members_id]);
		$pcb = array(
			'reff_id' => $ref_id,
			'username' => $member[0]->username,
			'level' => 0,
			'amount' => $v10
		);
		T_bonusgenerasi::create($pcb);
		// LEVEL 1
		$cm1 = DB::select('SELECT username,sponsor FROM members WHERE username=?', [$member[0]->sponsor]);
		if(count($cm1) > 0) {
			$cb1 = DB::update('UPDATE members SET saldo=saldo+? WHERE username=?', [$v15,$cm1[0]->username]);
			$pbg1 = array(
				'reff_id' => $ref_id,
				'username' => $cm1[0]->username,
				'level' => 1,
				'amount' => $v15
			);
			T_bonusgenerasi::create($pbg1);
			// LEVEL 2
			$cm2 = DB::select('SELECT username,sponsor FROM members WHERE username=?', [$cm1[0]->sponsor]);
			if(count($cm2) > 0) {
				$cb2 = DB::update('UPDATE members SET saldo=saldo+? WHERE username=?', [$v15,$cm2[0]->username]);
				$pbg2 = array(
					'reff_id' => $ref_id,
					'username' => $cm2[0]->username,
					'level' => 2,
					'amount' => $v15
				);
				T_bonusgenerasi::create($pbg2);
				// LEVEL 3
				$cm3 = DB::select('SELECT username,sponsor FROM members WHERE username=?', [$cm2[0]->sponsor]);
				if(count($cm3) > 0) {
					$cb3 = DB::update('UPDATE members SET saldo=saldo+? WHERE username=?', [$v10,$cm3[0]->username]);
					$pbg3 = array(
						'reff_id' => $ref_id,
						'username' => $cm3[0]->username,
						'level' => 3,
						'amount' => $v10
					);
					T_bonusgenerasi::create($pbg3);
					// LEVEL 4
					$cm4 = DB::select('SELECT username,sponsor FROM members WHERE username=?', [$cm3[0]->sponsor]);
					if(count($cm4) > 0) {
						$cb4 = DB::update('UPDATE members SET saldo=saldo+? WHERE username=?', [$v10,$cm4[0]->username]);
						$pbg4 = array(
							'reff_id' => $ref_id,
							'username' => $cm4[0]->username,
							'level' => 4,
							'amount' => $v10
						);
						T_bonusgenerasi::create($pbg4);
						// LEVEL 5
						$cm5 = DB::select('SELECT username,sponsor FROM members WHERE username=?', [$cm4[0]->sponsor]);
						if(count($cm5) > 0) {
							$cb5 = DB::update('UPDATE members SET saldo=saldo+? WHERE username=?', [$v10,$cm5[0]->username]);
							$pbg5 = array(
								'reff_id' => $ref_id,
								'username' => $cm5[0]->username,
								'level' => 5,
								'amount' => $v10
							);
							T_bonusgenerasi::create($pbg5);
							// LEVEL 6
							$cm6 = DB::select('SELECT username,sponsor FROM members WHERE username=?', [$cm5[0]->sponsor]);
							if(count($cm6) > 0) {
								$cb6 = DB::update('UPDATE members SET saldo=saldo+? WHERE username=?', [$v10,$cm6[0]->username]);
								$pbg6 = array(
									'reff_id' => $ref_id,
									'username' => $cm6[0]->username,
									'level' => 6,
									'amount' => $v10
								);
								T_bonusgenerasi::create($pbg6);
								// LEVEL 7
								$cm7 = DB::select('SELECT username,sponsor FROM members WHERE username=?', [$cm6[0]->sponsor]);
								if(count($cm7) > 0) {
									$cb7 = DB::update('UPDATE members SET saldo=saldo+? WHERE username=?', [$v5,$cm7[0]->username]);
									$pbg7 = array(
										'reff_id' => $ref_id,
										'username' => $cm7[0]->username,
										'level' => 7,
										'amount' => $v5
									);
									T_bonusgenerasi::create($pbg7);
									// LEVEL 8
									$cm8 = DB::select('SELECT username,sponsor FROM members WHERE username=?', [$cm7[0]->sponsor]);
									if(count($cm8) > 0) {
										$cb8 = DB::update('UPDATE members SET saldo=saldo+? WHERE username=?', [$v5,$cm8[0]->username]);
										$pbg8 = array(
											'reff_id' => $ref_id,
											'username' => $cm8[0]->username,
											'level' => 8,
											'amount' => $v10
										);
										T_bonusgenerasi::create($pbg8);
										// LEVEL 9
										$cm9 = DB::select('SELECT username,sponsor FROM members WHERE username=?', [$cm8[0]->sponsor]);
										if(count($cm9) > 0) {
											$cb9 = DB::update('UPDATE members SET saldo=saldo+? WHERE username=?', [$v5,$cm9[0]->username]);
											$pbg9 = array(
												'reff_id' => $ref_id,
												'username' => $cm9[0]->username,
												'level' => 9,
												'amount' => $v5
											);
											T_bonusgenerasi::create($pbg9);
											// LEVEL 10
											$cm10 = DB::select('SELECT username,sponsor FROM members WHERE username=?', [$cm9[0]->sponsor]);
											if(count($cm10) > 0) {
												$cb10 = DB::update('UPDATE members SET saldo=saldo+? WHERE username=?', [$v5,$cm10[0]->username]);
												$pbg10 = array(
													'reff_id' => $ref_id,
													'username' => $cm10[0]->username,
													'level' => 10,
													'amount' => $v5
												);
												T_bonusgenerasi::create($pbg10);
											}
										}
									}
								}
							}
						}
					}
				}
			}
		}
		$mp = $actualprice;
		$ns = $member[0]->saldo - $mp;
		$xusr = User::where('id', '=', $member[0]->id)->first();
		$xusr->update(array('saldo' => $ns));
		DB::table('t_ppob')->insert(
			[
				'members_id' => $members_id,
				'trx_id' => $ref_id,
				'trx_date' => Date('Y-m-d H:i:s'),
				'trx_name' => "PLN POSTPAID",
				'no_hp' => $req['hp'],
				'tagihan' => $lamount[$req['code']][1],
				'fee_admin' => $lamount[$req['code']][2],
				'total_tagihan' => $actualprice,
				'ending_saldo' => $ns,
				'product_code' => $code,
				'status' => "Berhasil",
				'status_bonus' => '0'
			]
		);
		return $data;
	}
	function plnPascaPay(Request $request) {
		$req = $request->all();
		$members_id = $req['member_id'];
		$password = $req['password'];
		$username   = "089687271843";
		$apiKey   = "7285d8726bcde318728";
		$tr_id  = $req['tr_id'];
		$ref_id = $req['ref_id'];
		$code = 'pln-postpaid';
		$price = $req['price'];
		$markup = 3000;
		$signature  = md5($username.$apiKey.$tr_id);
		$json = '{
				"commands"    : "pay-pasca",
				"username"    : "089687271843",
				"tr_id"          : "'.$tr_id.'",
				"sign"        : "'.md5($username.$apiKey.$tr_id).'"
				}';
		$url = "https://mobilepulsa.net/api/v1/bill/check";
		if($members_id == "" || $members_id == null) {
			return '{
				"data": {
					"trx_id": "",
					"saldo": "",
					"rc": "0",
					"desc": "Data members_id kosong, Hubungi Admin!",
					"bit11": "",
					"bit12": "",
					"bit48": "",
					"bit62": ""
				}
			}';
		}
		$member = DB::select('SELECT id, saldo, sponsor, username FROM members WHERE id='.$members_id.' AND password ="'.$password.'"');
		if(empty($member)){
			return '{
				"data": {
					"trx_id": "",
					"saldo": "",
					"rc": "0",
					"desc": "Data member tidak terdaftar, Hubungi Admin!",
					"bit11": "",
					"bit12": "",
					"bit48": "",
					"bit62": ""
				}
			}';
		}

		$actualprice = $price + $markup;

		if($member[0]->saldo < $actualprice) {
			$tp = array(
				'member_id' => $members_id,
				'log_id' => '0',
				'target' => $req['code'],
				'reff_id' => $ref_id,
				'prodname' => $req['hp'],
				'amount' => $actualprice,
				'status' => 'FAILED',
				'message' => 'Insufficient balance',
				'time' => date('Y-m-d H:i:s'),
				'payload' => json_encode($json)
			);
			T_transaction::create($tp);
			return '{
				"data": {
					"trx_id": "",
					"saldo": "",
					"rc": "0",
					"desc": "Saldo Tidak Cukup!",
					"bit11": "",
					"bit12": "",
					"bit48": "",
					"bit62": ""
				}
			}';
		}

		$ch  = curl_init();
		curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $json);
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

		$data = curl_exec($ch);
		curl_close($ch);
		$rd = json_decode($data);
		$tp = array(
			'member_id' => $members_id,
			'log_id' => '0',
			'target' => $req['hp'],
			'reff_id' => $ref_id,
			'prodname' => $code,
			'amount' => $actualprice,
			'status' => 'SUCCESS',
			'message' => 'Pembayaran PLN Pasca Bayar Berhasil',
			'time' => date('Y-m-d H:i:s'),
			'payload' => json_encode($json)
		);
		T_transaction::create($tp);

		$ha = $markup * (25/100);
		$vshare = $markup - $ha;
		$v10 = $vshare * (10/100);
		$v15 = $vshare * (15/100);
		$v5 = $vshare * (5/100);
		// CASHBACK
		$cb = DB::update('UPDATE members SET saldo=saldo+? WHERE id=?', [$v10,$members_id]);
		$pcb = array(
			'reff_id' => $ref_id,
			'username' => $member[0]->username,
			'level' => 0,
			'amount' => $v10
		);
		T_bonusgenerasi::create($pcb);
		// LEVEL 1
		$cm1 = DB::select('SELECT username,sponsor FROM members WHERE username=?', [$member[0]->sponsor]);
		if(count($cm1) > 0) {
			$cb1 = DB::update('UPDATE members SET saldo=saldo+? WHERE username=?', [$v15,$cm1[0]->username]);
			$pbg1 = array(
				'reff_id' => $ref_id,
				'username' => $cm1[0]->username,
				'level' => 1,
				'amount' => $v15
			);
			T_bonusgenerasi::create($pbg1);
			// LEVEL 2
			$cm2 = DB::select('SELECT username,sponsor FROM members WHERE username=?', [$cm1[0]->sponsor]);
			if(count($cm2) > 0) {
				$cb2 = DB::update('UPDATE members SET saldo=saldo+? WHERE username=?', [$v15,$cm2[0]->username]);
				$pbg2 = array(
					'reff_id' => $ref_id,
					'username' => $cm2[0]->username,
					'level' => 2,
					'amount' => $v15
				);
				T_bonusgenerasi::create($pbg2);
				// LEVEL 3
				$cm3 = DB::select('SELECT username,sponsor FROM members WHERE username=?', [$cm2[0]->sponsor]);
				if(count($cm3) > 0) {
					$cb3 = DB::update('UPDATE members SET saldo=saldo+? WHERE username=?', [$v10,$cm3[0]->username]);
					$pbg3 = array(
						'reff_id' => $ref_id,
						'username' => $cm3[0]->username,
						'level' => 3,
						'amount' => $v10
					);
					T_bonusgenerasi::create($pbg3);
					// LEVEL 4
					$cm4 = DB::select('SELECT username,sponsor FROM members WHERE username=?', [$cm3[0]->sponsor]);
					if(count($cm4) > 0) {
						$cb4 = DB::update('UPDATE members SET saldo=saldo+? WHERE username=?', [$v10,$cm4[0]->username]);
						$pbg4 = array(
							'reff_id' => $ref_id,
							'username' => $cm4[0]->username,
							'level' => 4,
							'amount' => $v10
						);
						T_bonusgenerasi::create($pbg4);
						// LEVEL 5
						$cm5 = DB::select('SELECT username,sponsor FROM members WHERE username=?', [$cm4[0]->sponsor]);
						if(count($cm5) > 0) {
							$cb5 = DB::update('UPDATE members SET saldo=saldo+? WHERE username=?', [$v10,$cm5[0]->username]);
							$pbg5 = array(
								'reff_id' => $ref_id,
								'username' => $cm5[0]->username,
								'level' => 5,
								'amount' => $v10
							);
							T_bonusgenerasi::create($pbg5);
							// LEVEL 6
							$cm6 = DB::select('SELECT username,sponsor FROM members WHERE username=?', [$cm5[0]->sponsor]);
							if(count($cm6) > 0) {
								$cb6 = DB::update('UPDATE members SET saldo=saldo+? WHERE username=?', [$v10,$cm6[0]->username]);
								$pbg6 = array(
									'reff_id' => $ref_id,
									'username' => $cm6[0]->username,
									'level' => 6,
									'amount' => $v10
								);
								T_bonusgenerasi::create($pbg6);
								// LEVEL 7
								$cm7 = DB::select('SELECT username,sponsor FROM members WHERE username=?', [$cm6[0]->sponsor]);
								if(count($cm7) > 0) {
									$cb7 = DB::update('UPDATE members SET saldo=saldo+? WHERE username=?', [$v5,$cm7[0]->username]);
									$pbg7 = array(
										'reff_id' => $ref_id,
										'username' => $cm7[0]->username,
										'level' => 7,
										'amount' => $v5
									);
									T_bonusgenerasi::create($pbg7);
									// LEVEL 8
									$cm8 = DB::select('SELECT username,sponsor FROM members WHERE username=?', [$cm7[0]->sponsor]);
									if(count($cm8) > 0) {
										$cb8 = DB::update('UPDATE members SET saldo=saldo+? WHERE username=?', [$v5,$cm8[0]->username]);
										$pbg8 = array(
											'reff_id' => $ref_id,
											'username' => $cm8[0]->username,
											'level' => 8,
											'amount' => $v10
										);
										T_bonusgenerasi::create($pbg8);
										// LEVEL 9
										$cm9 = DB::select('SELECT username,sponsor FROM members WHERE username=?', [$cm8[0]->sponsor]);
										if(count($cm9) > 0) {
											$cb9 = DB::update('UPDATE members SET saldo=saldo+? WHERE username=?', [$v5,$cm9[0]->username]);
											$pbg9 = array(
												'reff_id' => $ref_id,
												'username' => $cm9[0]->username,
												'level' => 9,
												'amount' => $v5
											);
											T_bonusgenerasi::create($pbg9);
											// LEVEL 10
											$cm10 = DB::select('SELECT username,sponsor FROM members WHERE username=?', [$cm9[0]->sponsor]);
											if(count($cm10) > 0) {
												$cb10 = DB::update('UPDATE members SET saldo=saldo+? WHERE username=?', [$v5,$cm10[0]->username]);
												$pbg10 = array(
													'reff_id' => $ref_id,
													'username' => $cm10[0]->username,
													'level' => 10,
													'amount' => $v5
												);
												T_bonusgenerasi::create($pbg10);
											}
										}
									}
								}
							}
						}
					}
				}
			}
		}
		$mp = $actualprice;
		$ns = $member[0]->saldo - $mp;
		$xusr = User::where('id', '=', $member[0]->id)->first();
		$xusr->update(array('saldo' => $ns));
		DB::table('t_ppob')->insert(
			[
				'members_id' => $members_id,
				'trx_id' => $ref_id,
				'trx_date' => Date('Y-m-d H:i:s'),
				'trx_name' => "PLN POSTPAID",
				'no_hp' => $req['hp'],
				'tagihan' => $price,
				'fee_admin' => $markup,
				'total_tagihan' => $actualprice,
				'ending_saldo' => $ns,
				'product_code' => $code,
				'status' => "Berhasil",
				'status_bonus' => '0'
			]
		);
		return $data;
	}
	function plnPraInqu(Request $request) {
		$req = $request->all();
		$members_id = $req['member_id'];
		$password = $req['password'];
		$username   = "089687271843";
		$apiKey   = "7285d8726bcde318728";
		$ref_id  = uniqid('');
		$markup = 0;
		$code = $req['code'];
		$signature  = md5($username.$apiKey.$ref_id);
		$json = '{
			"commands"    : "inquiry_pln",
			"username"    : "089687271843",
			"hp"          : "'.$req['hp'].'",
			"sign"        : "'.md5($username.$apiKey.$ref_id).'"
			}';
		// $url = "https://testprepaid.mobilepulsa.net/v1/legacy/index";
		// $url = "https://api.mobilepulsa.net/v1/legacy/index";
		// $ch  = curl_init();
		// curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
		// curl_setopt($ch, CURLOPT_URL, $url);
		// curl_setopt($ch, CURLOPT_POSTFIELDS, $json);
		// curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
		// curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

		// $data = curl_exec($ch);
		// curl_close($ch);
		// $rd = json_decode($data);
		// return $data;
		if($members_id == "" || $members_id == null) {
			return '{
				"data": {
					"trx_id": "",
					"saldo": "",
					"rc": "0",
					"desc": "Data members_id kosong, Hubungi Admin!",
					"bit11": "",
					"bit12": "",
					"bit48": "",
					"bit62": ""
				}
			}';
		}
		$member = DB::select('SELECT id, saldo FROM members WHERE id='.$members_id.' AND password ="'.$password.'"');
		if(empty($member)){
			return '{
				"data": {
					"trx_id": "",
					"saldo": "",
					"rc": "0",
					"desc": "Data member tidak terdaftar, Hubungi Admin!",
					"bit11": "",
					"bit12": "",
					"bit48": "",
					"bit62": ""
				}
			}';
		}
		$lamount = array(
			"hpln20000" =>	array(20000, 20500, 2500),
			"hpln50000" =>  array(50000,50500, 2500),
			"hpln100000" => array(100000, 100500, 2500),
			"hpln200000" => array(200000, 200500, 2500),
			"hpln500000" => array(500000, 500500, 2500),
			"hpln1000000" => array(100000, 1000500, 2500),
		);

		$actualprice = $lamount[$req['code']][1] + $lamount[$req['code']][2];

		if($member[0]->saldo < $actualprice) {
			$tp = array(
				'member_id' => $members_id,
				'log_id' => '0',
				'target' => $req['code'],
				'reff_id' => $ref_id,
				'prodname' => $req['hp'],
				'amount' => $actualprice,
				'status' => 'FAILED',
				'message' => 'Insufficient balance',
				'time' => date('Y-m-d H:i:s'),
				'payload' => json_encode($json)
			);
			// T_transaction::create($tp);
			return '{
				"data": {
					"trx_id": "",
					"saldo": "",
					"rc": "0",
					"desc": "Saldo Tidak Cukup!",
					"bit11": "",
					"bit12": "",
					"bit48": "",
					"bit62": ""
				}
			}';
		}
		$tp = array(
			'member_id' => $members_id,
			'log_id' => '0',
			'target' => $req['hp'],
			'reff_id' => $ref_id,
			'prodname' => $code,
			'amount' => $actualprice,
			'status' => 'SUCCESS',
			'message' => 'Inquiry Pulsa Berhasil',
			'time' => date('Y-m-d H:i:s'),
			'payload' => json_encode($json)
		);
		// T_transaction::create($tp);
		$data = '{"data":{
			"ref_id":"'.$ref_id.'",
			"status":1,
			"code":"'.$code.'",
			"hp":"'.$req['hp'].'",
			"pulsa": "'.$lamount[$req['code']][0].'",
			"price": '.$actualprice.',
			"message":"INQUIRY",
			"balance":0,
			"tr_id":"'.$ref_id.'",
			"rc":"39"
		}}';
		return $data;
	}
	function plnPraPay(Request $request) {
		$req = $request->all();
		$members_id = $req['member_id'];
		$password = $req['password'];
		$username   = "089687271843";
		$apiKey   = "7285d8726bcde318728";
		$ref_id  = uniqid('');
		$code = $req['code'];
		$signature  = md5($username.$apiKey.$ref_id);
		$json = '{
				"commands"    : "topup",
				"username"    : "089687271843",
				"ref_id"      : "'.$ref_id.'",
				"hp"          : "'.$req['hp'].'",
				"pulsa_code"  : "'.$code.'",
				"sign"        : "'.md5($username.$apiKey.$ref_id).'"
				}';
		// $url = "https://testprepaid.mobilepulsa.net/v1/legacy/index";
		$url = "https://api.mobilepulsa.net/v1/legacy/index";
		if($members_id == "" || $members_id == null) {
			return '{
				"data": {
					"trx_id": "",
					"saldo": "",
					"rc": "0",
					"desc": "Data members_id kosong, Hubungi Admin!",
					"bit11": "",
					"bit12": "",
					"bit48": "",
					"bit62": ""
				}
			}';
		}
		$member = DB::select('SELECT id, saldo, sponsor, username FROM members WHERE id='.$members_id.' AND password ="'.$password.'"');
		if(empty($member)){
			return '{
				"data": {
					"trx_id": "",
					"saldo": "",
					"rc": "0",
					"desc": "Data member tidak terdaftar, Hubungi Admin!",
					"bit11": "",
					"bit12": "",
					"bit48": "",
					"bit62": ""
				}
			}';
		}
		$lamount = array(
			"hpln20000" =>	array(20000, 20500, 2500),
			"hpln50000" =>  array(50000,50500, 2500),
			"hpln100000" => array(100000, 100500, 2500),
			"hpln200000" => array(200000, 200500, 2500),
			"hpln500000" => array(500000, 500500, 2500),
			"hpln1000000" => array(100000, 1000500, 2500),
		);

		$actualprice = $lamount[$req['code']][1] + $lamount[$req['code']][2];

		if($member[0]->saldo < $actualprice) {
			$tp = array(
				'member_id' => $members_id,
				'log_id' => '0',
				'target' => $req['code'],
				'reff_id' => $ref_id,
				'prodname' => $req['hp'],
				'amount' => $actualprice,
				'status' => 'FAILED',
				'message' => 'Insufficient balance',
				'time' => date('Y-m-d H:i:s'),
				'payload' => json_encode($json)
			);
			T_transaction::create($tp);
			return '{
				"data": {
					"trx_id": "",
					"saldo": "",
					"rc": "0",
					"desc": "Saldo Tidak Cukup!",
					"bit11": "",
					"bit12": "",
					"bit48": "",
					"bit62": ""
				}
			}';
		}

		$ch  = curl_init();
		curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $json);
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

		$data = curl_exec($ch);
		curl_close($ch);
		$rd = json_decode($data);
		$tp = array(
			'member_id' => $members_id,
			'log_id' => '0',
			'target' => $req['hp'],
			'reff_id' => $ref_id,
			'prodname' => $code,
			'amount' => $actualprice,
			'status' => 'SUCCESS',
			'message' => 'Pembayaran PLN Berhasil',
			'time' => date('Y-m-d H:i:s'),
			'payload' => json_encode($json)
		);
		T_transaction::create($tp);

		$ha = $lamount[$req['code']][2] * (25/100);
		$vshare = $lamount[$req['code']][2] - $ha;
		$v10 = $vshare * (10/100);
		$v15 = $vshare * (15/100);
		$v5 = $vshare * (5/100);
		// CASHBACK
		$cb = DB::update('UPDATE members SET saldo=saldo+? WHERE id=?', [$v10,$members_id]);
		$pcb = array(
			'reff_id' => $ref_id,
			'username' => $member[0]->username,
			'level' => 0,
			'amount' => $v10
		);
		T_bonusgenerasi::create($pcb);
		// LEVEL 1
		$cm1 = DB::select('SELECT username,sponsor FROM members WHERE username=?', [$member[0]->sponsor]);
		if(count($cm1) > 0) {
			$cb1 = DB::update('UPDATE members SET saldo=saldo+? WHERE username=?', [$v15,$cm1[0]->username]);
			$pbg1 = array(
				'reff_id' => $ref_id,
				'username' => $cm1[0]->username,
				'level' => 1,
				'amount' => $v15
			);
			T_bonusgenerasi::create($pbg1);
			// LEVEL 2
			$cm2 = DB::select('SELECT username,sponsor FROM members WHERE username=?', [$cm1[0]->sponsor]);
			if(count($cm2) > 0) {
				$cb2 = DB::update('UPDATE members SET saldo=saldo+? WHERE username=?', [$v15,$cm2[0]->username]);
				$pbg2 = array(
					'reff_id' => $ref_id,
					'username' => $cm2[0]->username,
					'level' => 2,
					'amount' => $v15
				);
				T_bonusgenerasi::create($pbg2);
				// LEVEL 3
				$cm3 = DB::select('SELECT username,sponsor FROM members WHERE username=?', [$cm2[0]->sponsor]);
				if(count($cm3) > 0) {
					$cb3 = DB::update('UPDATE members SET saldo=saldo+? WHERE username=?', [$v10,$cm3[0]->username]);
					$pbg3 = array(
						'reff_id' => $ref_id,
						'username' => $cm3[0]->username,
						'level' => 3,
						'amount' => $v10
					);
					T_bonusgenerasi::create($pbg3);
					// LEVEL 4
					$cm4 = DB::select('SELECT username,sponsor FROM members WHERE username=?', [$cm3[0]->sponsor]);
					if(count($cm4) > 0) {
						$cb4 = DB::update('UPDATE members SET saldo=saldo+? WHERE username=?', [$v10,$cm4[0]->username]);
						$pbg4 = array(
							'reff_id' => $ref_id,
							'username' => $cm4[0]->username,
							'level' => 4,
							'amount' => $v10
						);
						T_bonusgenerasi::create($pbg4);
						// LEVEL 5
						$cm5 = DB::select('SELECT username,sponsor FROM members WHERE username=?', [$cm4[0]->sponsor]);
						if(count($cm5) > 0) {
							$cb5 = DB::update('UPDATE members SET saldo=saldo+? WHERE username=?', [$v10,$cm5[0]->username]);
							$pbg5 = array(
								'reff_id' => $ref_id,
								'username' => $cm5[0]->username,
								'level' => 5,
								'amount' => $v10
							);
							T_bonusgenerasi::create($pbg5);
							// LEVEL 6
							$cm6 = DB::select('SELECT username,sponsor FROM members WHERE username=?', [$cm5[0]->sponsor]);
							if(count($cm6) > 0) {
								$cb6 = DB::update('UPDATE members SET saldo=saldo+? WHERE username=?', [$v10,$cm6[0]->username]);
								$pbg6 = array(
									'reff_id' => $ref_id,
									'username' => $cm6[0]->username,
									'level' => 6,
									'amount' => $v10
								);
								T_bonusgenerasi::create($pbg6);
								// LEVEL 7
								$cm7 = DB::select('SELECT username,sponsor FROM members WHERE username=?', [$cm6[0]->sponsor]);
								if(count($cm7) > 0) {
									$cb7 = DB::update('UPDATE members SET saldo=saldo+? WHERE username=?', [$v5,$cm7[0]->username]);
									$pbg7 = array(
										'reff_id' => $ref_id,
										'username' => $cm7[0]->username,
										'level' => 7,
										'amount' => $v5
									);
									T_bonusgenerasi::create($pbg7);
									// LEVEL 8
									$cm8 = DB::select('SELECT username,sponsor FROM members WHERE username=?', [$cm7[0]->sponsor]);
									if(count($cm8) > 0) {
										$cb8 = DB::update('UPDATE members SET saldo=saldo+? WHERE username=?', [$v5,$cm8[0]->username]);
										$pbg8 = array(
											'reff_id' => $ref_id,
											'username' => $cm8[0]->username,
											'level' => 8,
											'amount' => $v10
										);
										T_bonusgenerasi::create($pbg8);
										// LEVEL 9
										$cm9 = DB::select('SELECT username,sponsor FROM members WHERE username=?', [$cm8[0]->sponsor]);
										if(count($cm9) > 0) {
											$cb9 = DB::update('UPDATE members SET saldo=saldo+? WHERE username=?', [$v5,$cm9[0]->username]);
											$pbg9 = array(
												'reff_id' => $ref_id,
												'username' => $cm9[0]->username,
												'level' => 9,
												'amount' => $v5
											);
											T_bonusgenerasi::create($pbg9);
											// LEVEL 10
											$cm10 = DB::select('SELECT username,sponsor FROM members WHERE username=?', [$cm9[0]->sponsor]);
											if(count($cm10) > 0) {
												$cb10 = DB::update('UPDATE members SET saldo=saldo+? WHERE username=?', [$v5,$cm10[0]->username]);
												$pbg10 = array(
													'reff_id' => $ref_id,
													'username' => $cm10[0]->username,
													'level' => 10,
													'amount' => $v5
												);
												T_bonusgenerasi::create($pbg10);
											}
										}
									}
								}
							}
						}
					}
				}
			}
		}
		$mp = $actualprice;
		$ns = $member[0]->saldo - $mp;
		$xusr = User::where('id', '=', $member[0]->id)->first();
		$xusr->update(array('saldo' => $ns));
		DB::table('t_ppob')->insert(
			[
				'members_id' => $members_id,
				'trx_id' => $ref_id,
				'trx_date' => Date('Y-m-d H:i:s'),
				'trx_name' => "PLN PRABAYAR",
				'no_hp' => $req['hp'],
				'tagihan' => $lamount[$req['code']][1],
				'fee_admin' => $lamount[$req['code']][2],
				'total_tagihan' => $actualprice,
				'ending_saldo' => $ns,
				'product_code' => $code,
				'status' => "Berhasil",
				'status_bonus' => '0'
			]
		);
		return $data;
	}
	function tollInqu(Request $request) {
		$req = $request->all();
		$members_id = $req['member_id'];
		$password = $req['password'];
		$username   = "089687271843";
		$apiKey   = "6845d79e9afc378c";
		$ref_id  = uniqid('');
		$code = $req['code'];
		$signature  = md5($username.$apiKey.$ref_id);
		$json = '{
				"commands"    : "topup",
				"username"    : "089687271843",
				"ref_id"      : "'.$ref_id.'",
				"hp"          : "'.$req['hp'].'",
				"pulsa_code"  : "'.$code.'",
				"sign"        : "'.md5($username.$apiKey.$ref_id).'"
				}';
		$url = "https://testprepaid.mobilepulsa.net/v1/legacy/index";
		if($members_id == "" || $members_id == null) {
			return '{
				"data": {
					"trx_id": "",
					"saldo": "",
					"rc": "0",
					"desc": "Data members_id kosong, Hubungi Admin!",
					"bit11": "",
					"bit12": "",
					"bit48": "",
					"bit62": ""
				}
			}';
		}
		$member = DB::select('SELECT id, saldo FROM members WHERE id='.$members_id.' AND password ="'.$password.'"');
		if(empty($member)){
			return '{
				"data": {
					"trx_id": "",
					"saldo": "",
					"rc": "0",
					"desc": "Data member tidak terdaftar, Hubungi Admin!",
					"bit11": "",
					"bit12": "",
					"bit48": "",
					"bit62": ""
				}
			}';
		}
		$lamount = array(
			"etoll100000" =>	array(100000, 102000, 1000),
			"etoll200000" =>  array(200000, 202000, 1000)
		);
		$actualprice = $lamount[$req['code']][1] + 1000;

		if($member[0]->saldo < $actualprice) {
			$tp = array(
				'member_id' => $members_id,
				'log_id' => '0',
				'target' => $req['code'],
				'reff_id' => $ref_id,
				'prodname' => $req['hp'],
				'amount' => $actualprice,
				'status' => 'FAILED',
				'message' => 'Insufficient balance',
				'time' => date('Y-m-d H:i:s'),
				'payload' => json_encode($json)
			);
			T_transaction::create($tp);
			return '{
				"data": {
					"trx_id": "",
					"saldo": "",
					"rc": "0",
					"desc": "Saldo Tidak Cukup!",
					"bit11": "",
					"bit12": "",
					"bit48": "",
					"bit62": ""
				}
			}';
		}
		$tp = array(
			'member_id' => $members_id,
			'log_id' => '0',
			'target' => $req['hp'],
			'reff_id' => $ref_id,
			'prodname' => $code,
			'amount' => $actualprice,
			'status' => 'SUCCESS',
			'message' => 'Inquiry E-TOLL Berhasil',
			'time' => date('Y-m-d H:i:s'),
			'payload' => json_encode($json)
		);
		T_transaction::create($tp);
		$data = '{"data":{
			"ref_id":"'.$ref_id.'",
			"status":1,
			"code":"'.$code.'",
			"hp":"'.$req['hp'].'",
			"pulsa": "'.$lamount[$req['code']][0].'",
			"price": '.$actualprice.',
			"message":"INQUIRY",
			"balance":0,
			"tr_id":"'.$ref_id.'",
			"rc":"0001"
		}}';
		return $data;
	}
	function tollPay(Request $request) {
		$req = $request->all();
		$members_id = $req['member_id'];
		$password = $req['password'];
		$username   = "089687271843";
		$apiKey   = "7285d8726bcde318728";
		$ref_id  = uniqid('');
		$code = $req['code'];
		$signature  = md5($username.$apiKey.$ref_id);
		$json = '{
				"commands"    : "topup",
				"username"    : "089687271843",
				"ref_id"      : "'.$ref_id.'",
				"hp"          : "'.$req['hp'].'",
				"pulsa_code"  : "'.$code.'",
				"sign"        : "'.md5($username.$apiKey.$ref_id).'"
				}';
		$url = "https://api.mobilepulsa.net/v1/legacy/index";
		if($members_id == "" || $members_id == null) {
			return '{
				"data": {
					"trx_id": "",
					"saldo": "",
					"rc": "0",
					"desc": "Data members_id kosong, Hubungi Admin!",
					"bit11": "",
					"bit12": "",
					"bit48": "",
					"bit62": ""
				}
			}';
		}
		$member = DB::select('SELECT id, saldo, sponsor, username FROM members WHERE id='.$members_id.' AND password ="'.$password.'"');
		if(empty($member)){
			return '{
				"data": {
					"trx_id": "",
					"saldo": "",
					"rc": "0",
					"desc": "Data member tidak terdaftar, Hubungi Admin!",
					"bit11": "",
					"bit12": "",
					"bit48": "",
					"bit62": ""
				}
			}';
		}
		$lamount = array(
			"etoll100000" =>	array(100000, 102000, 1000),
			"etoll200000" =>  array(200000, 202000, 1000)
		);

		$actualprice = $lamount[$req['code']][1] + $lamount[$req['code']][2];

		if($member[0]->saldo < $actualprice) {
			$tp = array(
				'member_id' => $members_id,
				'log_id' => '0',
				'target' => $req['code'],
				'reff_id' => $ref_id,
				'prodname' => $req['hp'],
				'amount' => $actualprice,
				'status' => 'FAILED',
				'message' => 'Insufficient balance',
				'time' => date('Y-m-d H:i:s'),
				'payload' => json_encode($json)
			);
			T_transaction::create($tp);
			return '{
				"data": {
					"trx_id": "",
					"saldo": "",
					"rc": "0",
					"desc": "Saldo Tidak Cukup!",
					"bit11": "",
					"bit12": "",
					"bit48": "",
					"bit62": ""
				}
			}';
		}

		$ch  = curl_init();
		curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $json);
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

		$data = curl_exec($ch);
		curl_close($ch);
		$rd = json_decode($data);
		$tp = array(
			'member_id' => $members_id,
			'log_id' => '0',
			'target' => $req['hp'],
			'reff_id' => $ref_id,
			'prodname' => $code,
			'amount' => $actualprice,
			'status' => 'SUCCESS',
			'message' => 'Pembayaran E-TOLL Berhasil',
			'time' => date('Y-m-d H:i:s'),
			'payload' => json_encode($json)
		);
		T_transaction::create($tp);

		$ha = $lamount[$req['code']][2] * (25/100);
		$vshare = $lamount[$req['code']][2] - $ha;
		$v10 = $vshare * (10/100);
		$v15 = $vshare * (15/100);
		$v5 = $vshare * (5/100);
		// CASHBACK
		$cb = DB::update('UPDATE members SET saldo=saldo+? WHERE id=?', [$v10,$members_id]);
		$pcb = array(
			'reff_id' => $ref_id,
			'username' => $member[0]->username,
			'level' => 0,
			'amount' => $v10
		);
		T_bonusgenerasi::create($pcb);
		// LEVEL 1
		$cm1 = DB::select('SELECT username,sponsor FROM members WHERE username=?', [$member[0]->sponsor]);
		if(count($cm1) > 0) {
			$cb1 = DB::update('UPDATE members SET saldo=saldo+? WHERE username=?', [$v15,$cm1[0]->username]);
			$pbg1 = array(
				'reff_id' => $ref_id,
				'username' => $cm1[0]->username,
				'level' => 1,
				'amount' => $v15
			);
			T_bonusgenerasi::create($pbg1);
			// LEVEL 2
			$cm2 = DB::select('SELECT username,sponsor FROM members WHERE username=?', [$cm1[0]->sponsor]);
			if(count($cm2) > 0) {
				$cb2 = DB::update('UPDATE members SET saldo=saldo+? WHERE username=?', [$v15,$cm2[0]->username]);
				$pbg2 = array(
					'reff_id' => $ref_id,
					'username' => $cm2[0]->username,
					'level' => 2,
					'amount' => $v15
				);
				T_bonusgenerasi::create($pbg2);
				// LEVEL 3
				$cm3 = DB::select('SELECT username,sponsor FROM members WHERE username=?', [$cm2[0]->sponsor]);
				if(count($cm3) > 0) {
					$cb3 = DB::update('UPDATE members SET saldo=saldo+? WHERE username=?', [$v10,$cm3[0]->username]);
					$pbg3 = array(
						'reff_id' => $ref_id,
						'username' => $cm3[0]->username,
						'level' => 3,
						'amount' => $v10
					);
					T_bonusgenerasi::create($pbg3);
					// LEVEL 4
					$cm4 = DB::select('SELECT username,sponsor FROM members WHERE username=?', [$cm3[0]->sponsor]);
					if(count($cm4) > 0) {
						$cb4 = DB::update('UPDATE members SET saldo=saldo+? WHERE username=?', [$v10,$cm4[0]->username]);
						$pbg4 = array(
							'reff_id' => $ref_id,
							'username' => $cm4[0]->username,
							'level' => 4,
							'amount' => $v10
						);
						T_bonusgenerasi::create($pbg4);
						// LEVEL 5
						$cm5 = DB::select('SELECT username,sponsor FROM members WHERE username=?', [$cm4[0]->sponsor]);
						if(count($cm5) > 0) {
							$cb5 = DB::update('UPDATE members SET saldo=saldo+? WHERE username=?', [$v10,$cm5[0]->username]);
							$pbg5 = array(
								'reff_id' => $ref_id,
								'username' => $cm5[0]->username,
								'level' => 5,
								'amount' => $v10
							);
							T_bonusgenerasi::create($pbg5);
							// LEVEL 6
							$cm6 = DB::select('SELECT username,sponsor FROM members WHERE username=?', [$cm5[0]->sponsor]);
							if(count($cm6) > 0) {
								$cb6 = DB::update('UPDATE members SET saldo=saldo+? WHERE username=?', [$v10,$cm6[0]->username]);
								$pbg6 = array(
									'reff_id' => $ref_id,
									'username' => $cm6[0]->username,
									'level' => 6,
									'amount' => $v10
								);
								T_bonusgenerasi::create($pbg6);
								// LEVEL 7
								$cm7 = DB::select('SELECT username,sponsor FROM members WHERE username=?', [$cm6[0]->sponsor]);
								if(count($cm7) > 0) {
									$cb7 = DB::update('UPDATE members SET saldo=saldo+? WHERE username=?', [$v5,$cm7[0]->username]);
									$pbg7 = array(
										'reff_id' => $ref_id,
										'username' => $cm7[0]->username,
										'level' => 7,
										'amount' => $v5
									);
									T_bonusgenerasi::create($pbg7);
									// LEVEL 8
									$cm8 = DB::select('SELECT username,sponsor FROM members WHERE username=?', [$cm7[0]->sponsor]);
									if(count($cm8) > 0) {
										$cb8 = DB::update('UPDATE members SET saldo=saldo+? WHERE username=?', [$v5,$cm8[0]->username]);
										$pbg8 = array(
											'reff_id' => $ref_id,
											'username' => $cm8[0]->username,
											'level' => 8,
											'amount' => $v10
										);
										T_bonusgenerasi::create($pbg8);
										// LEVEL 9
										$cm9 = DB::select('SELECT username,sponsor FROM members WHERE username=?', [$cm8[0]->sponsor]);
										if(count($cm9) > 0) {
											$cb9 = DB::update('UPDATE members SET saldo=saldo+? WHERE username=?', [$v5,$cm9[0]->username]);
											$pbg9 = array(
												'reff_id' => $ref_id,
												'username' => $cm9[0]->username,
												'level' => 9,
												'amount' => $v5
											);
											T_bonusgenerasi::create($pbg9);
											// LEVEL 10
											$cm10 = DB::select('SELECT username,sponsor FROM members WHERE username=?', [$cm9[0]->sponsor]);
											if(count($cm10) > 0) {
												$cb10 = DB::update('UPDATE members SET saldo=saldo+? WHERE username=?', [$v5,$cm10[0]->username]);
												$pbg10 = array(
													'reff_id' => $ref_id,
													'username' => $cm10[0]->username,
													'level' => 10,
													'amount' => $v5
												);
												T_bonusgenerasi::create($pbg10);
											}
										}
									}
								}
							}
						}
					}
				}
			}
		}
		$mp = $actualprice;
		$ns = $member[0]->saldo - $mp;
		$xusr = User::where('id', '=', $member[0]->id)->first();
		$xusr->update(array('saldo' => $ns));
		DB::table('t_ppob')->insert(
			[
				'members_id' => $members_id,
				'trx_id' => $ref_id,
				'trx_date' => Date('Y-m-d H:i:s'),
				'trx_name' => "E-TOLL",
				'no_hp' => $req['hp'],
				'tagihan' => $lamount[$req['code']][1],
				'fee_admin' => $lamount[$req['code']][2],
				'total_tagihan' => $actualprice,
				'ending_saldo' => $ns,
				'product_code' => $code,
				'status' => "Berhasil",
				'status_bonus' => '0'
			]
		);
		return $data;
	}

	function testbonus(Request $request) {
		$req = $request->all();
		$members_id = $req['id'];
		$ref_id = 0;
		$ha = 1000 * (25/100);
		$vshare = 1000 - $ha;
		$v10 = $vshare * (10/100);
		$v15 = $vshare * (15/100);
		$v5 = $vshare * (5/100);
		// CASHBACK
		$member = DB::select('SELECT username,sponsor FROM members WHERE id=?', [$members_id]);
		$cb = DB::update('UPDATE members SET saldo=saldo+? WHERE id=?', [$v10,$members_id]);
		$pcb = array(
			'reff_id' => $ref_id,
			'username' => $member[0]->username,
			'level' => 0,
			'amount' => $v10
		);
		T_bonusgenerasi::create($pcb);
		// LEVEL 1
		$cm1 = DB::select('SELECT username,sponsor FROM members WHERE username=?', [$member[0]->sponsor]);
		if(count($cm1) > 0) {
			$cb1 = DB::update('UPDATE members SET saldo=saldo+? WHERE username=?', [$v15,$cm1[0]->username]);
			$pbg1 = array(
				'reff_id' => $ref_id,
				'username' => $cm1[0]->username,
				'level' => 1,
				'amount' => $v15
			);
			T_bonusgenerasi::create($pbg1);
			// LEVEL 2
			$cm2 = DB::select('SELECT username,sponsor FROM members WHERE username=?', [$cm1[0]->sponsor]);
			if(count($cm2) > 0) {
				$cb2 = DB::update('UPDATE members SET saldo=saldo+? WHERE username=?', [$v15,$cm2[0]->username]);
				$pbg2 = array(
					'reff_id' => $ref_id,
					'username' => $cm2[0]->username,
					'level' => 2,
					'amount' => $v15
				);
				T_bonusgenerasi::create($pbg2);
				// LEVEL 3
				$cm3 = DB::select('SELECT username,sponsor FROM members WHERE username=?', [$cm2[0]->sponsor]);
				if(count($cm3) > 0) {
					$cb3 = DB::update('UPDATE members SET saldo=saldo+? WHERE username=?', [$v10,$cm3[0]->username]);
					$pbg3 = array(
						'reff_id' => $ref_id,
						'username' => $cm3[0]->username,
						'level' => 3,
						'amount' => $v10
					);
					T_bonusgenerasi::create($pbg3);
					// LEVEL 4
					$cm4 = DB::select('SELECT username,sponsor FROM members WHERE username=?', [$cm3[0]->sponsor]);
					if(count($cm4) > 0) {
						$cb4 = DB::update('UPDATE members SET saldo=saldo+? WHERE username=?', [$v10,$cm4[0]->username]);
						$pbg4 = array(
							'reff_id' => $ref_id,
							'username' => $cm4[0]->username,
							'level' => 4,
							'amount' => $v10
						);
						T_bonusgenerasi::create($pbg4);
						// LEVEL 5
						$cm5 = DB::select('SELECT username,sponsor FROM members WHERE username=?', [$cm4[0]->sponsor]);
						if(count($cm5) > 0) {
							$cb5 = DB::update('UPDATE members SET saldo=saldo+? WHERE username=?', [$v10,$cm5[0]->username]);
							$pbg5 = array(
								'reff_id' => $ref_id,
								'username' => $cm5[0]->username,
								'level' => 5,
								'amount' => $v10
							);
							T_bonusgenerasi::create($pbg5);
							// LEVEL 6
							$cm6 = DB::select('SELECT username,sponsor FROM members WHERE username=?', [$cm5[0]->sponsor]);
							if(count($cm6) > 0) {
								$cb6 = DB::update('UPDATE members SET saldo=saldo+? WHERE username=?', [$v10,$cm6[0]->username]);
								$pbg6 = array(
									'reff_id' => $ref_id,
									'username' => $cm6[0]->username,
									'level' => 6,
									'amount' => $v10
								);
								T_bonusgenerasi::create($pbg6);
								// LEVEL 7
								$cm7 = DB::select('SELECT username,sponsor FROM members WHERE username=?', [$cm6[0]->sponsor]);
								if(count($cm7) > 0) {
									$cb7 = DB::update('UPDATE members SET saldo=saldo+? WHERE username=?', [$v5,$cm7[0]->username]);
									$pbg7 = array(
										'reff_id' => $ref_id,
										'username' => $cm7[0]->username,
										'level' => 7,
										'amount' => $v5
									);
									T_bonusgenerasi::create($pbg7);
									// LEVEL 8
									$cm8 = DB::select('SELECT username,sponsor FROM members WHERE username=?', [$cm7[0]->sponsor]);
									if(count($cm8) > 0) {
										$cb8 = DB::update('UPDATE members SET saldo=saldo+? WHERE username=?', [$v5,$cm8[0]->username]);
										$pbg8 = array(
											'reff_id' => $ref_id,
											'username' => $cm8[0]->username,
											'level' => 8,
											'amount' => $v10
										);
										T_bonusgenerasi::create($pbg8);
										// LEVEL 9
										$cm9 = DB::select('SELECT username,sponsor FROM members WHERE username=?', [$cm8[0]->sponsor]);
										if(count($cm9) > 0) {
											$cb9 = DB::update('UPDATE members SET saldo=saldo+? WHERE username=?', [$v5,$cm9[0]->username]);
											$pbg9 = array(
												'reff_id' => $ref_id,
												'username' => $cm9[0]->username,
												'level' => 9,
												'amount' => $v5
											);
											T_bonusgenerasi::create($pbg9);
											// LEVEL 10
											$cm10 = DB::select('SELECT username,sponsor FROM members WHERE username=?', [$cm9[0]->sponsor]);
											if(count($cm10) > 0) {
												$cb10 = DB::update('UPDATE members SET saldo=saldo+? WHERE username=?', [$v5,$cm10[0]->username]);
												$pbg10 = array(
													'reff_id' => $ref_id,
													'username' => $cm10[0]->username,
													'level' => 10,
													'amount' => $v5
												);
												T_bonusgenerasi::create($pbg10);
											}
										}
									}
								}
							}
						}
					}
				}
			}
		}
		return "ok";
	}
}