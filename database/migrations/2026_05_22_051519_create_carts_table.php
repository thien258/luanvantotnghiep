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
        Schema::create('carts', function (Blueprint $table) {
            $table->bigInteger('id', true);
            $table->integer('quantity')->default(1);
            $table->unsignedBigInteger('idUser')->index('fk_carts_user');
            $table->unsignedBigInteger('idPV')->index('fk_carts_pv');
            $table->timestamps();

             $table->foreign(['idPV'], 'FK_carts_pv')->references(['id'])->on('product_variants')->onUpdate('restrict')->onDelete('restrict');
            $table->foreign(['idUser'], 'FK_carts_user')->references(['id'])->on('users')->onUpdate('restrict')->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('carts');
    }
};
