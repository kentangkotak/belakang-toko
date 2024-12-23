<?php

namespace App\Http\Controllers\Api\Master;

use App\Helpers\FormatingHelper;
use App\Http\Controllers\Controller;
use App\Models\Barang;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BarangController extends Controller
{
    public function listbarang()
    {
        $data = Barang::whereNull('flaging')
        ->when(request('q') !== '' || request('q') !== null, function($x){
            $x->where('namabarang', 'like', '%' . request('q') . '%')
              ->orWhere('kodebarang','like', '%' . request('q') . '%');
        })->paginate('per_page');
        return new JsonResponse($data);
    }

    public function simpanbarang(Request $request)
    {
        if($request->kodebarang === '' || $request->kodebarang === null)
        {
            $cek = Barang::count();
            $total = (int) $cek + (int) 1;
            $kodebarang = FormatingHelper::matkdbarang($total,'BRG');
        }else{
            $kodebarang = $request->kodebarang;
        }
        $simpan = Barang::updateOrCreate(
            [
                'kodebarang' => $kodebarang
            ],
            [
                'namabarang' => $request->namabarang,
                'merk' => $request->merk,
                'satuan_b' => $request->satuan_b,
                'satuan_k' => $request->satuan_k,
                'isi' => $request->isi,
                'kategori' => $request->kategori,
                'hargajual1' => $request->hargajual1,
                'hargajual2' => $request->hargajual2,
                'ukuran' => $request->ukuran,
            ]
        );

        return new JsonResponse(
            [
                'message' => 'Data Sudah Disimpan',
                'result' => $simpan
            ], 200
        );
    }
}
