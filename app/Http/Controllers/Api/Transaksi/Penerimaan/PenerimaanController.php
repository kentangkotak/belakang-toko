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
            $x = DB::table('counter')->select('nopenerimaan')->get();
            $no = $x[0]->nopenerimaan;
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

            $simpanR = Penerimaan_r::create(
                [
                    'nopenerimaan' => $nopenerimaan,
                    'noorder' => $request->noorder,
                    'kdbarang' => $request->kdbarang,
                    'jumlah_b' => $request->noorder,
                    'jumlah_k' => $request->noorder,
                    'isi' => $request->isi,
                    'satuan_b' => $request->satuan_b,
                    'satuan_k' => $request->satuan_k,
                    'hargafaktur' => $request->hargafaktur,
                    'harga_beli_b' => $request->hargaasli,
                    'harga_beli_k' => $request->noorder,
                    'subtotal' => $request->noorder,
                ]
            );
            DB::commit();
        }catch (\Exception $e){
            DB::rollBack();
            return new JsonResponse(['message' => 'ada kesalahan', 'error' => $e], 500);
        }
    }
}
