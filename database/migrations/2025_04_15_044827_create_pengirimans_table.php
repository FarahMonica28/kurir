<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('pengirimans', function (Blueprint $table) {
            $table->id();
            $table->string('no_resi')->unique();
            $table->string('penerima');
            $table->text('alamat_tujuan');
            $table->enum('status', ['belum_diambil', 'diambil', 'sedang_dikirim', 'terkirim', 'gagal'])->default('belum_diambil');
            $table->timestamp('waktu_ambil')->nullable();
            $table->timestamp('waktu_kirim')->nullable();

            // Optional relasi ke user (kurir)
            $table->foreignId('kurir_id')->nullable()->constrained('users')->nullOnDelete();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pengirimans');
    }
};
