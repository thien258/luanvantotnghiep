<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('supplier_offers', function (Blueprint $table) {
            // Liên kết báo giá NSX với yêu cầu thu mua (nullable — có thể chào tự do)
            $table->foreignId('request_id')
                  ->nullable()
                  ->after('manufacturer_id')
                  ->constrained('procurement_requests')
                  ->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::table('supplier_offers', function (Blueprint $table) {
            $table->dropForeign(['request_id']);
            $table->dropColumn('request_id');
        });
    }
};
