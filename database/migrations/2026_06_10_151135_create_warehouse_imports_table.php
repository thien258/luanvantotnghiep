<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('warehouse_imports', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('file_path');           // Đường dẫn file lưu trong storage
            $table->string('original_name');       // Tên file gốc
            $table->string('supplier')->nullable(); // Nhà cung cấp
            $table->text('note')->nullable();       // Ghi chú
            $table->unsignedBigInteger('uploaded_by')->nullable(); // ID user upload
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->unsignedBigInteger('reviewed_by')->nullable(); // Admin duyệt
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamps();

            $table->foreign('uploaded_by')->references('id')->on('users')->onDelete('set null');
            $table->foreign('reviewed_by')->references('id')->on('users')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('warehouse_imports');
    }
};
