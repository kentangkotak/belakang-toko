<?php

namespace App\Http\Controllers\Api\Master;

use App\Http\Controllers\Controller;
use App\Models\Supplier;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SupplierController extends Controller
{
    public function list()
    {
        $list = Supplier::whereNull('flaging')
        ->when(request('q') !== '' || request('q') !== null, function($x){
            $x->where('nama', 'like', '%' . request('q') . '%')
              ->orWhere('telepon','like', '%' . request('q') . '%')
              ->orWhere('alamat','like', '%' . request('q') . '%')
              ;
        })
        ->orderBy('id', 'desc')
        ->simplePaginate(request('per_page'));
        return new JsonResponse($list);
    }

    public function simpan(Request $request)
    {
        $simpan = Supplier::create(
            [
                'nama' => $request->nama,
                'alamat' => $request->alamat,
                'telepon' => $request->telepon,
            ]
        );
        return new JsonResponse(
            [
                'message' => 'Data Berhasil Disimpan...!!!',
                'result' => $simpan
            ], 200);
    }

    public function hapus(Request $request)
    {
        $cari = Supplier::find($request->id);
        $cari->flaging = '1';
        $cari->save();

        return new JsonResponse(
            [
                'message' => 'Data Sudah Dihapus',
            ],200
        );
    }

    public function alllist()
    {
        $list = Supplier::whereNull('flaging')
        ->orderBy('id', 'desc')
        ->get();
        return new JsonResponse($list);
    }
}
