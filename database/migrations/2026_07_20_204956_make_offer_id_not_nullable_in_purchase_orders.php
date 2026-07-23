<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Bỏ nullable khỏi offer_id trong purchase_orders.
     * Phải drop FK cũ (ON DELETE SET NULL) trước, đổi NOT NULL, rồi add lại FK.
     */
    public function up(): void
    {
        Schema::table('purchase_orders', function (Blueprint $table) {
            // Bước 1: drop FK cũ có ON DELETE SET NULL
            $table->dropForeign(['offer_id']);

            // Bước 2: đổi cột thành NOT NULL
            $table->unsignedBigInteger('offer_id')->nullable(false)->change();

            // Bước 3: add lại FK với ON DELETE RESTRICT (không cho xóa offer khi còn PO)
            $table->foreign('offer_id')
                  ->references('id')
                  ->on('supplier_offers')
                  ->onDelete('restrict');
        });
    }

    /**
     * Rollback: trả về nullable + ON DELETE SET NULL như cũ.
     */
    public function down(): void
    {
        Schema::table('purchase_orders', function (Blueprint $table) {
            $table->dropForeign(['offer_id']);
            $table->unsignedBigInteger('offer_id')->nullable()->change();
            $table->foreign('offer_id')
                  ->references('id')
                  ->on('supplier_offers')
                  ->onDelete('set null');
        });
    }
};
