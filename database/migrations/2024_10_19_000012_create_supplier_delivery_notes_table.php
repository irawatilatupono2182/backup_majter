<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('supplier_delivery_notes', function (Blueprint $table) {
            $table->uuid('sp_id')->primary();
            $table->uuid('company_id');
            $table->uuid('po_id')->nullable();
            $table->uuid('supplier_id');
            $table->string('sp_number', 50);
            $table->date('delivery_date');
            $table->enum('status', ['Received', 'Partial', 'Damaged', 'Rejected'])->default('Received');
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();
            
            $table->foreign('company_id')->references('company_id')->on('companies')->onDelete('cascade');
            $table->foreign('po_id')->references('po_id')->on('purchase_orders');
            $table->foreign('supplier_id')->references('supplier_id')->on('suppliers');
            $table->unique(['company_id', 'sp_number'], 'uk_sp_number');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('supplier_delivery_notes');
    }
};