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
Route::get('api/login', 'ApiUserController@login');

Route::resource('saldo', 'SaldoController');
Route::post('api/saldo/topup', 'ApiTopupController@topupSaldo');
Route::post('api/buy_product', 'ApiUserController@buyProduct');
Route::post('api/cart/kurir', 'ApiUserController@addCartKurir');
Route::post('/saldo/verifikasi/{id}', 'SaldoController@verifikasiTopup');
Route::post('api/transaksi', 'ApiTransaksiController@createTransaksi');

// User
Route::get('/api/totaltransaksi/konfirmasipembayaran', 'ApiTransaksiController@totalKonfirmasiPembayaran');
Route::get('/api/totaltransaksi/pesanandiproses', 'ApiTransaksiController@totalPesananDiProses');
Route::get('/api/totaltransaksi/pesanandikirim', 'ApiTransaksiController@totalPesananDikirim');
Route::get('/api/totaltransaksi/pesananterkirim', 'ApiTransaksiController@totalPesananTerkirim');

// Store
Route::get('/api/totaltransaksi/menunggupembayaran', 'ApiTransaksiController@totalMenungguPembayaran');
Route::get('/api/totaltransaksi/sudahdibayar', 'ApiTransaksiController@totalSudahDibayar');
Route::get('/api/totaltransaksi/pesananstoredikirim', 'ApiTransaksiController@totalPesanansDikirim');