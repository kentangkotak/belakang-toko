<?php

use App\Http\Controllers\Api\Transaksi\Penjualan\PenjualanController;
use Illuminate\Support\Facades\Route;

Route::group([
    //'middleware' => 'auth:api',
    'prefix' => 'transaksi/penjualan'
], function () {
    Route::get('/list-barang', [PenjualanController::class, 'getBarang']);
    Route::post('/simpan-detail', [PenjualanController::class, 'simpanDetail']);
    Route::post('/delete-detail', [PenjualanController::class, 'hapusDetail']);

    // list penjualan
    Route::get('/list', [PenjualanController::class, 'getListPenjualan']);
});
