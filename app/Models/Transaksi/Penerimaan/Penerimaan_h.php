<?php

namespace App\Models\Transaksi\Penerimaan;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Penerimaan_h extends Model
{
    use HasFactory;
    protected $table = 'penerimaan_h';
    protected $guarded = ['id'];

    public function rinci()
    {
        return  $this->hasMany(Penerimaan_r::class, 'nopenerimaan', 'nopenerimaan');
    }
}
