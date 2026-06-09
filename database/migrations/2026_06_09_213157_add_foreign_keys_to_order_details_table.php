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
        Schema::table('order_details', function (Blueprint $table) {
            $table->foreign(['idOrder'], 'fk_detail_order')->references(['id'])->on('orders')->onUpdate('restrict')->onDelete('restrict');
            $table->foreign(['idProduct'], 'fk_detail_product')->references(['id'])->on('products')->onUpdate('restrict')->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('order_details', function (Blueprint $table) {
            $table->dropForeign('fk_detail_order');
            $table->dropForeign('fk_detail_product');
        });
    }
};
