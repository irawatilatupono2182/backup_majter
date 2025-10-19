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
        Schema::create('stocks', function (Blueprint $table) {
            $table->uuid('stock_id')->primary();
            $table->uuid('company_id');
            $table->uuid('product_id');
            $table->string('batch_number')->nullable();
            $table->decimal('quantity', 15, 2)->default(0);
            $table->decimal('reserved_quantity', 15, 2)->default(0); // Reserved for pending orders
            $table->decimal('available_quantity', 15, 2)->default(0); // Available = quantity - reserved
            $table->decimal('minimum_stock', 15, 2)->default(0);
            $table->decimal('unit_cost', 15, 2)->nullable(); // Last purchase cost
            $table->date('expiry_date')->nullable();
            $table->string('location')->nullable(); // Warehouse location
            $table->text('notes')->nullable();
            $table->uuid('created_by')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('company_id')->references('company_id')->on('companies')->onDelete('cascade');
            $table->foreign('product_id')->references('product_id')->on('products')->onDelete('cascade');
            $table->foreign('created_by')->references('id')->on('users')->onDelete('set null');

            $table->index(['company_id', 'product_id']);
            $table->index(['company_id', 'batch_number']);
            $table->index(['expiry_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stocks');
    }
};
