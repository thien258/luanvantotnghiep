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
        Schema::table('carts', function (Blueprint $table) {
        

            $table->unsignedBigInteger('product_id')->after('idUser');

            $table->foreign('product_id')
                ->references('id')
                ->on('products')
                ->onUpdate('restrict')
                ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::table('carts', function (Blueprint $table) {
            $table->dropForeign('FK_carts_product');
            $table->dropColumn('product_id');

        });
    }
};
