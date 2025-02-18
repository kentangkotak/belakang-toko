<?php

namespace App\Http\Controllers\Api\Master;

use App\Helpers\FormatingHelper;
use App\Http\Controllers\Controller;
use App\Models\Barang;
use App\Models\Imagebarang;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BarangController extends Controller
{
    public function listbarang()
    {
        // $data = Barang::whereNull('barangs.flaging')
        //     ->when(request('q') !== '' || request('q') !== null, function ($x) {
        //         $x->where('barangs.namabarang', 'like', '%' . request('q') . '%')
        //             ->orWhere('barangs.kodebarang', 'like', '%' . request('q') . '%');
        //     })
        //     ->with('images')
        //     // ->leftJoin('imagebarangs', 'barangs.kodebarang', '=', 'imagebarangs.kodebarang')
        //     // ->select('barangs.*',)
        //     // ->selectSub(function ($query) {
        //     //     $query->from('imagebarangs')
        //     //          ->selectRaw('GROUP_CONCAT(gambar)')
        //     //         ->whereColumn('imagebarangs.kodebarang', 'barangs.kodebarang');
        //     // }, 'gambar_list')
        //     // ->groupBy('barangs.kodebarang')
        //     ->orderBy('barangs.id', 'desc')
        //     ->simplePaginate(request('per_page'));

        $data = Barang::whereNull('barangs.flaging')
            ->when(request('q'), function ($query) {
                $query->where(function ($q) {
                    $q->where('barangs.namabarang', 'like', '%' . request('q') . '%')
                    ->orWhere('barangs.kodebarang', 'like', '%' . request('q') . '%');
                });
            })
            ->leftJoin('imagebarangs', function ($join) {
                $join->on('barangs.kodebarang', '=', 'imagebarangs.kodebarang')
                    ->where('imagebarangs.flag_thumbnail', '=', 1); // Hanya ambil gambar dengan flag_thumbnail = 1
            })
            ->select('barangs.*')
            ->selectRaw('
                GROUP_CONCAT(imagebarangs.gambar) as image,
                GROUP_CONCAT(imagebarangs.flag_thumbnail) as flag_thumbnail
            ')
            ->groupBy('barangs.id') // Group by primary key
            ->orderBy('barangs.id', 'desc')
            ->simplePaginate(request('per_page'));

        return new JsonResponse($data);
    }

    public function simpanbarang(Request $request)
    {

        if ($request->kodebarang === '' || $request->kodebarang === null)
        {
            $cek = Barang::count();
            $total = (int) $cek + (int) 1;
            $kodebarang = FormatingHelper::matkdbarang($total, 'BRG');
        } else {
            $kodebarang = $request->kodebarang;
        }

        $simpan = Barang::updateOrCreate(
        [
            'kodebarang' => $kodebarang
        ],
        [
            'namabarang' => $request->namabarang,
            'kualitas' => $request->kualitas,
            'brand' => $request->brand,
            'seri' => $request->seri,
            'satuan_b' => $request->satuan_b,
            'satuan_k' => $request->satuan_k,
            'isi' => $request->isi,
            'kategori' => $request->kategori,
            'hargajual1' => $request->hargajual1,
            'hargajual2' => $request->hargajual2,
            'hargabeli' => $request->hargabeli,
            'ukuran' => $request->ukuran,
        ]);
        if ($request->has('rincians')) {
            foreach ($request->rincians as $img) {
                if (isset($img['gambar']) && $img['gambar']->isValid()) {
                    $path = $img['gambar']->store('images', 'public');

                    // Gunakan relasi tanpa perlu set kodebarang manual
                    $simpan->images()->create([
                        'kodebarang' => $simpan->kodebarang,
                        'gambar' => $path
                    ]);
                }
            }
        }

            // if ($request->has('rincians') && is_array($request->rincians)) {
            // foreach ($request->rincians as $img) {
            //     if (!empty($img['gambar']) && $img['gambar'] instanceof \Illuminate\Http\UploadedFile && $img['gambar']->isValid()) {
            //         $path = $img['gambar']->store('images', 'public');

            //         $simpan->images()->create([
            //             'kodebarang'     => $simpan->kodebarang,
            //             'gambar'         => $path,
            //             ]);
            //         }
            //     }
            // }
        return new JsonResponse(
                [
                    'message' => 'Data Berhasil disimpan...!!!',
                    'result' => $simpan->load('images')
                ], 200);

        // if ($request->has('rincians')) {
        // foreach ($request->rincians as $img) {
        //     if (isset($img['images']) && $img['images'] instanceof \Illuminate\Http\UploadedFile) {
        //         $path = $img['images']->store('images', 'public');

        //         $simpan->imagebarang()->create([
        //             'kodebarang' => $simpan->kodebarang,
        //             'flag_thumbnail' => $img['flag_thumbnail'] ?? 0,
        //             'images' => $path,
        //             ]);
        //         }
        //     }
        // }

        // if ($request->has('gambar')) {
        //     $path = $request->file('gambar')->store('image', 'public');
        //     // array_merge($request, ['image' => $path]);
        //     $img->create(['image' => $path]);
        // }
        // foreach ($request->rincians as $img) {
        //     $simpan->dataimage()->create(
        //         [
        //             'kodebarang' => $simpan->kodebarang,
        //             'gambar' => null,
        //             'flag_thumbnail' => $img['flag_thumbnail'] ?? '0'
        //         ]);
        // }


    //    if ($request->has('rincians') && is_array($request->rincians)) {
    //     foreach ($request->rincians as $img) {
    //         if (!empty($img['gambar']) && $img['gambar'] instanceof \Illuminate\Http\UploadedFile && $img['gambar']->isValid()) {
    //             $path = $img['gambar']->store('images', 'public');

    //             $simpan->imagebarang()->create([
    //                 'kodebarang'     => $simpan->kodebarang,
    //                 'flag_thumbnail' => $img['flag_thumbnail'] ?? 0,
    //                 'gambar'         => $path,
    //                 ]);
    //             }
    //         }
    //     }


        // if ($request->has('rincians')) {
        // foreach ($request->rincians as $img) {
        //     if (isset($img['images']) && $img['images'] instanceof \Illuminate\Http\UploadedFile) {
        //         $path = $img['images']->store('images', 'public');

        //         $simpan->imagebarang()->create([
        //             'kodebarang' => $simpan->kodebarang,
        //             'flag_thumbnail' => $img['flag_thumbnail'] ?? 0,
        //             'images' => $path,
        //             ]);
        //         }
        //     }
        // }

        // if ($request->has('gambar')) {
        //     $path = $request->file('gambar')->store('image', 'public');
        //     // array_merge($request, ['image' => $path]);
        //     $img->create(['image' => $path]);
        // }
        // return new JsonResponse(
        //     [
        //         'message' => 'Data Sudah Disimpan',
        //         'result' => $simpan
        //     ],
        //     200
        // );
    }

    public function deleteItem(Request $request)
    {
        // nyoba biar bisa push

        $cek = Barang::find($request->id);
        if (!$cek) {
            return new JsonResponse(['message' => 'Data Tidak Ditemukan'], 500);
        }

        $cek->delete();

        return new JsonResponse(['message' => 'Data Sudah Dihapus'], 200);
    }
}
