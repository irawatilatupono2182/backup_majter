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
        Schema::create('payables', function (Blueprint $table) {
            $table->uuid('payable_id')->primary();
            $table->uuid('company_id');
            $table->uuid('supplier_id');
            
            // Reference type: 'po' or 'manual'
            $table->enum('reference_type', ['po', 'manual'])->default('manual');
            
            // For PO reference
            $table->uuid('purchase_order_id')->nullable();
            
            // For manual reference
            $table->string('reference_number')->nullable();
            $table->text('reference_description')->nullable();
            
            // Payable details
            $table->string('payable_number')->unique();
            $table->date('payable_date');
            $table->date('due_date');
            $table->decimal('amount', 15, 2);
            $table->decimal('paid_amount', 15, 2)->default(0);
            $table->decimal('remaining_amount', 15, 2);
            
            // Status: 'unpaid', 'partial', 'paid', 'overdue'
            $table->enum('status', ['unpaid', 'partial', 'paid', 'overdue'])->default('unpaid');
            
            // File upload for proof/evidence
            $table->string('attachment_path')->nullable();
            $table->string('attachment_filename')->nullable();
            
            // Additional info
            $table->text('notes')->nullable();
            $table->uuid('created_by');
            $table->uuid('updated_by')->nullable();
            
            $table->timestamps();
            $table->softDeletes();
            
            // Foreign keys
            $table->foreign('company_id')->references('company_id')->on('companies')->onDelete('cascade');
            $table->foreign('supplier_id')->references('supplier_id')->on('suppliers')->onDelete('cascade');
            $table->foreign('purchase_order_id')->references('po_id')->on('purchase_orders')->onDelete('set null');
            $table->foreign('created_by')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('updated_by')->references('id')->on('users')->onDelete('set null');
            
            // Indexes
            $table->index('company_id');
            $table->index('supplier_id');
            $table->index('purchase_order_id');
            $table->index('status');
            $table->index('due_date');
            $table->index(['company_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payables');
    }
};
