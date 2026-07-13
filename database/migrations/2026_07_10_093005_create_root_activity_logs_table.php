<?php

use Illuminate\Database\Migrations\Migration;   // Class base cho mọi migration
use Illuminate\Database\Schema\Blueprint;       // Dùng để định nghĩa cấu trúc bảng (cột, index...)
use Illuminate\Support\Facades\Schema;          // Facade để gọi các lệnh tạo/xóa bảng

// Migration dạng anonymous class — Laravel 9+ không cần đặt tên class
return new class extends Migration
{
    /**
     * up() — Chạy khi gõ: php artisan migrate
     * Tạo bảng root_activity_logs để lưu mọi thao tác của tài khoản root.
     */
    public function up(): void
    {
        Schema::create('root_activity_logs', function (Blueprint $table) {

            // id: khóa chính tự tăng (BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY)
            $table->id();

            // user_id: ID của tài khoản root đã thao tác
            // Dùng unsignedBigInteger thay vì foreignId để không tạo FK constraint
            // → tránh lỗi khi xóa user sau khi log đã tồn tại
            $table->unsignedBigInteger('user_id');

            // user_name: Tên hiển thị của root tại thời điểm thao tác
            // Lưu snapshot thay vì join về sau → log vẫn đọc được dù user bị đổi tên / xóa
            $table->string('user_name');

            // user_email: Email của root tại thời điểm thao tác
            // Cùng lý do snapshot với user_name ở trên
            $table->string('user_email');

            // action: Mô tả hành động tiếng Việt dễ đọc
            // Vd: "Xóa user #5", "Duyệt nhập kho #12", "Cập nhật trạng thái đơn hàng #99"
            // Middleware LogRootActivity tạo chuỗi này bằng cách map URL → description
            $table->string('action');

            // created_at: Thời điểm thao tác được ghi lại
            // useCurrent() → DB tự điền NOW() nếu không truyền giá trị
            // Không tạo updated_at vì log không bao giờ bị sửa
            $table->timestamp('created_at')->useCurrent();

        });
    }

    /**
     * down() — Chạy khi gõ: php artisan migrate:rollback
     * Xóa bảng root_activity_logs để hoàn tác migration.
     *
     * dropIfExists thay vì drop → không báo lỗi nếu bảng không tồn tại
     */
    public function down(): void
    {
        Schema::dropIfExists('root_activity_logs');
    }
};
