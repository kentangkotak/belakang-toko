<?php

namespace App\Models\Transaksi\Penerimaan;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderPembelian_r extends Model
{
    use HasFactory;
    protected $table = 'orderpembelian_r';
    protected $guarded = ['id'];
}
