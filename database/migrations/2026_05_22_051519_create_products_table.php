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
        Schema::create('products', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('title', 250);
            $table->text('decription');
            $table->text('image');
            $table->unsignedBigInteger('idCategory')->default(23)->index('fk_product_category');
            $table->unsignedBigInteger('idBrand')->nullable()->index('fk_product_brand');
            $table->text('status');
            $table->unsignedBigInteger('idConcentration')->nullable()->index('fk_product_concentration');
            $table->timestamps();


            
             $table->foreign(['idBrand'], 'fk_product_brand')->references(['id'])->on('brands')->onUpdate('restrict')->onDelete('restrict');
            $table->foreign(['idCategory'], 'fk_product_category')->references(['id'])->on('categories')->onUpdate('restrict')->onDelete('restrict');
            $table->foreign(['idConcentration'], 'fk_product_concentration')->references(['id'])->on('concentrations')->onUpdate('restrict')->onDelete('restrict');
        });
       
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
