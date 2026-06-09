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
        Schema::table('festival_product', function (Blueprint $table) {
            $table->foreign(['idFestival'], 'fk_fp_festival')->references(['id'])->on('festivals')->onUpdate('restrict')->onDelete('cascade');
            $table->foreign(['idProduct'], 'fk_fp_product')->references(['id'])->on('products')->onUpdate('restrict')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('festival_product', function (Blueprint $table) {
            $table->dropForeign('fk_fp_festival');
            $table->dropForeign('fk_fp_product');
        });
    }
};
