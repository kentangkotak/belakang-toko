<?php

namespace App\Http\Controllers\Api\Transaksi\Penerimaan;

use App\Helpers\FormatingHelper;
use App\Http\Controllers\Controller;
use App\Models\Transaksi\Penerimaan\Penerimaan_h;
use App\Models\Transaksi\Penerimaan\Penerimaan_r;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PenerimaanController extends Controller
{
    public function simpan(Request $request)
    {

        if($request->penerimaan === '' || $request->penerimaan === null)
        {
            DB::select('call nopenerimaan(@nomor)');
            $x = DB::table('counter')->select('penerimaan')->get();
            $no = $x[0]->penerimaan;
            $nopenerimaan = FormatingHelper::nopenerimaan($no, 'P');
        }else{
            $nopenerimaan = $request->penerimaan;
        }

        try{
            DB::beginTransaction();
                $simpan = Penerimaan_h::updateOrCreate(
                    [
                        'nopenerimaan' => $nopenerimaan,
                    ],
                    [
                        'noorder' => $request->noorder,
                        'tgl' => date('Y-m-d H:i:s'),
                        'kdsupllier' => $request->kdsuplier,
                    ]
                );
                // return 'wew';
                $hargabelisatuankecil = $request->jumlahpo/$request->isi;
                $simpanR = Penerimaan_r::create(
                    [
                        'nopenerimaan' => $nopenerimaan,
                        'noorder' => $request->noorder,
                        'kdbarang' => $request->kdbarang,
                        'jumlah_b' => $request->jumlahpo,
                        'jumlah_k' => $request->jumlahpo_k,
                        'isi' => $request->isi,
                        'satuan_b' => $request->satuan_b,
                        'satuan_k' => $request->satuan_k,
                        'hargafaktur' => $request->hargafaktur,
                        'harga_beli_b' => $request->hargaasli,
                        'harga_beli_k' => $hargabelisatuankecil,
                        'subtotal' => 1,
                    ]
                );
            DB::commit();
                return new JsonResponse([
                    'message' => 'Data Tersimpan',
                    'result' => $simpanR
                ]);

        }catch (\Exception $e){
            DB::rollBack();
            return new JsonResponse(['message' => 'ada kesalahan', 'error' => $e], 500);
        }
    }

    public function getList()
    {
        $list = Penerimaan_h::with(
            [
                'rinci'
            ]
        )
        ->simplePaginate(request('per_page'));
        return new JsonResponse($list);
    }
}
