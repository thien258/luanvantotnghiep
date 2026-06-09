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
        Schema::table('products', function (Blueprint $table) {
            $table->foreign(['idBrand'], 'fk_product_brand')->references(['id'])->on('brands')->onUpdate('restrict')->onDelete('restrict');
            $table->foreign(['idCategory'], 'fk_product_category')->references(['id'])->on('categories')->onUpdate('restrict')->onDelete('restrict');
            $table->foreign(['idConcentration'], 'fk_product_concentration')->references(['id'])->on('concentrations')->onUpdate('restrict')->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropForeign('fk_product_brand');
            $table->dropForeign('fk_product_category');
            $table->dropForeign('fk_product_concentration');
        });
    }
};
