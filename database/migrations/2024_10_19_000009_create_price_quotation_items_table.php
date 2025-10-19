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
        Schema::create('price_quotation_items', function (Blueprint $table) {
            $table->uuid('ph_item_id')->primary();
            $table->uuid('ph_id');
            $table->uuid('product_id');
            $table->decimal('qty', 15, 4);
            $table->string('unit', 20);
            $table->decimal('unit_price', 18, 2);
            $table->decimal('discount_percent', 5, 2)->default(0);
            $table->decimal('subtotal', 18, 2);
            $table->timestamps();
            
            $table->foreign('ph_id')->references('ph_id')->on('price_quotations')->onDelete('cascade');
            $table->foreign('product_id')->references('product_id')->on('products');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('price_quotation_items');
    }
};