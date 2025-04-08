<?php

namespace App\Http\Controllers\Api\Transaksi\Penjualan;

use App\Helpers\FormatingHelper;
use App\Http\Controllers\Controller;
use App\Models\Barang;
use App\Models\Pelanggan;
use App\Models\Stok\stok;
use App\Models\Transaksi\Penjualan\DetailPenjualan;
use App\Models\Transaksi\Penjualan\DetailPenjualanFifo;
use App\Models\Transaksi\Penjualan\HeaderPenjualan;
use App\Models\User;
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
            ->with([
                'stok' => function ($q) {
                    $q->select(
                        'kdbarang',
                        DB::raw('sum(jumlah_b) as jumlah_b'),
                        DB::raw('sum(jumlah_k) as jumlah_k'),
                        'isi',
                        'satuan_b',
                        'satuan_k',
                        'harga_beli_b',
                        'harga_beli_k',
                    )
                        ->groupBy('kdbarang')
                        ->where('jumlah_k', '>', 0);
                },
            ])
            ->limit(request('limit'))
            ->get();
        return new JsonResponse($data);
    }
    public function getSales()
    {
        // temporary sebelum ada data sales
        $data = User::get();
        return new JsonResponse($data);
    }
    public function getPelanggan()
    {
        $data = Pelanggan::whereNull('flaging')
            ->where(function ($x) {
                $x->where('nama', 'like', '%' . request('q') . '%')
                    ->orWhere('kodeplgn', 'like', '%' . request('q') . '%')
                    ->orWhere('namabank', 'like', '%' . request('q') . '%')
                    ->orWhere('telepon', 'like', '%' . request('q') . '%')
                    ->orWhere('alamat', 'like', '%' . request('q') . '%');
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
                    'sales_id' => $request->sales_id,
                    'pelanggan_id' => $request->pelanggan_id,
                    'total' => $total,
                    'total_diskon' => $totalDiskon,
                ]
            );
            if (!$detail) {
                throw new Exception("Header Tidak Tersimpan", 1);
            }
            $header->load('detail.masterBarang', 'sales', 'pelanggan');
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
    /**
     * list penjualan
     * jika penjualan dari hp di flag 1
     * di front end di bedakan cara edit nya
     */

    public function getListPenjualan()
    {
        $raw = HeaderPenjualan::with([
            'pelanggan',
            'detailFifo.masterBarang',
            'detail.masterBarang',
            'sales',
        ])
            ->where('no_penjualan', 'like', '%' . request('q') . '%')
            // ->where('flag', '!=', '1')
            ->orderBy('flag', 'asc')
            ->orderBy('id', 'desc')
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
    public function simpanPembayaran(Request $request)
    {
        try {
            DB::beginTransaction();
            $data = HeaderPenjualan::where('no_penjualan', $request->no_penjualan)->first();
            if (!$data) {
                return new JsonResponse(['message' => 'Gagal Menyimpan, data tidak ditemukan'], 410);
            }
            $data->update([
                'pelanggan_id' => $request->pelanggan_id,
                'bayar' => $request->bayar,
                'kembali' => $request->kembali,
                'flag' => $request->cara_bayar,
            ]);

            $detail = DetailPenjualan::where('no_penjualan', $request->no_penjualan)->get();
            $kode = $detail->pluck('kodebarang');
            $stoks = stok::lockForUpdate()->whereIn('kdbarang', $kode)->where('jumlah_k', '>', 0)->orderBy('id', 'asc')->get();


            // update Stok
            foreach ($detail as $item) {
                $stok = collect($stoks)->where('kdbarang', $item->kodebarang);
                $jumlahKeluar = $item->jumlah;
                foreach ($stok as $stokItem) {
                    if ($jumlahKeluar <= 0) break;
                    $sisa = $stokItem->jumlah_k;

                    $pengurangan = min($jumlahKeluar, $sisa);
                    $simpanRinciFifo = DetailPenjualanFifo::updateOrCreate([
                        'no_penjualan' => $request->no_penjualan,
                        'kodebarang' => $item->kodebarang,
                        'stok_id' => $stokItem->id,
                    ], [
                        'jumlah' => $pengurangan,
                        'harga_beli' => $stokItem->harga_beli_k,
                        'harga_jual' => $item->harga_jual,
                        'diskon' => $item->diskon,
                        'subtotal' => $item->subtotal,
                    ]);
                    if (!$simpanRinciFifo) {
                        throw new \Exception('Rincian Obat gagal disimpan');
                    }
                    // Update jumlah stok pada item
                    $stokItem->decrement('jumlah_k', $pengurangan); // Perbaikan: langsung update jumlah dalam satu langkah
                    $jumlahKeluar -= $pengurangan; // Perbaikan: kurangi permintaan yang sudah terpenuhi

                }
                // return new JsonResponse([
                //     'message' => 'Percobaan',
                //     'item' => $item,
                //     'stok' => $stok,
                // ], 410);
            }
            $data->load(
                'detail.masterBarang',
                'detailFifo.masterBarang',
                'sales',
                'pelanggan'
            );

            DB::commit();
            return new JsonResponse([
                'message' => 'Data Pembayaran Sudah di catat',
                'data' => $data
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
}
