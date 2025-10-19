<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('delivery_note_items', function (Blueprint $table) {
            $table->uuid('sj_item_id')->primary();
            $table->uuid('sj_id');
            $table->uuid('product_id');
            $table->decimal('qty', 15, 4);
            $table->string('unit', 20);
            $table->decimal('unit_price', 18, 2);
            $table->decimal('discount_percent', 5, 2)->default(0);
            $table->decimal('subtotal', 18, 2);
            $table->timestamps();
            
            $table->foreign('sj_id')->references('sj_id')->on('delivery_notes')->onDelete('cascade');
            $table->foreign('product_id')->references('product_id')->on('products');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('delivery_note_items');
    }
};