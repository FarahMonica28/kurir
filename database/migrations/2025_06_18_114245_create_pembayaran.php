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
        Schema::create('pembayaran', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('transaksii_id');
            $table->string('checkout_link');
            // $table->string('eksternal_id');
            $table->double('price');
            $table->string('customer');
            $table->string('customer_email');
            $table->string('status');
            $table->timestamps();
            $table->foreign('transaksii_id')->references('id')->on('transaksii');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pembayaran');
    }
};
