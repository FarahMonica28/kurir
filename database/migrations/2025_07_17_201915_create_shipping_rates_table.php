<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('shipping_rates', function (Blueprint $table) {
            $table->id();
            $table->integer('origin_id');
            $table->integer('destination_id');
            $table->integer('weight'); // dalam gram
            $table->string('courier', 10);
            $table->string('service_code', 20);
            $table->string('service_name')->nullable();
            $table->integer('cost');
            $table->string('etd')->nullable();
            $table->text('note')->nullable();
            $table->timestamps();
            $table->timestamp('expired_at')->nullable();
        });
    }


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('shipping_rates');
    }
};
