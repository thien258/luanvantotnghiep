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
        Schema::create('product_variants', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('idProduct')->index('fk_pv_product');
            $table->unsignedBigInteger('idVolume')->index('fk_pv_volume');
            $table->integer('price');
            $table->integer('stock');
            $table->timestamps();


            
              $table->foreign(['idProduct'], 'FK_pv_product')->references(['id'])->on('products')->onUpdate('restrict')->onDelete('restrict');
            $table->foreign(['idVolume'], 'fk_pv_volume')->references(['id'])->on('volumes')->onUpdate('restrict')->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_variants');
    }
};
