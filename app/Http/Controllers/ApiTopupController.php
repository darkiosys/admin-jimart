<?php

namespace App\Http\Controllers;
use DB;
use App\Saldo;
use App\User;
use App\T_transaction;

use Illuminate\Http\Request;

class ApiTopupController extends Controller
{
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
	function callback(Request $request) {
		$data = file_get_contents('php://input');
		$my_file = 'callback.txt';
		$handle = fopen($my_file, 'w') or die('Cannot open file:  '.$my_file);
		fwrite($handle, $data);
		fclose($handle);
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
		$password = $req['password'];
		$username   = "089687271843";
		$apiKey   = "6845d79e9afc378c";
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
		$member = DB::select('SELECT id, saldo, sponsor FROM members WHERE id='.$members_id.' AND password ="'.$password.'"');
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
			"htelkomsel1000" => 1900,
			"htelkomsel2000" => 3200,
			"htelkomsel3000" => 4800, 
			"htelkomsel5000" => 5900,
			"htelkomsel10000" => 10850,
			"htelkomsel15000" => 15500,
			"htelkomsel20000" => 20500,
			"htelkomsel25000" => 25000,
			"htelkomsel40000" => 40000,
			"htelkomsel50000" => 49750,
			"htelkomsel100000" => 98500,
			"htelkomsel150000" => 148750,
			"htelkomsel200000" => 198000,
			"htelkomsel300000" => 297500,
			"htelkomsel500000" => 495000,
			"htelkomsel1000000" => 987500,
			"hindosat5000" => 5990,
			"hindosat10000" => 10990,
			"hindosat12000" => 12500,
			"hindosat20000" => 20200,
			"hindosat25000" => 24900,
			"hindosat30000" => 30550,
			"hindosat50000" => 49250,
			"hindosat60000" => 58800,
			"hindosat80000" => 78000,
			"hindosat100000" => 98000,
			"hindosat150000" => 143000,
			"hindosat200000" => 185500,
			"hindosat250000" => 232000,
			"hindosat500000" => 463000,
			"hindosat1000000" => 926000,
			"xld5000" => 5800,
			"xld10000" => 10800,
			"xld15000" => 15300,
			"xld25000" => 24900,
			"xld30000" => 29900,
			"xld50000" => 49700,
			"xld100000" => 99250,
			"xld150000" => 150000,
			"xld200000" => 198500,
			"xld300000" => 298500,
			"xld500000" => 495000,
			"xld1000000" => 990000,
			"haxis5000" => 5800,
			"haxis10000" => 10800,
			"haxis15000" => 14925,
			"haxis25000" => 24900,
			"haxis50000" => 49700,
			"haxis100000" => 99250,
			"haxis200000" => 198500,
			"hthree1000"  => 1300,
			"hthree2000"  => 2250,
			"hthree3000"  => 3450,
			"hthree5000"  => 5400,
			"hthree10000"  => 10400,
			"hthree15000"  => 15000,
			"hthree20000"  => 19700,
			"hthree25000"  => 24625,
			"hthree30000"  => 30000,
			"hthree50000"  => 49000,
			"hthree100000"  => 98500,
			"hthree150000"  => 148500,
			"hthree200000"  => 199000,
			"hthree300000"  => 297000,
			"hthree500000"  => 495000,
			"hthree1000000"  => 990000,
			"hsmart5000" => 5175,
			"hsmart10000" => 10100,
			"hsmart20000" => 19800,
			"hsmart25000" => 24800,
			"hsmart50000" => 49500,
			"hsmart60000" => 60000,
			"hsmart100000" => 97550,
			"hsmart150000" => 147000,
			"hsmart200000" => 196000,
			"hsmart300000" => 294000,
			"hsmart500000" => 490000,
			"hsmart1000000" => 980000,
			"hceria50000" => 50000,
			"hceria100000" => 100000,
			"hceria200000" => 200000,
		);

		$actualprice = $lamount[$req['code']] + 1000;

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
			'message' => 'Pembayaran Pulsa Berhasil',
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
		// LEVEL 1
		$cm1 = DB::select('SELECT username,sponsor FROM members WHERE username=?', [$member[0]->sponsor]);
		if(count($cm1) > 0) {
			$cb1 = DB::update('UPDATE members SET saldo=saldo+? WHERE username=?', [$v15,$cm1[0]->sponsor]);
			// LEVEL 2
			$cm2 = DB::select('SELECT username,sponsor FROM members WHERE username=?', [$cm1[0]->sponsor]);
			if(count($cm2) > 0) {
				$cb2 = DB::update('UPDATE members SET saldo=saldo+? WHERE username=?', [$v15,$cm2[0]->sponsor]);
				// LEVEL 3
				$cm3 = DB::select('SELECT username,sponsor FROM members WHERE username=?', [$cm2[0]->sponsor]);
				if(count($cm3) > 0) {
					$cb3 = DB::update('UPDATE members SET saldo=saldo+? WHERE username=?', [$v10,$cm3[0]->sponsor]);
					// LEVEL 4
					$cm4 = DB::select('SELECT username,sponsor FROM members WHERE username=?', [$cm3[0]->sponsor]);
					if(count($cm4) > 0) {
						$cb4 = DB::update('UPDATE members SET saldo=saldo+? WHERE username=?', [$v10,$cm4[0]->sponsor]);
						// LEVEL 5
						$cm5 = DB::select('SELECT username,sponsor FROM members WHERE username=?', [$cm4[0]->sponsor]);
						if(count($cm5) > 0) {
							$cb5 = DB::update('UPDATE members SET saldo=saldo+? WHERE username=?', [$v10,$cm5[0]->sponsor]);
							// LEVEL 6
							$cm6 = DB::select('SELECT username,sponsor FROM members WHERE username=?', [$cm5[0]->sponsor]);
							if(count($cm6) > 0) {
								$cb6 = DB::update('UPDATE members SET saldo=saldo+? WHERE username=?', [$v10,$cm6[0]->sponsor]);
								// LEVEL 7
								$cm7 = DB::select('SELECT username,sponsor FROM members WHERE username=?', [$cm6[0]->sponsor]);
								if(count($cm7) > 0) {
									$cb7 = DB::update('UPDATE members SET saldo=saldo+? WHERE username=?', [$v5,$cm7[0]->sponsor]);
									// LEVEL 8
									$cm8 = DB::select('SELECT username,sponsor FROM members WHERE username=?', [$cm7[0]->sponsor]);
									if(count($cm8) > 0) {
										$cb8 = DB::update('UPDATE members SET saldo=saldo+? WHERE username=?', [$v5,$cm8[0]->sponsor]);
										// LEVEL 9
										$cm9 = DB::select('SELECT username,sponsor FROM members WHERE username=?', [$cm8[0]->sponsor]);
										if(count($cm9) > 0) {
											$cb9 = DB::update('UPDATE members SET saldo=saldo+? WHERE username=?', [$v5,$cm9[0]->sponsor]);
											// LEVEL 10
											$cm10 = DB::select('SELECT username,sponsor FROM members WHERE username=?', [$cm9[0]->sponsor]);
											if(count($cm10) > 0) {
												$cb10 = DB::update('UPDATE members SET saldo=saldo+? WHERE username=?', [$v5,$cm10[0]->sponsor]);
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