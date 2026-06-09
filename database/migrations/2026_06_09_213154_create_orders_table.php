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
        Schema::create('orders', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('idUser')->index('fk_orders_user');
            $table->string('fullname')->nullable();
            $table->string('phone')->nullable();
            $table->string('address');
            $table->string('payment_method')->default('CREDIT CARD');
            $table->integer('total_price')->default(0);
            $table->integer('status')->default(0);
            $table->text('note')->nullable();
            $table->string('tracking_code')->nullable();
            $table->timestamp('created_at')->nullable()->useCurrent();
            $table->timestamp('updated_at')->useCurrentOnUpdate()->nullable()->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
