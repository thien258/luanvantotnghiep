<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Thêm cột created_by vào bảng title để liên kết với bảng users.
 * Admin nào tạo banner/slide thì lưu user_id vào đây.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('title', function (Blueprint $table) {
            // nullable: slide cũ không có created_by vẫn hợp lệ
            $table->unsignedBigInteger('created_by')->nullable()->after('descrip');

            $table->foreign('created_by')
                  ->references('id')->on('users')
                  ->onUpdate('cascade')
                  ->onDelete('set null'); // Xóa user → slide vẫn còn, created_by = null
        });
    }

    public function down(): void
    {
        Schema::table('title', function (Blueprint $table) {
            $table->dropForeign(['created_by']);
            $table->dropColumn('created_by');
        });
    }
};
