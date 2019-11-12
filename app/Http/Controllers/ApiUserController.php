<?php

namespace App\Http\Controllers;

use Mail;
use Illuminate\Support\Facades\Hash;
use App\User;
use App\OldUser;
use App\TdEcommerce;
use App\TEcommerce;
use App\TransaksiDetail;
use App\TransaksiKurir;
use App\Slider;
use DB;
use JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;

use Illuminate\Http\Request;

class ApiUserController extends Controller
{
	function newupload(Request $request) {
		$this->validate($request, [
			'image' => 'mimes:jpeg,png,bmp,tiff |max:4096',
		],
			$messages = [
				'required' => 'The :attribute field is required.',
				'mimes' => 'Only jpeg, png, bmp,tiff are allowed.'
			]
		);
		$user = User::where('id', '=', $request->query('id'))
		->where('password', '=', $request->query('password'))
		->where('username', '=', $request->query('username'))
		->first();
		$file = $request->file('image');
		if($user) {
			$tujuan_upload = 'userprofile';
			$file->move($tujuan_upload,$file->getClientOriginalName());
			return $tujuan_upload.'/'.$file->getClientOriginalName();
		} else {
			return 'uploads-mobile'.'/'.$file->getClientOriginalName();
		}
	}
	function slider(Request $request)
	{
		// return '{"api_status":1,"api_message":"success","api_authorization":"You are in debug mode !","data":[{"id_slider":3,"no_urut":1,"judul_slider":"Abcd","link":"http:\/\/jmart.co.id\/img\/header4.jpg","foto":"320180208025346","foto_type":"http:\/\/jimart.store\/admin\/public\/.jpg","foto_size":167,"created":"2017-11-25 08:05:03","created_by":"","modified":"2019-08-23 06:46:53","modified_by":"azmicolejr"},{"id_slider":3,"no_urut":1,"judul_slider":"Abcd","link":"http:\/\/jmart.co.id\/img\/header3.jpg","foto":"320180208025346","foto_type":"http:\/\/jimart.store\/admin\/public\/.jpg","foto_size":167,"created":"2017-11-25 08:05:03","created_by":"","modified":"2019-08-23 06:46:53","modified_by":"azmicolejr"},{"id_slider":2,"no_urut":2,"judul_slider":"Hat for Men","link":"http:\/\/jmart.co.id\/img\/header2.jpg","foto":"220180208025208","foto_type":"http:\/\/jimart.store\/admin\/public\/.jpg","foto_size":833,"created":"2017-11-25 08:05:03","created_by":"","modified":"2019-08-23 06:46:43","modified_by":"mazmi"},{"id_slider":1,"no_urut":3,"judul_slider":"Eye Liner","link":"http:\/\/jmart.co.id\/img\/header1.jpg","foto":"120171204051126","foto_type":"http:\/\/jimart.store\/admin\/public\/.jpg","foto_size":203,"created":"2017-11-25 08:05:03","created_by":"","modified":"2019-08-23 06:46:49","modified_by":"mazmi"}]}';
		$ret = array(
			"api_status" => 1,
			"api_message" => "success",
			"data" => Slider::get()
		);
		return $ret;
	}

	function getmember(Request $request) {
		$req = $request->all();
		$user = User::where('id', '=', $req['id'])
		->where('password', '=', $req['password'])
		->where('username', '=', $req['username'])
		->first();
		$user['api_status'] = 1;
		$user['api_message'] = "success";
		$a = $user['photo'];

		if (strpos($a, 'https://jmart.co.id/') !== false) {
			$user['photo'] = $user['photo'];
		} else {
			$user['photo'] = 'https://jmart.co.id/'.$user['photo'];
		}
		return $user;
	}

	function shareHistory(Request $request) {
		$req = $request->all();
		$keyword = $request->get('search');
        $perPage = 10;
        $sharehistory = DB::table('t_bonusgeneration')
            ->select('*')->where('username', '=', $req['username'])->orderBy('created_at', 'desc')->paginate($perPage);
		return $sharehistory;
	}

	function getProfile(Request $request) {
		$req = $request->all();
		return array("data" => array(User::where('id', '=', $req['id'])->first()));
	}
	
	function createpassword(Request $request) {
		$req = $request->all();
		return Hash::make($req['pwd']);
	}

	function createmember(Request $request) {
		$req = $request->all();
		if($req['key'] == 'starp321%#^GRyhEAW5') {
			$memberPayload = array(
				"nik" => $req['nik'],
				"first_name" => $req['first_name'],
				"last_name" => $req['last_name'],
				"sponsor" => $req['sponsor'],
				"username" => $req['username'],
				"password" => Hash::make($req['password']),
				"email" => $req['email'],
				"phone" => $req['phone'],
				"tgl_lahir" => $req['tgl_lahir'],
				"jenis_kelamin" => $req['jenis_kelamin'],
			);
			User::create($memberPayload);
		}
		return array(
			"status" => 1,
			"message" => "create member success"
		);
	}

	function transppob(Request $request) {
		$req = $request->all();
		$a = $req['members_id'];
		$b = $req['username'];
		$c = $req['password'];
		$transppob = DB::select('SELECT * FROM t_ppob WHERE members_id = '.$a.' ORDER BY trx_date DESC');
		$ret = array(
			"api_status" => 1,
			"api_message" => "success",
			"data" => $transppob
		);
		return $ret;
	}

	function transsaldo(Request $request) {
		$req = $request->all();
		$a = $req['members_id'];
		$b = $req['username'];
		$c = $req['password'];
		$transsaldo = DB::select('SELECT * FROM t_transfer_saldo WHERE members_id = '.$a);
		$ret = array(
			"api_status" => 1,
			"api_message" => "success",
			"data" => $transsaldo
		);
		return $ret;
	}

	function getcategories(Request $request) {
		$req = $request->all();
		$categories = DB::select('SELECT * FROM product_categories ORDER BY created_at desc');
		for ($i=0; $i < count($categories); $i++) { 
			$categories[$i]->image_url = 'https://jmart.co.id/'.$categories[$i]->image_url;
		}
		$ret = array(
			"api_status" => 1,
			"api_message" => "success",
			"data" => $categories
		);
		return $ret;
	}

	function getproducts(Request $request) {
		$req = $request->all();
		$products = DB::select('SELECT DISTINCT products.id, products.members_id, products.product_name, products.product_slug, products.keywords, products.description, products.weight, products.rating_avg, products.product_categories_id, products.product_category_sub_id, products.product_category_supersub_id, products.price, products.discount, products.price_discount, products.stock, product_categories.category_name as category, product_category_sub.category_sub_name as category_sub, product_category_supersub.category_supersub_name as category_supersub, members.store_name, members.store_image, members.store_address as store_city FROM products LEFT JOIN product_categories ON products.product_categories_id = product_categories.id LEFT JOIN product_category_sub ON products.product_category_sub_id = product_category_sub.id LEFT JOIN product_category_supersub ON products.product_category_supersub_id = product_category_supersub.id LEFT JOIN members ON products.members_id = members.id LIMIT 10');
		for ($i=0; $i < count($products); $i++) { 
			$img = DB::select('SELECT image_url FROM product_images where products_id='.$products[$i]->id.' ORDER BY created_at desc limit 1');
			$products[$i]->image_url = 'https://jmart.co.id/'.$img[0]->image_url;
		}
		$ret = array(
			"api_status" => 1,
			"api_message" => "success",
			"data" => $products
		);
		return $ret;
	}

	function getproduct(Request $request) {
		$req = $request->all();
		$product = DB::select('SELECT DISTINCT(products.id), products.members_id, products.product_name, products.product_slug, products.keywords, products.description, products.weight, products.rating_avg, products.product_categories_id, products.product_category_sub_id, products.product_category_supersub_id, products.price, products.discount, products.price_discount, products.stock, product_categories.category_name as category, product_category_sub.category_sub_name as category_sub, product_category_supersub.category_supersub_name as category_supersub, members.store_name, product_images.image_url, members.store_image, members.store_address as store_city FROM products LEFT JOIN product_images ON products.id = product_images.products_id LEFT JOIN product_categories ON products.product_categories_id = product_categories.id LEFT JOIN product_category_sub ON products.product_category_sub_id = product_category_sub.id LEFT JOIN product_category_supersub ON products.product_category_supersub_id = product_category_supersub.id LEFT JOIN members ON products.members_id = members.id where products.id = '.$req['id']);
		$s = $product[0];
		$s->api_status = 1;
		$s->api_message = "success";
		return (array)$product[0];
	}

	function productimages(Request $request) {
		$req = $request->all();
		$pimage = DB::select('SELECT * FROM product_images WHERE products_id='.$req['products_id']);
		for ($i=0; $i < count($pimage); $i++) { 
			$pimage[$i]->image_url = 'https://jmart.co.id/'.$pimage[$i]->image_url;
		}
		$ret = array(
			"api_status" => 1,
			"api_message" => "success",
			"data" => $pimage
		);
		return $ret;
	}
	function product_whistlist(Request $request) {
		$ret = array(
			"api_status" => 1,
			"api_message" => "success",
			"data" => array()
		);
		return $ret;
	}

	function product_chart(Request $request) {
		$ret = array(
			"api_status" => 1,
			"api_message" => "success",
			"data" => array()
		);
		return $ret;
	}

	public function forgotPassword(Request $request)
	{
		$req = $request->all();
		$login = $req['login'];
		$user = User::where('username', '=', $login)->orWhere('email', '=', $login)->first();
		if ($user) {
			$rand = rand(10000, 99999);
			$username = array(
				"first_name" => $user->first_name,
				"last_name" => $user->last_name,
				"email" => $user->email,
				"newpass" => $rand
			);
			$user->update(array('password' => Hash::make($rand)));
			Mail::send('emails.reminder', $username, function ($m) use ($username) {
				$m->from('admin@starpreneur.co.id', 'Reset Password');
				$m->to($username['email'], 'Acep Hasanudin')->subject('New Password');
			});
			return array("message" => "success");
		} else {
			return array("message" => "failed");
		}
	}

	public function changePassword(Request $request)
	{
		$req = $request->all();
		$login = $req['username'];
		$user = User::where('username', '=', $login)->orWhere('email', '=', $login)->first();
		if ($user) {
			if ($user) {
				$res = Hash::check($req['oldpassword'], $user->password);
				if ($res) {
					$user->update(array('password' => Hash::make($req['newpassword'])));
					return 1;
				} else {
					return 0;
				}
			} else {
				return 0;
			}
		} else {
			return 0;
		}
	}

	public function changePasswordAdmin(Request $request)
	{
		$req = $request->all();
		$login = $req['id'];
		$user = User::where('id', '=', $login)->first();
		if ($user) {
			$user->update(array('password' => Hash::make($req['password'])));
			return redirect('/member-saldo');
		} else {
			return "failed";
		}
	}

	public function login(Request $request)
	{
		$requestData = $request->all();
		$failedLogin = array(
			"api_status" => 0,
			"api_message" => "Invalid credentials. Check your username and password."
		);
		$user = User::where('email', '=', $requestData['email'])->orWhere('username', '=', $requestData['email'])->first();

		if ($user) {
			$res = Hash::check($requestData['password'], $user->password);
			if ($res) {
				$token = md5(date('Y-m-d H:i:s'));
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
						"token",
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
					"token" => $token,
					"status" => $user->status
				);
				$ip = $request->ip();
				$user->update(array("token" => $token, "ip_address" => $ip));
				return $arr;
			} else {
				return $failedLogin;
			}
		} else {
			$user = OldUser::where('username', '=', $requestData['email'])->where('pass', '=', md5($requestData['password']))->first();
			if ($user) {
				$userPayload = array();
				if ($user->nama) {
					$userPayload['first_name'] = $user->nama;
					$userPayload['last_name'] = $user->nama;
				}
				if ($user->sponsor) {
					$userPayload['sponsor'] = $user->sponsor;
				}
				if ($user->username) {
					$userPayload['username'] = $user->username;
				}
				if ($requestData['password']) {
					$userPayload['password'] = Hash::make($requestData['password']);
				}
				if ($user->email) {
					$userPayload['email'] = $user->email;
				}
				if ($user->phone) {
					$userPayload['phone'] = $user->phone;
				}
				if ($user->tgl_lahir) {
					$userPayload['tgl_lahir'] = $user->tgl_lahir;
				}
				if ($user->jenis_kelamin) {
					$userPayload['jenis_kelamin'] = $user->jenis_kelamin;
				}
				if ($user->foto) {
					$userPayload['foto'] = $user->foto;
				}
				if ($user->saldo) {
					$userPayload['saldo'] = $user->saldo;
				}
				if ($user->points) {
					$userPayload['points'] = $user->points;
				}
				$userPayload['subdistrict_id'] = 307;
				if ($user->ip_address) {
					$userPayload['ip_address'] = $user->ip_address;
				}
				if ($user->last_login) {
					$userPayload['last_login'] = $user->last_login;
				}
				$userPayload['status'] = 0;
				if ($user->token) {
					$userPayload['token'] = $user->token;
				}
				if ($user->upline) {
					$userPayload['upline'] = $user->upline;
				}
				if ($user->posisi) {
					$userPayload['posisi'] = $user->posisi;
				}
				if ($user->kota) {
					$userPayload['kota'] = $user->kota;
				}
				if ($user->bank) {
					$userPayload['bank'] = $user->bank;
				}
				if ($user->norek) {
					$userPayload['norek'] = $user->norek;
				}
				if ($user->an) {
					$userPayload['an'] = $user->an;
				}
				if ($user->adminrp) {
					$userPayload['adminrp'] = $user->adminrp;
				}
				if ($user->tgl) {
					$userPayload['tgl'] = $user->tgl;
				}
				if ($user->tglaktif) {
					$userPayload['tglaktif'] = $user->tglaktif;
				}
				if ($user->paket) {
					$userPayload['paket'] = $user->paket;
				}
				if ($user->blokir) {
					$userPayload['blokir'] = $user->blokir;
				}
				if ($user->membership) {
					$userPayload['membership'] = $user->membership;
				}
				if ($user->fo) {
					$userPayload['fo'] = $user->fo;
				}
				if ($user->stocklist) {
					$userPayload['stocklist'] = $user->stocklist;
				}
				if ($user->reward1) {
					$userPayload['reward1'] = $user->reward1;
				}
				if ($user->reward2) {
					$userPayload['reward2'] = $user->reward2;
				}
				if ($user->reward3) {
					$userPayload['reward3'] = $user->reward3;
				}
				if ($user->reward4) {
					$userPayload['reward4'] = $user->reward4;
				}
				if ($user->reward5) {
					$userPayload['reward5'] = $user->reward5;
				}
				if ($user->reward6) {
					$userPayload['reward6'] = $user->reward6;
				}
				if ($user->jabatan) {
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

	function addKurirTransaksi(Request $request)
	{
		$req = $request->all();
		$kurirPayload = array(
			"store_id" => $req["store_id"],
			"transaksi_id" => $req["transaksi_id"],
			"kurir" => $req["kurir"],
			"waktu" => $req["waktu"],
			"biaya" => $req["biaya"]
		);
		$kurir = TransaksiKurir::create($kurirPayload);
		return "success";
	}

	function addCartKurir(Request $request)
	{
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
