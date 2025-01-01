<?php

use App\Http\Controllers\Api\Master\BarangController;
use App\Http\Controllers\Api\Master\SelectController;
use Illuminate\Support\Facades\Route;

Route::group([
    //'middleware' => 'auth:api',
    'prefix' => 'master/select'
], function () {
    Route::get('/master-satuan-all', [SelectController::class, 'satuan_all']);
    Route::get('/master-satuan-filter', [SelectController::class, 'satuan_filter']);


    // ini untuk select yg lain
});
