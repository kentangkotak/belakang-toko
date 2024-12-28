<?php
use App\Http\Controllers\Api\Master\SupplierController;
use App\Http\Controllers\Api\Master\UsersController;
use Illuminate\Support\Facades\Route;

Route::group([
    //'middleware' => 'auth:api',
    'prefix' => 'master/users'
], function () {
    Route::post('/save', [UsersController::class, 'save_user']);
    Route::post('/delete', [UsersController::class, 'remove_user']);
});
