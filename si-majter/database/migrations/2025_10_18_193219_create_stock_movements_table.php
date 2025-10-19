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
        Schema::create('stock_movements', function (Blueprint $table) {
            $table->uuid('stock_movement_id')->primary();
            $table->uuid('company_id');
            $table->uuid('product_id');
            $table->enum('movement_type', ['in', 'out', 'adjustment']);
            $table->decimal('quantity', 15, 2);
            $table->decimal('unit_cost', 15, 2)->nullable();
            $table->string('reference_type')->nullable(); // 'purchase_order', 'delivery_note', 'adjustment', etc.
            $table->uuid('reference_id')->nullable();
            $table->string('batch_number')->nullable();
            $table->date('expiry_date')->nullable();
            $table->text('notes')->nullable();
            $table->uuid('created_by');
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('company_id')->references('company_id')->on('companies');
            $table->foreign('product_id')->references('product_id')->on('products');
            $table->foreign('created_by')->references('id')->on('users');

            $table->index(['company_id', 'product_id']);
            $table->index(['reference_type', 'reference_id']);
            $table->index('movement_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stock_movements');
    }
};
