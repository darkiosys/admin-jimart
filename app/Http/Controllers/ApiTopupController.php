<?php

namespace App\Http\Controllers;
use App\Saldo;
use App\User;

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
	function inquiryPasca(Request $request) {
		$req = $request->all();
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