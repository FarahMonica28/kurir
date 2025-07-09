<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Tabel Transaksi
        Schema::create('transaksii', function (Blueprint $table) {
            $table->id();
            $table->string('no_resi')->unique();
            // $table->string('pengirim');
            $table->string('penerima');
            $table->text('alamat_asal');
            $table->text('alamat_tujuan');
            $table->decimal('berat_barang', 8, 2); // kg
            $table->string('ekspedisi'); // jne / tiki / pos
            $table->string('layanan'); // REG, YES, dll
            $table->integer('biaya'); // biaya dari RajaOngkir
            $table->dateTime('waktu')->nullable();
            $table->enum('status', ['menunggu', 'diproses', 'dikirim', 'selesai']);
            $table->integer('penilaian')->nullable(); // rating 1-5
            $table->text('komentar')->nullable();

            // Relasi lokasi
            $table->foreignId('asal_provinsi_id')->constrained('provinces');
            $table->foreignId('asal_kota_id')->constrained('cities');
            $table->foreignId('tujuan_provinsi_id')->constrained('provinces');
            $table->foreignId('tujuan_kota_id')->constrained('cities');

            // Relasi pengguna dan kurir
            $table->foreignId('pengguna_id')->constrained('pengguna');
            // $table->foreignId('kurir_id')->constrained('users');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tb_transaksi');
        Schema::dropIfExists('cities');
        Schema::dropIfExists('provinces');
    }
};
