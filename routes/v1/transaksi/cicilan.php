<?php

use App\Http\Controllers\Api\Transaksi\Penjualan\CicilanController;
use Illuminate\Support\Facades\Route;

Route::group([
    //'middleware' => 'auth:api',
    'prefix' => 'transaksi/cicilan'
], function () {
    Route::get('/list', [CicilanController::class, 'getPenjualan']);
    Route::post('/bawa-nota', [CicilanController::class, 'bawaNota']);
});
