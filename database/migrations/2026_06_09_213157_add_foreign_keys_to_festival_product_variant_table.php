<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('festival_product_variant', function (Blueprint $table) {
            $table->foreign(['festival_id'])->references(['id'])->on('festivals')->onUpdate('no action')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('festival_product_variant', function (Blueprint $table) {
            $table->dropForeign('festival_product_variant_festival_id_foreign');
        });
    }
};
