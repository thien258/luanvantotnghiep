<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('comments', function (Blueprint $table) {
            // Số sao đánh giá 1-5 (nullable để tương thích comment cũ không có sao)
            $table->tinyInteger('rating')->nullable()->after('chat');
            // Liên kết tới user đăng nhập (nullable để giữ comment cũ)
            $table->unsignedBigInteger('user_id')->nullable()->after('rating');
            // Liên kết tới order_detail để biết đã đánh giá sản phẩm trong đơn đó chưa
            $table->unsignedBigInteger('order_detail_id')->nullable()->after('user_id');
        });
    }

    public function down(): void
    {
        Schema::table('comments', function (Blueprint $table) {
            $table->dropColumn(['rating', 'user_id', 'order_detail_id']);
        });
    }
};
