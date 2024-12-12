<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('barangs', function (Blueprint $table) {
            $table->id();
            $table->string('kodebarang');
            $table->string('namabarang');
            $table->string('merk');
            $table->string('satuan_b');
            $table->string('satuan_k');
            $table->integer('isi');
            $table->string('kategori');
            $table->decimal('hargajual1', 12, 2)->nullable();
            $table->decimal('hargajual2', 12, 2)->nullable();
            $table->string('ukuran');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('barangs');
    }
};
