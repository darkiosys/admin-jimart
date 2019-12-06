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
    return redirect('http://marketplace.jmart.co.id');
});

Route::get('/testbonus', 'ApiTopupController@testbonus');

// Indotama get key
Route::get('/api/v2/login', 'IDTController@Login');
// v2 Pesawat
Route::get('/api/v2/flight/listroute', 'IDTController@ListRoute');
Route::get('/api/v2/flight/checkflight', 'IDTController@CheckFlight');
Route::get('/api/v2/flight/checkprice', 'IDTController@CheckPrice');
Route::get('/api/v2/flight/bookingticket', 'IDTController@BookingTicket');
Route::get('/api/v2/flight/issuedticket', 'IDTController@IssuedTicket');


Auth::routes();

Route::get('/api/ppob_fee_admin', function() {
    return '{"api_status":1,"api_message":"success","api_response_fields":["id","name","ppob_slug","fee_admin"],"api_authorization":"You are in debug mode !","id":10,"name":"TRANSFER SALDO ","ppob_slug":"transfer-saldo","fee_admin":1000}';
});

Route::get('/api/v1/toll/inquiry', 'ApiTopupController@tollInqu');
Route::get('/api/v1/toll/pay', 'ApiTopupController@tollPay');
Route::get('/api/v1/checkpassword', 'ApiUserController@checkpassword');
Route::get('/admin/member/history_bonus', 'ApiUserController@shareHistory');

Route::get('/api/slider', 'ApiUserController@slider');
Route::get('/api/product', 'ApiUserController@getproduct');
Route::get('/api/product_images', 'ApiUserController@productimages');
Route::get('/api/product_wishlists', 'ApiUserController@product_whistlist');
Route::get('/api/product_carts', 'ApiUserController@product_chart');

Route::get('/api/user/changepassword', 'ApiUserController@changePassword');
Route::post('/api/user/register', 'ApiUserController@userregister');

Route::get('/api/v1/postpaid/inquiry', 'ApiTopupController@PostpaidInquiry');
Route::get('/api/v1/postpaid/pay', 'ApiTopupController@PostpaidPay');

Route::get('/api/v1/postpaid/inquiry/dev', 'ApiTopupController@PostpaidInquiryDev');
Route::get('/api/v1/postpaid/pay/dev', 'ApiTopupController@PostpaidPayDev');

Route::get('/api/v1/share/history', 'ApiUserController@shareHistory');

Route::get('/api/v1/bpjs/inquiry', 'ApiTopupController@bpjsInqu');
Route::get('/api/v1/bpjs/pay', 'ApiTopupController@bpjsPay');
Route::get('/api/v1/telkom/inquiry', 'ApiTopupController@telkomInqu');
Route::get('/api/v1/telkom/pay', 'ApiTopupController@telkomPay');
Route::get('/api/v1/esamsat/inquiry', 'ApiTopupController@esamsatInqu');
Route::get('/api/v1/esamsat/pay', 'ApiTopupController@esamsatPay');
Route::get('/api/v1/hpasca/inquiry', 'ApiTopupController@hpascaInqu');
Route::get('/api/v1/hpasca/pay', 'ApiTopupController@hpascaPay');
Route::get('/api/trans_ppob', 'ApiUserController@transppob');
Route::get('/api/transfer_saldo', 'ApiUserController@transsaldo');
Route::get('/api/categories', 'ApiUserController@getcategories');
Route::get('/api/products', 'ApiUserController@getproducts');
Route::post('/api/new-upload', 'ApiUserController@newupload');

Route::get('/api/v1/pln/pra/inquiry', 'ApiTopupController@plnPraInqu');
Route::get('/api/v1/pln/pra/pay', 'ApiTopupController@plnPraPay');

Route::get('/api/v1/topuptest', 'ApiTopupController@indextest');
Route::get('/api/v1/paytest', 'ApiTopupController@paytest');
Route::get('/api/v1/pln/pasca/pay', 'ApiTopupController@plnPascaPay');

Route::get('/api/v1/topup', 'ApiTopupController@index');
Route::get('/api/v1/inquiry', 'ApiTopupController@inquiryPasca');
Route::get('/api/v1/pln/postpaid/inquiry', 'ApiTopupController@plnPostpaidInquiry');
Route::get('/api/v1/pln/postpaid/pay', 'ApiTopupController@plnPostpaidPay');
Route::get('/api/v1/inquiry/pay', 'ApiTopupController@inquiryPay');
Route::post('/api/v1/prepaid/callback', 'ApiTopupController@callback');
Route::post('/api/v1/createmember', 'ApiUserController@createmember');

Route::get('/api/v1/pay', 'ApiTopupController@pay');

Route::get('/home', 'HomeController@index')->name('home');
Route::get('api/login', 'ApiUserController@login');
Route::get('api/v1/login', 'ApiUserController@login');
Route::get('/api/v1/profile', 'ApiUserController@getProfile');

Route::get('api/forgot_password', 'ApiUserController@forgotPassword');

Route::get('api/createpassword', 'ApiUserController@createpassword');

Route::post('api/saldo/bonus', 'ApiTopupController@topupBonus');

Route::post('/user/changepassword', 'ApiUserController@changePasswordAdmin');

Route::post('/user/jmart/import', 'ApiUserController@importjmart');

Route::resource('saldo', 'SaldoController');
Route::get('/transfer_saldo', 'SaldoController@transfer_saldo');
Route::post('/transfer_saldo', 'SaldoController@posttransfer_saldo');
Route::get('ppob', 'SaldoController@ppob');
Route::get('ppob/delete', 'SaldoController@ppobdelete');
Route::get('ppob/return', 'SaldoController@ppobreturn');
Route::get('banner', 'HomeController@banner');
Route::get('/member-saldo', 'SaldoController@memberSaldo');
Route::get('transaksi', 'TransaksiController@getTransaksi');
Route::get('transfer', 'TransaksiController@getTransfer');
Route::post('api/saldo/topup', 'ApiTopupController@topupSaldo');
Route::post('api/buy_product', 'ApiUserController@buyProduct');
Route::post('api/cart/kurir', 'ApiUserController@addCartKurir');
Route::post('/saldo/verifikasi/{id}', 'SaldoController@verifikasiTopup');
Route::post('/transaksi/verifikasi/{id}', 'TransaksiController@verifikasiTransaksi');
Route::post('api/transaksi', 'ApiTransaksiController@createTransaksi');
Route::post('api/transaksi/kurir', 'ApiUserController@addKurirTransaksi');

Route::get('api/transaksi', 'ApiTransaksiController@getTransaksi');

Route::get('/api/hapusmember/{id}', 'SaldoController@hapusMember');
Route::get('/api/hapussaldomember/{id}', 'SaldoController@kosongSaldo');

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


Route::get('/member-edit', function () {

    $members_id = Request::get('members_id');
    $username   = Request::get('username');
    $password   = Request::get('password');

    $first_name         = Request::get('first_name');
    $last_name          = Request::get('last_name');
    $photo              = Request::get('photo');
    $nik                = Request::get('nik');
    $email              = Request::get('email');
    $phone              = Request::get('phone');
    $tgl_lahir          = Request::get('tgl_lahir');
    $jenis_kelamin      = Request::get('jenis_kelamin');
    
    $fileName = null;
    if (Request::hasFile('file')) {
        $file = Request::file('file');
        $fileName = md5($file->getClientOriginalName() . time()) . "." . $file->getClientOriginalExtension();
        $file->move('./uploads/members/', $fileName);    
    }
    
    $member = DB::select('SELECT id FROM members WHERE id=? AND username=? AND password=?', [$members_id,$username,$password]);
    
    // return '{
    //         "api_message": "$first_name = '.$first_name.'\n$nik '.$nik.'\n$tgl_lahir = '.$tgl_lahir.'" 
    //     }';

    if(empty($member)){
        return '{
            "api_status": 0,
            "api_message": "Member belum terdaftar,\nsilahkan coba kembali!" 
        }';
    }
    else if(empty($first_name)){
        return '{
            "api_status": 0,
            "api_message": "Nama Depan tidak boleh kodong!" 
        }';
    }
    
    else if(empty($tgl_lahir)){
        return '{
            "api_status": 0,
            "api_message": "Tgl Lahir tidak boleh kodong!" 
        }';
    }
    
    try{
        
        $affected = DB::update('UPDATE members 
            SET first_name=?,last_name=?,photo=?,nik=?,email=?,phone=?,tgl_lahir=?,jenis_kelamin=?
            WHERE id=?', 
            [$first_name,$last_name,$photo,$nik,$email,$phone,$tgl_lahir,$jenis_kelamin,
                            $members_id]);
        
         return '{
                "api_status": 1,
                "api_message": "Data Member berhasil diubah!" 
            }';

        // if( $affected >= 1){
        //     return '{
        //         "api_status": 1,
        //         "api_message": "Data Member berhasil diubah!" 
        //     }';
        // }
        // else{ return '{
        //         "api_status": 0,
        //         "api_message": "Data Member gagal diubah! " 
        //     }'; }
    } catch(TimeoutException $e) {
           return '{
                "api_status": 0,
                "api_message": "Error = '.$e.'" 
            }';
            
    } catch(Exception $e){
        return '{
            "api_status": 0,
            "api_message": "Error = '.$e.'" 
        }';
    }
});

Route::post('/store-edit', function () {

    $members_id = Request::post('members_id');
    $username   = Request::post('username');
    $password   = Request::post('password');
    $condition   = Request::post('condition');
    
    $store_name         = Request::post('store_name');
    $store_image        = Request::post('store_image');
    $store_note         = Request::post('store_note');
    $store_address      = Request::post('store_address');
    $store_kode_pos     = Request::post('store_kode_pos');
    $subdistrict_id     = Request::post('subdistrict_id');
    $store_status       = Request::post('store_status');
    
    $member = DB::select('SELECT id FROM members WHERE id=? AND username=? AND password=?', [$members_id,$username,$password]);
    
    if(empty($member)){
        return '{
            "api_status": 0,
            "api_message": "Member belum terdaftar,\nsilahkan coba kembali!" 
        }';
    }
    else if(empty($store_name)){
        return '{
            "api_status": 0,
            "api_message": "Products ID tidak boleh kodong!" 
        }';
    }
    
    else if(empty($subdistrict_id)){
        return '{
            "api_status": 0,
            "api_message": "Kecamatan tidak boleh kodong!" 
        }';
    }
    
    try{
            $affected = 0;
            if($condition == "Buat Toko"){
                $affected = DB::update('UPDATE members 
                    SET store_name=?,store_image=?,store_note=?,store_address=?,store_kode_pos=?,subdistrict_id=?,store_opened=?,store_status=?
                    WHERE id=?', 
                [$store_name,$store_image,$store_note,$store_address,$store_kode_pos,$subdistrict_id,date("YmdHis"),"Buka",
                                    $members_id]);
            }else{
                $affected = DB::update('UPDATE members 
                    SET store_name=?,store_image=?,store_note=?,store_address=?,store_kode_pos=?,subdistrict_id=?,store_status=?
                    WHERE id=?', 
                [$store_name,$store_image,$store_note,$store_address,$store_kode_pos,$subdistrict_id,$store_status,
                                    $members_id]);
            }
            
        if( $affected >= 1){
            return '{
                "api_status": 1,
                "api_message": "Data toko berhasil disimpan!" 
            }';
        }
        else{ return '{
                "api_status": 0,
                "api_message": "Data toko gagal disimpan!" 
            }'; }
    } catch(TimeoutException $e) {
           return '{
                "api_status": 0,
                "api_message": "Error = '.$e.'" 
            }';
            
    } catch(Exception $e){
        return '{
            "api_status": 0,
            "api_message": "Error = '.$e.'" 
        }';
    }
});

Route::get('/api/get_saldo', 'ApiUserController@get_reqtopup');

Route::get('/create-store', 'ApiUserController@createstore');

Route::post('/carts-edit', function () {

    $members_id = Request::post('members_id');
    $username   = Request::post('username');
    $password   = Request::post('password');
    $edit_field   = Request::post('edit_field');
    
    $products_id    = Request::post('products_id');
    $qty            = Request::post('qty');
    $note           = Request::post('note');
    $trx_date   = date("YmdHis");
    
    $member = DB::select('SELECT id FROM members WHERE id=? AND username=? AND password=?', [$members_id,$username,$password]);
    
    // return '{
    //         "api_message": "$products_id = '.$products_id.'\n$qty '.$qty.'\n$note = '.$note.'" 
    //     }';

    if(empty($member)){
        return '{
            "api_status": 0,
            "api_message": "Member belum terdaftar,\nsilahkan coba kembali!" 
        }';
    }
    else if(empty($products_id)){
        return '{
            "api_status": 0,
            "api_message": "Products ID tidak boleh kodong!" 
        }';
    }
    
    try{
        $affected = 0;
        
        switch ($edit_field){
            case "qty":
                 $affected = DB::update('UPDATE product_carts 
                                SET qty = ?
                                WHERE members_id=? AND products_id=?', 
                                [$qty,$members_id,$products_id]);
                break;
            case "note":
                $affected = DB::update('UPDATE product_carts 
                                SET note=?
                                WHERE members_id=? AND products_id=?', 
                                [$note,$members_id,$products_id]);
                break;
            default: $affected = 0;
            
        }

        if( $affected >= 1){
            return '{
                "api_status": 1,
                "api_message": "Update cart berhasil!" 
            }';
        }
        else{ return '{
                "api_status": 0,
                "api_message": "Update cart gagal, coba beberapa saat lagi!" 
            }'; }
    } catch(TimeoutException $e) {
           return '{
                "api_status": 0,
                "api_message": "Error = '.$e.'" 
            }';
            
    } catch(Exception $e){
        return '{
            "api_status": 0,
            "api_message": "Error = '.$e.'" 
        }';
    }
});

// Route::post('/tranfer-saldo', function () {

//     $members_id = Request::post('members_id');
//     $username   = Request::post('username');
//     $password   = Request::post('password');
    
//     $usernameTujuan = Request::post('tujuan');
//     $nominal    = Request::post('nominal');
//     $trx_date   = date("YmdHis");
    
//     $data_admin = DB::table('ppob_fee_admin')->select('fee_admin') ->where('ppob_slug', '=', 'transfer-saldo')->get();
//     $fee_admin = (int) $data_admin[0]->fee_admin;
    
//     $memberTujuan = DB::select('SELECT id FROM members WHERE username=?', [$usernameTujuan]);
//     $member = DB::select('SELECT id,username,saldo,password FROM members WHERE id=?', [$members_id]);
//     $saldoMember = (int) $member[0]->saldo;
//     $hashedPassword = $member[0]->password;
    
//     if(empty($member)){
//         return '{
//             "api_status": 0,
//             "api_message": "Member belum terdaftar,\nsilahkan coba kembali!" 
//         }';
//     }
//     else if(empty($memberTujuan)){
//         return '{
//             "api_status": 0,
//             "api_message": "Member tujuan belum terdaftar,\nsilahkan masukan username lainnya!" 
//         }';
//     }
//     else if($saldoMember < (int)($nominal + $fee_admin)){
//         return '{
//             "api_status": 0,
//             "api_message": "Saldo tidak cukup.\n\nSaat ini saldo anda :\nRp. '.number_format($saldoMember).'.\nSilahkan lakukan topup saldo!" 
//         }';
//     }
    
//     if (!Hash::check($password, $hashedPassword)) {
//         if(md5($password) != $hashedPassword){
//             return '{
//                 "api_status": 0,
//                 "api_message": "Password salah, silahkan coba kembali!" 
//             }';
//         }
//     }

        
//     try{
//         $affected = DB::update('UPDATE members AS mem_dari, members AS mem_tujuan 
//                                 SET mem_dari.saldo=mem_dari.saldo-?, mem_tujuan.saldo=mem_tujuan.saldo+? 
//                                 WHERE mem_dari.id=? AND mem_tujuan.username=?', 
//                                 [($nominal+$fee_admin),$nominal,$members_id,$usernameTujuan,]);
                    
        
//         if( $affected >= 1){
//             $sender = DB::select('SELECT id,username,saldo FROM members WHERE id=?', [$members_id]);
//             $usernameSender =       $sender[0]->username;
//             $saldoSender    = (int) $sender[0]->saldo;
            
//             $receiver = DB::select('SELECT id,username,saldo FROM members WHERE username=?', [$usernameTujuan]);
//             $idReceiver         = (int) $receiver[0]->id;
//             $usernameReceiver   =       $receiver[0]->username;
//             $saldoReceiver      = (int) $receiver[0]->saldo;

//             DB::table('t_transfer_saldo')->insert(
//                 [
//                     'members_id' => $members_id, 'sender' => $usernameSender,'receiver' => $usernameReceiver,'nominal' => ($nominal+$fee_admin), 'ending_saldo' => $saldoSender,
//                      'date' => $trx_date,'status' => "Keluar",
//                 ]
//             );
            
//             DB::table('t_transfer_saldo')->insert(
//                 [
//                     'members_id' => $idReceiver, 'sender' => $usernameSender,'receiver' => $usernameReceiver,'nominal' => $nominal, 'ending_saldo' => $saldoReceiver,
//                      'date' => $trx_date,'status' => "Masuk",
//                 ]
//             );
            
//             return '{
//                 "api_status": 1,
//                 "api_message": "Tranfer saldo berhasil!" 
//             }';
//         }
//         else{ return '{
//                 "api_status": 0,
//                 "api_message": "Tranfer saldo gagal, coba beberapa saat lagi!" 
//             }'; }
//     } catch(TimeoutException $e) {
//            return '{
//                 "api_status": 0,
//                 "api_message": "TimeoutException = '.$e.'" 
//             }';
            
//     } catch(Exception $e){
//         return '{
//             "api_status": 0,
//             "api_message": "Error = '.$e.'" 
//         }';
//     }

// });

Route::get('/api/member', 'ApiUserController@getmember');