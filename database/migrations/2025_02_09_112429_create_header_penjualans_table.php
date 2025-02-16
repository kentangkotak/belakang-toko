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
        Schema::create('header_penjualans', function (Blueprint $table) {
            $table->id();
            $table->string('no_penjualan')->unique();
            $table->unsignedBigInteger('pelanggan_id')->nullable();
            $table->dateTime('tgl')->nullable();
            $table->double('total', 24, 2)->default(0);
            $table->double('total_diskon', 24, 2)->default(0);
            $table->string('flag', 10)->nullable()->comment('null = draft, 1=pesanan, 2=selesai');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('header_penjualans');
    }
};
