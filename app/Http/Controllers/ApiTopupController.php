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
			"htelkomsel1000" => 1000,
			"htelkomsel2000" => 2000,
			"htelkomsel3000" => 3000, 
			"htelkomsel5000" => 5000,
			"htelkomsel10000" => 10000,
			"htelkomsel20000" => 20000,
			"htelkomsel25000" => 25000,
			"htelkomsel40000" => 40000,
			"htelkomsel50000" => 50000,
			"htelkomsel100000" => 100000,
			"htelkomsel150000" => 150000,
			"htelkomsel200000" => 200000,
			"htelkomsel300000" => 300000,
			"htelkomsel500000" => 500000,
			"htelkomsel1000000" => 1000000,
			"hindosat5000" => 5000,
			"hindosat10000" => 10000,
			"hindosat12000" => 12000,
			"hindosat20000" => 20000,
			"hindosat25000" => 25000,
			"hindosat30000" => 30000,
			"hindosat50000" => 50000,
			"hindosat60000" => 60000,
			"hindosat80000" => 80000,
			"hindosat100000" => 100000,
			"hindosat150000" => 150000,
			"hindosat200000" => 200000,
			"hindosat250000" => 250000,
			"hindosat500000" => 500000,
			"hindosat1000000" => 1000000,
			"xld5000" => 5000,
			"xld10000" => 10000,
			"xld15000" => 15000,
			"xld25000" => 25000,
			"xld30000" => 30000,
			"xld50000" => 50000,
			"xld100000" => 100000,
			"xld150000" => 150000,
			"xld200000" => 200000,
			"xld300000" => 300000,
			"xld500000" => 500000,
			"xld1000000" => 1000000,
			"haxis5000" => 5000,
			"haxis10000" => 10000,
			"haxis15000" => 15000,
			"haxis25000" => 25000,
			"haxis50000" => 50000,
			"haxis100000" => 100000,
			"haxis200000" => 200000,
			"hthree1000"  => 1000,
			"hthree2000"  => 2000,
			"hthree3000"  => 3000,
			"hthree5000"  => 5000,
			"hthree10000"  => 10000,
			"hthree15000"  => 15000,
			"hthree20000"  => 20000,
			"hthree25000"  => 25000,
			"hthree30000"  => 30000,
			"hthree50000"  => 50000,
			"hthree100000"  => 100000,
			"hthree150000"  => 150000,
			"hthree200000"  => 200000,
			"hthree300000"  => 300000,
			"hthree500000"  => 500000,
			"hthree1000000"  => 1000000,
			"hsmart5000" => 5000,
			"hsmart10000" => 10000,
			"hsmart20000" => 20000,
			"hsmart25000" => 25000,
			"hsmart50000" => 50000,
			"hsmart60000" => 60000,
			"hsmart100000" => 100000,
			"hsmart150000" => 150000,
			"hsmart200000" => 200000,
			"hsmart300000" => 300000,
			"hsmart500000" => 500000,
			"hsmart1000000" => 1000000,
			"hceria50000" => 50000,
			"hceria100000" => 100000,
			"hceria200000" => 200000,
		);

		if($member[0]->saldo < $lamount[$req['code']]) {
			$tp = array(
				'member_id' => $members_id,
				'log_id' => '0',
				'target' => $req['code'],
				'reff_id' => $ref_id,
				'prodname' => $req['hp'],
				'amount' => 0,
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
		$tp = array(
			'member_id' => $members_id,
			'log_id' => '0',
			'target' => $req['hp'],
			'reff_id' => $ref_id,
			'prodname' => $code,
			'amount' => 0,
			'status' => 'SUCCESS',
			'message' => 'Pembelian Pulsa Berhasil',
			'time' => date('Y-m-d H:i:s'),
			'payload' => json_encode($json)
		);
		T_transaction::create($tp);
		return $data->price;
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