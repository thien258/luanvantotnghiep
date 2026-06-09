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
        Schema::create('festival_product', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('idFestival')->nullable()->index('fk_fp_festival');
            $table->unsignedBigInteger('idProduct')->nullable()->index('fk_fp_product');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('festival_product');
    }
};
