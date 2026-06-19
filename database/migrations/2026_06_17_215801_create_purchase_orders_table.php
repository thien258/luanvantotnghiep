<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('purchase_orders', function (Blueprint $table) {
            $table->id();
            // offer_id nullable: admin có thể đặt hàng trực tiếp không qua báo giá
            $table->foreignId('offer_id')->nullable()->constrained('supplier_offers')->onDelete('set null');
            $table->foreignId('manufacturer_id')->constrained('manufacturers')->onDelete('cascade');
            $table->string('order_code')->unique();       
            $table->decimal('total_amount', 15, 2)->default(0);
            $table->enum('status', ['pending', 'confirmed', 'delivering', 'received', 'cancelled'])->default('pending');
            $table->date('expected_date')->nullable();
            $table->text('note')->nullable();
            $table->foreignId('created_by')->constrained('users')->onDelete('cascade');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('purchase_orders');
    }
};
