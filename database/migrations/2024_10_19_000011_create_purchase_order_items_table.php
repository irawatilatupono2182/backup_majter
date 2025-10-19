<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('purchase_order_items', function (Blueprint $table) {
            $table->uuid('po_item_id')->primary();
            $table->uuid('po_id');
            $table->uuid('product_id');
            $table->decimal('qty_ordered', 15, 4);
            $table->decimal('qty_received', 15, 4)->default(0);
            $table->string('unit', 20);
            $table->decimal('unit_price', 18, 2);
            $table->decimal('discount_percent', 5, 2)->default(0);
            $table->decimal('subtotal', 18, 2);
            $table->timestamps();
            
            $table->foreign('po_id')->references('po_id')->on('purchase_orders')->onDelete('cascade');
            $table->foreign('product_id')->references('product_id')->on('products');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('purchase_order_items');
    }
};