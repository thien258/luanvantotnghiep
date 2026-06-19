<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('supplier_offer_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('offer_id')->constrained('supplier_offers')->onDelete('cascade');
            // product_id nullable: NSX có thể chào sp chưa có trong hệ thống
            $table->foreignId('product_id')->nullable()->constrained('products')->onDelete('set null');
            $table->string('product_name');               // NSX tự nhập tên (dự phòng)
            $table->decimal('unit_price', 15, 2);         // Giá NSX chào — KHÔNG có qty (admin tự quyết)
            $table->text('note')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('supplier_offer_items');
    }
};
