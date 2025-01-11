<?php

namespace App\Http\Controllers\Api\Transaksi\Penerimaan;

use App\Helpers\FormatingHelper;
use App\Http\Controllers\Controller;
use App\Models\Transaksi\Penerimaan\OrderPembelian_h;
use App\Models\Transaksi\Penerimaan\OrderPembelian_r;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OrderPenerimaanController extends Controller
{
    public function simpan(Request $request)
    {
        if($request->noorder === null)
        {
            DB::select('call noorderpembelian(@nomor)');
            $x = DB::table('counter')->select('orderbeli')->get();
            $no = $x[0]->orderbeli;

            $notrans = FormatingHelper::matorderpembelian($no, 'OR');
        }else{
            $notrans = $request->noorder;
        }

        try{
            DB::beginTransaction();
            $simpan = OrderPembelian_h::updateOrCreate(
                [
                    'noorder' => $notrans,
                ],
                [
                    'tglorder' => date('Y-m-d H:i:s'),
                    'kdsuplier' => $request->kdsuplier
                ]
            );
            $simpanR = OrderPembelian_r::create(
                [
                    'noorder' => $notrans,
                    'kdbarang' => $request->kdbarang,
                    'jumlahpo' => $request->jumlah,
                    'hargapo' => $request->harga,
                    'user' => $request->user,
                ]
            );

            DB::commit();
            return new JsonResponse(
                [
                    'message' => 'Data Berhasil Disimpan',
                    'notrans' => $notrans,
                    'result' => $simpanR
                ],200);

        } catch (\Exception $e) {
            DB::rollBack();
            return new JsonResponse(['message' => 'ada kesalahan', 'error' => $e], 500);
        }
    }

    public function getlistorder()
    {
        $list = OrderPembelian_h::with(
            [
                'suplier',
                'rinci' => function($rinci){
                    $rinci->with(['mbarang']);
                }
            ]
        )
        ->orderBy('id', 'desc')
        ->simplePaginate(request('per_page'));
        return new JsonResponse($list);
    }

    public function getallbynoorder()
    {
        $list = OrderPembelian_h::with(
            [
                'suplier',
                'rinci' => function($rinci){
                    $rinci->with(['mbarang']);
                }
            ]
        )
        ->where('noorder', request('noorder'))
        ->get();
        return new JsonResponse($list);
    }
}
