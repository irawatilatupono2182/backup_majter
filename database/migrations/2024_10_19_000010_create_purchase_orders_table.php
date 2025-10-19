<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('purchase_orders', function (Blueprint $table) {
            $table->uuid('po_id')->primary();
            $table->uuid('company_id');
            $table->uuid('ph_id')->nullable();
            $table->uuid('supplier_id');
            $table->string('po_number', 50);
            $table->enum('type', ['PPN', 'Non-PPN']);
            $table->date('order_date');
            $table->date('expected_delivery')->nullable();
            $table->enum('status', ['Pending', 'Confirmed', 'Partial', 'Completed', 'Cancelled'])->default('Pending');
            $table->text('notes')->nullable();
            $table->uuid('created_by')->nullable();
            $table->timestamps();
            $table->softDeletes();
            
            $table->unique(['company_id', 'po_number'], 'uk_po_number');
            $table->foreign('company_id')->references('company_id')->on('companies')->onDelete('cascade');
            $table->foreign('ph_id')->references('ph_id')->on('price_quotations');
            $table->foreign('supplier_id')->references('supplier_id')->on('suppliers');
            $table->foreign('created_by')->references('id')->on('users');
            $table->index(['company_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('purchase_orders');
    }
};