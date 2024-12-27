<?php

namespace App\Http\Controllers\Api\Master;

use App\Http\Controllers\Controller;
use App\Models\Pelanggan;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PelangganController extends Controller
{
    public function listpelanggan()
    {
        $list =  Pelanggan::where('flaging' ,'!==','1')->get();
        return new JsonResponse($list);
    }

    public function simpan(Request $request)
    {
        $simpan = Pelanggan::create(
            [
                'nama' => $request->nama,
                'alamat' => $request->alamat,
                'telepon' => $request->telepon,
                'norek' => $request->norek,
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
        $cari = Pelanggan::find($request->id);
        $cari->flaging = '1';
        $cari->save();

        return new JsonResponse(
            [
                'message' => 'Data Sudah Dihapus',
            ],200
        );
    }
}
