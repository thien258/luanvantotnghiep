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
        //
        Schema::table('products', function (Blueprint $table) {
        if (!Schema::hasColumn('products', 'volume')) {
            $table->string('volume')->nullable();
        }
        if (!Schema::hasColumn('products', 'price')) {
            $table->integer('price')->default(0);
        }
        if (!Schema::hasColumn('products', 'quantity')) {
            $table->integer('quantity')->default(0);
        }
    });

    // 2. Xóa khóa ngoại ở các bảng phụ trỏ tới product_variants
    // Xóa liên kết từ bảng carts (nếu còn)
    Schema::table('carts', function (Blueprint $table) {
        if (Schema::hasColumn('carts', 'product_variant_id')) {
            $table->dropForeign(['product_variant_id']);
            $table->dropColumn('product_variant_id');
        }
    });

    // Xóa liên kết từ bảng festival_product_variant
    Schema::table('festival_product_variant', function (Blueprint $table) {
        if (Schema::hasColumn('festival_product_variant', 'product_variant_id')) {
            $table->dropForeign(['product_variant_id']);
            $table->dropColumn('product_variant_id');
        }
    });

    // 3. Bây giờ mới xóa các bảng phụ
    Schema::dropIfExists('product_variants');
    Schema::dropIfExists('volumes');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn(['volume', 'price', 'quantity']);
        });
    }
};
