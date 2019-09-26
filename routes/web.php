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

Route::get('/api/v1/topuptest', 'ApiTopupController@indextest');
Route::get('/api/v1/paytest', 'ApiTopupController@paytest');

Route::get('/api/v1/topup', 'ApiTopupController@index');
Route::get('/api/v1/inquiry', 'ApiTopupController@inquiryPasca');
Route::get('/api/v1/pln/postpaid/inquiry', 'ApiTopupController@plnPostpaidInquiry');
Route::get('/api/v1/pln/postpaid/pay', 'ApiTopupController@plnPostpaidPay');
Route::get('/api/v1/inquiry/pay', 'ApiTopupController@inquiryPay');
Route::get('/api/v1/prepaid/callback', 'ApiTopupController@callback');
Route::post('/api/v1/prepaid/callback', 'ApiTopupController@callback');

Route::get('/api/v1/pay', 'ApiTopupController@pay');

Route::get('/home', 'HomeController@index')->name('home');
Route::get('api/login', 'ApiUserController@login');
Route::get('api/v1/login', 'ApiUserController@login');
Route::get('/api/v1/slider', 'ApiUserController@slider');
Route::get('/api/v1/profile', 'ApiUserController@getProfile');

Route::get('api/forgot_password', 'ApiUserController@forgotPassword');

Route::post('api/saldo/bonus', 'ApiTopupController@topupBonus');

Route::resource('saldo', 'SaldoController');
Route::get('/member-saldo', 'SaldoController@memberSaldo');
Route::get('transaksi', 'TransaksiController@getTransaksi');
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