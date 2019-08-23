<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

Auth::routes();

Route::get('/home', 'HomeController@index')->name('home');
Route::get('api/v1/login', 'ApiUserController@login');
Route::get('/api/v1/slider', 'ApiUserController@slider');
Route::get('/api/v1/profile', 'ApiTransaksiController@getProfile');

Route::get('api/forgot_password', 'ApiUserController@forgotPassword');

Route::post('api/saldo/bonus', 'ApiTopupController@topupBonus');

Route::resource('saldo', 'SaldoController');
Route::get('transaksi', 'TransaksiController@getTransaksi');
Route::post('api/saldo/topup', 'ApiTopupController@topupSaldo');
Route::post('api/buy_product', 'ApiUserController@buyProduct');
Route::post('api/cart/kurir', 'ApiUserController@addCartKurir');
Route::post('/saldo/verifikasi/{id}', 'SaldoController@verifikasiTopup');
Route::post('/transaksi/verifikasi/{id}', 'TransaksiController@verifikasiTransaksi');
Route::post('api/transaksi', 'ApiTransaksiController@createTransaksi');
Route::post('api/transaksi/kurir', 'ApiUserController@addKurirTransaksi');

Route::get('api/transaksi', 'ApiTransaksiController@getTransaksi');

// User
Route::get('/api/totaltransaksi/konfirmasipembayaran', 'ApiTransaksiController@totalKonfirmasiPembayaran');
Route::get('/api/totaltransaksi/pesanandiproses', 'ApiTransaksiController@totalPesananDiProses');
Route::get('/api/totaltransaksi/pesanandikirim', 'ApiTransaksiController@totalPesananDikirim');
Route::get('/api/totaltransaksi/pesananterkirim', 'ApiTransaksiController@totalPesananTerkirim');

// Store
Route::get('/api/totaltransaksi/menunggupembayaran', 'ApiTransaksiController@totalMenungguPembayaran');
Route::get('/api/totaltransaksi/sudahdibayar', 'ApiTransaksiController@totalSudahDibayar');
Route::get('/api/totaltransaksi/pesananstoredikirim', 'ApiTransaksiController@totalPesanansDikirim');

Route::get('/api/transaksi/sudahdibayar', 'ApiTransaksiController@invoiceSudahDibayar');
Route::get('/api/transaksi/detailsudahdibayar', 'ApiTransaksiController@detailInvoiceSudahDibayar');

Route::get('/api/v1/api-ppob', function () {
    
    ini_set('max_execution_time', 27);
    set_time_limit(27);
    ini_set("default_socket_timeout", 27);
    
    ini_set('error_reporting', E_ALL);
    ini_set('display_errors', '2');

    define("PEL_USER", "HTH19010016");
    define("PEL_PASS", "moQsu5");
    define("PEL_KEY", "k1vl7s6vrxo02vy");
    define("PEL_URL", "https://202.152.60.62:1112/transactions/trx.json");
    
    // define("PEL_URL", "https://202.152.60.61:8118/Transactions/trx.json");
    //Link Production : https://202.152.60.62:1112/users/login
    
    // define("PEL_USER", "plg12110004");
    // define("PEL_PASS", "aldwin.3");
    // define("PEL_KEY", "1234");
    // define("PEL_URL", "https://202.152.60.61:8118/Transactions/trx.json");
    
    $members_id = Request::get('members_id');
    $password = Request::get('password');
    
    $trx_date   = date("YmdHis");
    $trx_id     = $members_id.$trx_date; 
    $trx_type   = Request::get('trx_type');
    
    $product_id = Request::get('pro_code');
    $account_no = Request::get('pel_id'); 
    $no_hp      = Request::get('no_hp');
    $nomination = Request::get('nomination');
    $periode    = Request::get('periode');
    $ppob_slug  = Request::get('ppob_slug');
    $signature = md5(PEL_USER.PEL_PASS.$product_id.$trx_date.PEL_KEY);
    $post_data = array(
        'trx_date' => $trx_date,
        'trx_type' => $trx_type,
        'trx_id' => $trx_id,
        'cust_msisdn' => $no_hp,
        'cust_account_no' => $account_no,
        'product_id' => $product_id,
        'product_nomination' => $nomination,
        'periode_payment' => $periode
    );
    $post_data = http_build_query($post_data, '', '&');
    
    if($product_id == ""||$product_id == null)
    {
        return '{
            "data": {
                "trx": {
                    "trx_id": "",
                    "saldo": "",
                    "rc": "717",
                    "desc": "Data product_id kosong, Hubungi Admin!",
                    "bit11": "",
                    "bit12": "",
                    "bit48": "",
                    "bit62": ""
                }
            }
        }';
    }
    else if($members_id == ""||$members_id == null)
    {
        return '{
            "data": {
                "trx": {
                    "trx_id": "",
                    "saldo": "",
                    "rc": "717",
                    "desc": "Data members_id kosong, Hubungi Admin!",
                    "bit11": "",
                    "bit12": "",
                    "bit48": "",
                    "bit62": ""
                }
            }
        }';
    }
    else if($trx_type == ""||$trx_type == null)
    {
        return '{
            "data": {
                "trx": {
                    "trx_id": "",
                    "saldo": "",
                    "rc": "717",
                    "desc": "Data trx_type kosong, Hubungi Admin!",
                    "bit11": "",
                    "bit12": "",
                    "bit48": "",
                    "bit62": ""
                }
            }
        }';
    }
    else
    {
        $member = DB::select('SELECT id FROM members WHERE id='.$members_id.' AND password ="'.$password.'"');
        if(empty($member)){
            return '{
                "data": {
                    "trx": {
                        "trx_id": "",
                        "saldo": "",
                        "rc": "717",
                        "desc": "Data member tidak terdaftar, Hubungi Admin!",
                        "bit11": "",
                        "bit12": "",
                        "bit48": "",
                        "bit62": ""
                    }
                }
            }';
        }
        
        if($trx_type == "2200"&&($ppob_slug==""||$ppob_slug==null)){
             return '{
                "data": {
                    "trx": {
                        "trx_id": "",
                        "saldo": "",
                        "rc": "717",
                        "desc": "Data trx_type kosong, Hubungi Admin!",
                        "bit11": "",
                        "bit12": "",
                        "bit48": "",
                        "bit62": ""
                    }
                }
            }';
        }
        
        try{
            

            $curl = curl_init(PEL_URL);
            curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
            curl_setopt($curl, CURLOPT_VERBOSE, true);
            curl_setopt($curl, CURLOPT_POST, true);
            curl_setopt($curl, CURLOPT_URL, PEL_URL);
            curl_setopt($curl, CURLOPT_HEADER, 0); //enable or disable using 0/1
            curl_setopt($curl, CURLOPT_HTTPHEADER, array(
                'Authorization: '.'PELANGIREST username='.PEL_USER.'&password='.PEL_PASS.'&signature='.$signature
            ));
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($curl, CURLOPT_POSTFIELDS, $post_data);
            
            $response = curl_exec($curl);
            $info = curl_getinfo($curl);
            curl_close($curl);
            // echo strlen($info);
            // printf(json_encode($info));
            // printf(json_encode($response));
            
            // var_dump($response);
            
            
            
            if($trx_type == "2200"){
                $array = json_decode($response);
                $data   = $array->data->trx;
                $rc   = $array->data->trx->rc;
            
                if($rc=="0000"||$rc=="00"||$rc=="0001"||$rc=="01"){
                    
                        $data_admin = DB::table('ppob_fee_admin')->select('fee_admin','name') ->where('ppob_slug', '=', $ppob_slug)->get();
                        $fee_admin = (int) $data_admin[0]->fee_admin;
                        $trx_name = $data_admin[0]->name;
                        $nominal  = (int) $nomination;
                    
                    try{
                        
                        if($ppob_slug == "pln-postpaid"){
                            $fee_admin = $fee_admin * ((int) $array->data->trx->bill_status);
                        }
                        
                        if($ppob_slug != "pulsa"&&$ppob_slug != "pln-prepaid"){
                        if($ppob_slug == "telkom"){
                                $nominal  = (int) $array->data->trx->jumlah_bayar;
                            }
                            else{
                                $nominal  = (int) $array->data->trx->amount;
                            }
                        }

                        $total_tagihan = $nominal + $fee_admin;  
                        $affected = DB::update('UPDATE members SET saldo=saldo-? WHERE id=?', [$total_tagihan,$members_id]);

                         $msg = '{
                                "data": {
                                    "trx": {
                                        "trx_id": "",
                                        "saldo": "",
                                        "rc": "717",
                                        "desc": "Update Saldo Gagal, untuk credit = '.$total_tagihan.'",
                                        "bit11": "",
                                        "bit12": "",
                                        "bit48": "",
                                        "bit62": ""
                                    }
                                }
                            }';
                        
                        if( $affected==1){
                            $member = DB::select('SELECT sponsor,username,id,saldo FROM members WHERE id=?', [$members_id]);
                            $saldoMember = (int) $member[0]->saldo;
                            DB::table('t_ppob')->insert(
                                [
                                    'members_id' => $members_id, 'trx_id' => $trx_id, 'trx_date' => $trx_date,'trx_name' => $trx_name, 'no_hp' => $no_hp, 'no_pel' => $account_no, 'tagihan' => $nominal, 
                                    'fee_admin' => $fee_admin,'total_tagihan' => $total_tagihan,'ending_saldo' => $saldoMember, 'product_code' => $product_id, 'periode_payment' => $periode,
                                    'status' => "Berhasil", 'data_json' => $response, 'status_bonus' => '0'
                                ]
                            );
                            $ha = $fee_admin * (25/100);
                            $vshare = $fee_admin - $ha;
                            $v10 = $vshare * (10/100);
                            $v15 = $vshare * (15/100);
                            $v5 = $vshare * (5/100);
                            // CASHBACK
                            $cb = DB::update('UPDATE members SET saldo=saldo+? WHERE id=?', [$v10,$members_id]);
                            // LEVEL 1
                            $cm1 = DB::select('SELECT username,sponsor FROM members WHERE username=?', [$member[0]->sponsor]);
                            if(count($cm1) > 0) {
                                $cb1 = DB::update('UPDATE members SET saldo=saldo+? WHERE username=?', [$v15,$cm1[0]->username]);
                                // LEVEL 2
                                $cm2 = DB::select('SELECT username,sponsor FROM members WHERE username=?', [$cm1[0]->sponsor]);
                                if(count($cm2) > 0) {
                                    $cb2 = DB::update('UPDATE members SET saldo=saldo+? WHERE username=?', [$v15,$cm2[0]->username]);
                                    // LEVEL 3
                                    $cm3 = DB::select('SELECT username,sponsor FROM members WHERE username=?', [$cm2[0]->sponsor]);
                                    if(count($cm3) > 0) {
                                        $cb3 = DB::update('UPDATE members SET saldo=saldo+? WHERE username=?', [$v10,$cm3[0]->username]);
                                        // LEVEL 4
                                        $cm4 = DB::select('SELECT username,sponsor FROM members WHERE username=?', [$cm3[0]->sponsor]);
                                        if(count($cm4) > 0) {
                                            $cb4 = DB::update('UPDATE members SET saldo=saldo+? WHERE username=?', [$v10,$cm4[0]->username]);
                                            // LEVEL 5
                                            $cm5 = DB::select('SELECT username,sponsor FROM members WHERE username=?', [$cm4[0]->sponsor]);
                                            if(count($cm5) > 0) {
                                                $cb5 = DB::update('UPDATE members SET saldo=saldo+? WHERE username=?', [$v10,$cm5[0]->username]);
                                                // LEVEL 6
                                                $cm6 = DB::select('SELECT username,sponsor FROM members WHERE username=?', [$cm5[0]->sponsor]);
                                                if(count($cm6) > 0) {
                                                    $cb6 = DB::update('UPDATE members SET saldo=saldo+? WHERE username=?', [$v10,$cm6[0]->username]);
                                                    // LEVEL 7
                                                    $cm7 = DB::select('SELECT username,sponsor FROM members WHERE username=?', [$cm6[0]->sponsor]);
                                                    if(count($cm7) > 0) {
                                                        $cb7 = DB::update('UPDATE members SET saldo=saldo+? WHERE username=?', [$v5,$cm7[0]->username]);
                                                        // LEVEL 8
                                                        $cm8 = DB::select('SELECT username,sponsor FROM members WHERE username=?', [$cm7[0]->sponsor]);
                                                        if(count($cm8) > 0) {
                                                            $cb8 = DB::update('UPDATE members SET saldo=saldo+? WHERE username=?', [$v5,$cm8[0]->username]);
                                                            // LEVEL 9
                                                            $cm9 = DB::select('SELECT username,sponsor FROM members WHERE username=?', [$cm8[0]->sponsor]);
                                                            if(count($cm9) > 0) {
                                                                $cb9 = DB::update('UPDATE members SET saldo=saldo+? WHERE username=?', [$v5,$cm9[0]->username]);
                                                                // LEVEL 10
                                                                $cm10 = DB::select('SELECT username,sponsor FROM members WHERE username=?', [$cm9[0]->sponsor]);
                                                                if(count($cm10) > 0) {
                                                                    $cb10 = DB::update('UPDATE members SET saldo=saldo+? WHERE username=?', [$v5,$cm10[0]->username]);
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
                            return $response;
                        }
                        else{ return $msg; }
                    }
                    catch(Exception $e){
                        return '{
                                "data": {
                                    "trx": {
                                        "trx_id": "",
                                        "saldo": "",
                                        "rc": {
                                            "0": "717"
                                        },
                                        "desc": "Error = '.$e.'",
                                        "bit11": "",
                                        "bit12": "",
                                        "bit48": "",
                                        "bit62": ""
                                    }
                                }
                            }';
                    }
                }
                else{
                    return $response;
                }
            }
            else
            {
                // return json_encode($response);
                return $response;
            }
        } catch(TimeoutException $e) {
            return '{
                    "data": {
                        "trx": {
                            "trx_id": "",
                            "saldo": "",
                            "rc": "717",
                            "desc": "Error Time Out = '.$e.'",
                            "bit11": "",
                            "bit12": "",
                            "bit48": "",
                            "bit62": ""
                        }
                    }
                }';
            
        } catch(Exception $e){
            return '{
                    "data": {
                        "trx": {
                            "trx_id": "",
                            "saldo": "",
                            "rc": "717",
                            "desc": "Error = '.$e.'",
                            "bit11": "",
                            "bit12": "",
                            "bit48": "",
                            "bit62": ""
                        }
                    }
                }';
        }
    } 
});