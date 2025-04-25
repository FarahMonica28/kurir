<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Jalankan migration.
     */
    public function up(): void
{
    Schema::create('pengiriman', function (Blueprint $table) {
        $table->id(); // Primary key
        $table->string('no_resi')->unique();
        $table->unsignedBigInteger('user_id')->nullable(); // Pengirim
        $table->foreignId('kurir_id')->constrained('kurir', 'kurir_id')->onDelete('cascade'); // Foreign key ke tabel kurir
        $table->string('paket'); // Nama atau deskripsi paket
        $table->enum('status', ['dikemas', 'dikirim', 'diterima'])->default('dikemas'); // Status pengiriman
        $table->timestamp('tanggal_pengiriman')->nullable(); // Tanggal pengiriman
        $table->timestamp('tanggal_penerimaan')->nullable(); // Tanggal penerimaan
        $table->string('penerima'); // Nama penerima paket
        $table->text('alamat'); // Alamat penerima paket
        $table->decimal('biaya', 10, 2)->default(0); // Biaya pengiriman
        $table->timestamps(); // created_at & updated_at
    });
    // database/migrations/xxxx_xx_xx_create_pengiriman_table.php
// Schema::create('pengiriman', function (Blueprint $table) {
//     $table->id();
//     $table->string('resi')->unique();
//     $table->unsignedBigInteger('kurir_id')->nullable();
//     $table->string('nama_penerima');
//     $table->text('alamat');
//     $table->string('status_terakhir')->nullable();
//     $table->timestamps();
// });

}


    /**
     * Rollback migration.
     */
    // public function down(): void
    // {
    //     Schema::dropIfExists('pengiriman');
    // }
    public function down()
    {
        Schema::table('pengiriman', function (Blueprint $table) {
            $table->dropForeign(['kurir_id']);
            $table->dropColumn('kurir_id');
        });
    }
};
