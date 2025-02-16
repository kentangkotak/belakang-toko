<?php

namespace App\Models\Transaksi\Penjualan;

use App\Models\Pelanggan;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HeaderPenjualan extends Model
{
    use HasFactory;
    protected $guarded = ['id'];
    public function detail()
    {
        return $this->hasMany(DetailPenjualan::class, 'no_penjualan', 'no_penjualan');
    }
    public function pelanggan()
    {
        return $this->belongsTo(Pelanggan::class);
    }
}
