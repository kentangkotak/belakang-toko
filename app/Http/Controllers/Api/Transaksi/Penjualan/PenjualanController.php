<?php

namespace App\Http\Controllers\Api\Transaksi\Penjualan;

use App\Helpers\FormatingHelper;
use App\Http\Controllers\Controller;
use App\Models\Barang;
use App\Models\Transaksi\Penjualan\DetailPenjualan;
use App\Models\Transaksi\Penjualan\HeaderPenjualan;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PenjualanController extends Controller
{
    public function getBarang()
    {
        $data = Barang::select(
            'brand',
            'namabarang',
            'kodebarang',
            'id',
            'satuan_k',
            'seri',
            'ukuran',
            'hargajual1',
            'hargajual2',
        )
            ->whereNull('flaging')
            ->where(function ($x) {
                $x->where('namabarang', 'like', '%' . request('q') . '%')
                    ->orWhere('kodebarang', 'like', '%' . request('q') . '%');
            })
            ->limit(request('limit'))
            ->get();
        return new JsonResponse($data);
    }
    public function simpanDetail(Request $request)
    {
        try {
            DB::beginTransaction();
            if ($request->nota === null) {
                DB::select('call no_nota_penjualan(@nomor)');
                $x = DB::table('counter')->select('penjualan')->first();
                $no = $x->penjualan;

                $nota = FormatingHelper::notaPenjualan($no, 'PJL');
            } else {
                $nota = $request->nota;
            }
            $subtotal = ($request->jumlah * $request->harga_jual) - $request->diskon;
            $detail = DetailPenjualan::updateOrCreate(
                [
                    'no_penjualan' => $nota,
                    'kodebarang' => $request->kodebarang,
                ],
                [
                    'jumlah' => $request->jumlah,
                    'harga_jual' => $request->harga_jual,
                    'harga_beli' => $request->harga_beli,
                    'diskon' => $request->diskon,
                    'subtotal' => $subtotal
                ]
            );
            if (!$detail) {
                throw new Exception("Detail Tidak Tersimpan", 1);
            }
            $total = DetailPenjualan::where('no_penjualan', '=', $nota)->sum('subtotal');
            $totalDiskon = DetailPenjualan::where('no_penjualan', '=', $nota)->sum('diskon');
            $header = HeaderPenjualan::updateOrCreate(
                [
                    'no_penjualan' => $nota,
                ],
                [
                    'tgl' => date('Y-m-d H:i:s'),
                    'pelanggan_id' => $request->pelanggan_id,
                    'total' => $total,
                    'total_diskon' => $totalDiskon,
                ]
            );
            if (!$detail) {
                throw new Exception("Header Tidak Tersimpan", 1);
            }
            $header->load('detail.masterBarang', 'pelanggan');
            DB::commit();
            return new JsonResponse([
                'message' => 'Data telah disimpan',
                'detail' => $detail,
                'header' => $header,
                'nota' => $nota,
                'total' => $total,
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();
            return new JsonResponse([
                'message' => $th->getMessage(),
                'line' => $th->getLine(),
                'file' => $th->getFile(),
            ], 410);
        }
    }
    public function getListPenjualan()
    {
        $raw = HeaderPenjualan::with([
            'pelanggan',
            'detail.masterBarang',
        ])
            ->where('no_penjualan', 'like', '%' . request('q') . '%')
            ->orderBy('id', 'desc')
            ->orderBy('flag', 'asc')
            ->simplePaginate(request('per_page'));
        $data['data'] = collect($raw)['data'];
        $data['meta'] = collect($raw)->except('data');
        return new JsonResponse($data);
    }
    public function hapusDetail(Request $request)
    {
        $detail = DetailPenjualan::find($request->id);
        if (!$detail) {
            return new JsonResponse(['message' => 'Data Tidak Ditemukan'], 410);
        }
        $detail->delete();

        $allDetail = DetailPenjualan::where('no_penjualan', '=', $request->no_penjualan)->get();
        $header = HeaderPenjualan::where('no_penjualan', '=', $request->no_penjualan)
            ->first();
        $isDeleteHeader = '0';
        if (sizeof($allDetail) == 0) {
            $header->delete();
            $isDeleteHeader = '1';
        } else $header->load('pelanggan', 'detail.masterBarang');

        return new JsonResponse([
            'message' => 'Data Sudah Dihapus',
            'header' => $header,
            'isDeleteHeader' => $isDeleteHeader,
        ], 200);
    }
}
