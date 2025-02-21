<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Barang extends Model
{
    use HasFactory;
    protected $guarded = ['id'];
     public function rincians()
    {
        return $this->hasMany(Imagebarang::class, 'kodebarang', 'kodebarang');
    }


}
