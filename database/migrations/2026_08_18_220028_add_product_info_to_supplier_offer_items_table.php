<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('supplier_offer_items', function (Blueprint $table) {
            $table->string('volume', 50)->nullable()->after('image');
            $table->string('concentration_text', 100)->nullable()->after('volume');
            $table->string('category_text', 100)->nullable()->after('concentration_text');
            $table->string('brand_text', 100)->nullable()->after('category_text');
        });
    }

    public function down(): void
    {
        Schema::table('supplier_offer_items', function (Blueprint $table) {
            $table->dropColumn(['volume', 'concentration_text', 'category_text', 'brand_text']);
        });
    }
};
