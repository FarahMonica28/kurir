<?php

// database/migrations/xxxx_xx_xx_xxxxxx_add_status_pembayaran_to_transaksii_table.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::table('transaksii', function (Blueprint $table) {
            $table->string('status_pembayaran')->default('pending')->after('status'); // atau sesuaikan posisi kolom
        });
    }

    public function down(): void {
        Schema::table('transaksii', function (Blueprint $table) {
            $table->dropColumn('status_pembayaran');
        });
    }
};
