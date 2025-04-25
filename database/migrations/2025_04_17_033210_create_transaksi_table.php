<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('transaksi', function (Blueprint $table) {
            $table->id('no_transaksi');
            $table->text('alamat_asal');
            $table->text('alamat_tujuan');
            $table->string('penerima', 70);
            $table->string('pengirim', 70);
            $table->float('berat_barang');
            $table->integer('biaya');
            $table->string('kurir_id', 70);
            $table->string('nama_barang', 50);
            $table->string('no_hp_penerima', 12);
            $table->text('waktu');
            $table->string('status', 25);
            $table->integer('penilaian')->nullable();
            $table->text('komentar')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transaksi');
    }
};
