<?php

use App\Models\Barang;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});
Route::get('/autogen', function () {

    echo "ok4";
    // $data = DB::table('barangs')->get();
    // $data = Barang::all();
    // return response()->json($data);
});

Route::get('/autogenx', function () {
    return 'wew';
});
