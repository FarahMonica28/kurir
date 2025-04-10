<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('kurir', function (Blueprint $table) {
            $table->id('kurir_id'); // Primary Key (Auto Increment)
            // $table->string('name'); // Nama Kurir
            // $table->string('email')->unique(); // Email Kurir
            // $table->string('phone')->unique(); // No. Telepon
            // $table->string('photo')->nullable(); // Foto Profil
            $table->enum('status', ['aktif', 'nonaktif'])->default('aktif'); // Status Kurir
            $table->rememberToken();
            $table->timestamps(); // Created_at & Updated_at
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kurir');
    }
};
