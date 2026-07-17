<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Thêm cột created_by vào bảng footer.
 * Admin nào tạo/cập nhật thông tin footer thì lưu user_id vào đây.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('footer', function (Blueprint $table) {
            $table->unsignedBigInteger('created_by')->nullable()->after('email');

            $table->foreign('created_by')
                  ->references('id')->on('users')
                  ->onUpdate('cascade')
                  ->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::table('footer', function (Blueprint $table) {
            $table->dropForeign(['created_by']);
            $table->dropColumn('created_by');
        });
    }
};
