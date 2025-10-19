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
        Schema::create('inventory_batches', function (Blueprint $table) {
            $table->uuid('batch_id')->primary();
            $table->uuid('company_id');
            $table->uuid('product_id');
            $table->uuid('supplier_id')->nullable();
            $table->enum('reference_type', ['PO', 'Manual', 'Adjustment'])->nullable();
            $table->uuid('reference_id')->nullable();
            $table->date('received_date');
            $table->date('expiry_date')->nullable();
            $table->decimal('initial_qty', 15, 4);
            $table->decimal('remaining_qty', 15, 4)->default(0);
            $table->string('unit', 20);
            $table->decimal('purchase_price', 18, 4)->default(0);
            $table->decimal('additional_cost', 18, 2)->default(0);
            $table->decimal('hpp_per_unit', 18, 4)->default(0);
            $table->enum('status', ['STOCK', 'USED', 'DAMAGED', 'EXPIRED'])->default('STOCK');
            $table->timestamps();
            $table->softDeletes();
            
            $table->foreign('company_id')->references('company_id')->on('companies')->onDelete('cascade');
            $table->foreign('product_id')->references('product_id')->on('products')->onDelete('cascade');
            $table->foreign('supplier_id')->references('supplier_id')->on('suppliers')->onDelete('set null');
            $table->index(['company_id', 'product_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inventory_batches');
    }
};