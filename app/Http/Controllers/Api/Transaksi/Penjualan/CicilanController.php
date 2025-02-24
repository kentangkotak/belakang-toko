<?php

namespace App\Http\Controllers\Api\Transaksi\Penjualan;

use App\Http\Controllers\Controller;
use App\Models\Transaksi\Penjualan\HeaderPenjualan;
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
            ->orderBy('flag', 'asc')
            ->orderBy('id', 'desc')
            ->simplePaginate(request('per_page'));
        $data['data'] = collect($raw)['data'];
        $data['meta'] = collect($raw)->except('data');
        return new JsonResponse($data);
    }
}
