<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Chuyển bảng title và footer từ MyISAM sang InnoDB
     * để hỗ trợ FK constraint với bảng users.
     * MyISAM không hỗ trợ foreign key nên migration trước
     * đã thêm cột created_by nhưng FK không được tạo thực tế.
     */
    public function up(): void
    {
        // Chuyển engine + tạo lại FK
        DB::statement('ALTER TABLE `title` ENGINE = InnoDB');
        DB::statement('ALTER TABLE `footer` ENGINE = InnoDB');

        // Thêm FK (đã bị bỏ qua lúc migration trước do MyISAM)
        DB::statement('
            ALTER TABLE `title`
            ADD CONSTRAINT `title_created_by_foreign`
            FOREIGN KEY (`created_by`) REFERENCES `users`(`id`)
            ON UPDATE CASCADE ON DELETE SET NULL
        ');

        DB::statement('
            ALTER TABLE `footer`
            ADD CONSTRAINT `footer_created_by_foreign`
            FOREIGN KEY (`created_by`) REFERENCES `users`(`id`)
            ON UPDATE CASCADE ON DELETE SET NULL
        ');
    }

    /**
     * Rollback: xóa FK và chuyển về MyISAM.
     */
    public function down(): void
    {
        DB::statement('ALTER TABLE `title` DROP FOREIGN KEY `title_created_by_foreign`');
        DB::statement('ALTER TABLE `footer` DROP FOREIGN KEY `footer_created_by_foreign`');
        DB::statement('ALTER TABLE `title` ENGINE = MyISAM');
        DB::statement('ALTER TABLE `footer` ENGINE = MyISAM');
    }
};
