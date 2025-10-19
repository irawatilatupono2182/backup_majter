<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('invoices', function (Blueprint $table) {
            $table->uuid('invoice_id')->primary();
            $table->uuid('company_id');
            $table->uuid('customer_id');
            $table->uuid('sj_id')->nullable();
            $table->string('invoice_number', 50);
            $table->enum('type', ['PPN', 'Non-PPN']);
            $table->date('invoice_date');
            $table->date('due_date');
            $table->decimal('total_amount', 18, 2)->default(0);
            $table->decimal('ppn_amount', 18, 2)->default(0);
            $table->decimal('grand_total', 18, 2)->default(0);
            $table->enum('status', ['Unpaid', 'Partial', 'Paid', 'Overdue', 'Cancelled'])->default('Unpaid');
            $table->text('notes')->nullable();
            $table->uuid('created_by')->nullable();
            $table->timestamps();
            $table->softDeletes();
            
            $table->unique(['company_id', 'invoice_number'], 'uk_invoice_number');
            $table->foreign('company_id')->references('company_id')->on('companies')->onDelete('cascade');
            $table->foreign('customer_id')->references('customer_id')->on('customers');
            $table->foreign('sj_id')->references('sj_id')->on('delivery_notes');
            $table->foreign('created_by')->references('id')->on('users');
            $table->index(['company_id', 'status']);
            $table->index('due_date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('invoices');
    }
};