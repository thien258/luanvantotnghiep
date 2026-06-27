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
        Schema::table('warehouse_stock_logs', function (Blueprint $table) {
            // Thêm cột expiry_date để lưu ngày hết hạn của lô hàng nhập
            // nullable vì log export không có HSD, chỉ log import mới cần
            // after('reason') = đặt sau cột reason trong DB
            $table->date('expiry_date')->nullable()->after('reason');
        });
    }

    public function down(): void
    {
        Schema::table('warehouse_stock_logs', function (Blueprint $table) {
            // Rollback: xóa cột expiry_date nếu migrate:rollback
            $table->dropColumn('expiry_date');
        });
    }
};
