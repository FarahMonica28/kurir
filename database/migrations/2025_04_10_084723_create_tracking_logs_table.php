<?php

// database/migrations/xxxx_xx_xx_create_tracking_logs_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('tracking_log', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('id');
            $table->string('status'); // contoh: "dikemas", "dikirim", "diterima"
            $table->text('catatan')->nullable(); // opsional: detail tambahan
            $table->timestamp('waktu')->nullable(); // waktu status ini terjadi
            $table->timestamps();
            $table->foreign('id')->references('id')->on('pengiriman')->onDelete('cascade');
        });
    }

    public function down(): void {
        Schema::dropIfExists('tracking_log');
    }
};
