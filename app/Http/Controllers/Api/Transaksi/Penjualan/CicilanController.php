<?php

namespace App\Http\Controllers\Api\Transaksi\Penjualan;

use App\Http\Controllers\Controller;
use App\Models\Transaksi\Penjualan\HeaderPenjualan;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CicilanController extends Controller
{
    //
    public function getPenjualan()
    {
        $raw = HeaderPenjualan::with([
            'pelanggan',
            'sales',
            'detail.masterBarang',
        ])
            ->where('no_penjualan', 'like', '%' . request('q') . '%')
            ->when(
                request('flag') == 'semua',
                function ($q) {
                    $q->whereIn('flag', ['2', '3']);
                },
                function ($q) {
                    $q->where('flag', request('flag'));
                }
            )
            ->when(request()->has('sales'), function ($q) {
                $sales = User::select('id')->where('nama', 'LIKE', '%' . request('sales') . '%')->pluck('id');
                $q->whereIn('sales_id', $sales);
            })
            ->when(request()->has('q'), function ($q) {
                $q->where('no_penjualan', 'like', '%' . request('q') . '%');
            })
            ->orderBy('flag', 'asc')
            ->orderBy('id', 'desc')
            ->simplePaginate(request('per_page'));
        $data['data'] = collect($raw)['data'];
        $data['meta'] = collect($raw)->except('data');
        return new JsonResponse($data);
    }

    public function bawaNota(Request $request)
    {
        $data = HeaderPenjualan::find($request->id);
        if (!$data) {
            return new JsonResponse(['message' => 'Data Tidak Ditemukan'], 410);
        }
        $data->update([
            'flag' => '4'
        ]);
        $data->load([
            'pelanggan',
            'sales',
            'detail.masterBarang',
        ]);
        return new JsonResponse([
            'message' => 'Berhasil Membawa Nota',
            'data' => $data

        ], 200);
    }
}
