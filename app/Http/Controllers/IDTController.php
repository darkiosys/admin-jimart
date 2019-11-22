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
		$url = "http://36.79.180.2:62455/edc/devel/sim_mlm/?EDC=LOGIN.SP1911199562.4de480f38b17e801fc9e22a0e44af051.EDC.18%23%2A217%23%2A205%23%2A148";
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
	
	function ListRoute(Request $request) {
		$req = $request->all();
		$url = "http://36.79.180.2:62455/edc/devel/sim_mlm/?EDC=TIKET.AIRLINES.EDC.18%23%2A217%23%2A205%23%2A148.".$req['pin'].".".$req['session'];
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
	function CheckFlight(Request $request) {
		$req = $request->all();
		$url = "http://36.79.180.2:62455/edc/devel/sim_mlm/?EDC=TIKET.AIRLINES.SCHEDULE.EDC.18%23%2A217%23%2A205%23%2A148.".$req['pin'].".".$req['session']."&FROM=".$req['from']."&TO=".$req['to']."&DATE=".$req['date'];
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
	function CheckPrice(Request $request) {
		$req = $request->all();
		$url = "http://36.79.180.2:62455/edc/devel/sim_mlm/?EDC=TIKET.AIRLINES.CHECK.EDC.18%23%2A217%23%2A205%23%2A148.".$req['pin'].".".$req['session']."&FROM=".$req['from']."&TO=".$req['to']."&DATE=".$req['date']."&FLIGHT=".$req['flight']."&ADULT=".$req['adult']."&CHILD=".$req['child']."&INFANT=".$req['infant'];
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
	function BookingTicket(Request $request) {
		$req = $request->all();
		$url = "http://36.79.180.2:62455/edc/devel/sim_mlm/?EDC=TIKET.AIRLINES.BOOKING.EDC.18%23%2A217%23%2A205%23%2A148.".$req['pin'].".".$req['session']."&FROM=".$req['from']."&TO=".$req['to']."&DATE=".$req['date']."&FLIGHT=".$req['flight']."&ADULT=".$req['adult']."&CHILD=".$req['child']."&INFANT=".$req['infant']."&EMAIL=".$req['email']."&PHONE=".$req['phone']."&PASSANGERNAME=".$req['passangername']."&DATEOFBIRTH=".$req['dateofbirth']."&BAGGAGEVOLUME=".$req['baggagevolume']."&PASSPORTNUMBER=".$req['passportnumber']."&PASSPORTEXPIRED=".$req['passportexpired'];
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
	function IssuedTicket(Request $request) {
		$req = $request->all();
		$url = "http://36.79.180.2:62455/edc/devel/sim_mlm/?EDC=TIKET.AIRLINES.ISSUED.".$req['codebooking'].".EDC.18%23%2A217%23%2A205%23%2A148.".$req['pin'].".".$req['session'];
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