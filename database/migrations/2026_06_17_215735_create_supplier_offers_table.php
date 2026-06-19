<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('supplier_offers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('manufacturer_id')->constrained('manufacturers')->onDelete('cascade');
            $table->string('offer_code')->unique();          // VD: OFR-20260617-001
            $table->text('note')->nullable();
            $table->enum('status', ['draft', 'submitted', 'accepted', 'rejected'])->default('submitted');
            $table->timestamp('submitted_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('supplier_offers');
    }
};
