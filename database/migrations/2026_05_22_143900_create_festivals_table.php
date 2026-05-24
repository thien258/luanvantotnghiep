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
        Schema::create('festivals', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name',150);
            $table->integer('discount')->default(0);
                $table->integer('status')->default(1);
                $table->date('start_date');
                $table->date('end_date');

            $table->timestamps();
        });
        Schema::create('festival_product', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('idFestival')->nullable()->index('fk_fp_festival');
            $table->foreign(['idFestival'], 'fk_fp_festival')->references(['id'])->on('festivals')->onUpdate('restrict')->onDelete('cascade');
            $table->unsignedBigInteger('idProduct')->nullable()->index('fk_fp_product');
            $table->foreign(['idProduct'], 'fk_fp_product')->references(['id'])->on('products')->onUpdate('restrict')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('festival_product');
        Schema::dropIfExists('festivals');
    }
};
