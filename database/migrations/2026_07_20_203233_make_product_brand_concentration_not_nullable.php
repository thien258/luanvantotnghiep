<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Bỏ nullable khỏi idBrand và idConcentration trong bảng products.
     * Bắt buộc mỗi sản phẩm phải có thương hiệu và nồng độ.
     */
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            // Đổi từ nullable → NOT NULL (giữ nguyên index FK)
            $table->unsignedBigInteger('idBrand')->nullable(false)->change();
            $table->unsignedBigInteger('idConcentration')->nullable(false)->change();
        });
    }

    /**
     * Rollback: cho phép nullable trở lại.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->unsignedBigInteger('idBrand')->nullable()->change();
            $table->unsignedBigInteger('idConcentration')->nullable()->change();
        });
    }
};
