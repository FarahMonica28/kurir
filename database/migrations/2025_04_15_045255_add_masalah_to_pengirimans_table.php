<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('pengirimans', function (Blueprint $table) {
            $table->text('masalah')->nullable()->after('status');
            $table->timestamp('waktu_masalah')->nullable()->after('masalah');
        });
    }

    public function down(): void
    {
        Schema::table('pengirimans', function (Blueprint $table) {
            $table->dropColumn(['masalah', 'waktu_masalah']);
        });
    }
};
