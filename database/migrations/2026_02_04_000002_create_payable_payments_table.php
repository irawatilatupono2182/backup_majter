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
        Schema::create('payable_payments', function (Blueprint $table) {
            $table->uuid('payment_id')->primary();
            $table->uuid('payable_id');
            $table->uuid('company_id');
            
            // Payment details
            $table->string('payment_number')->unique();
            $table->date('payment_date');
            $table->decimal('amount', 15, 2);
            
            // Payment method: 'cash', 'transfer', 'check', 'giro', 'other'
            $table->enum('payment_method', ['cash', 'transfer', 'check', 'giro', 'other'])->default('transfer');
            
            // Bank details (for transfer/check/giro)
            $table->string('bank_name')->nullable();
            $table->string('account_number')->nullable();
            $table->string('check_giro_number')->nullable();
            
            // File upload for payment proof
            $table->string('attachment_path')->nullable();
            $table->string('attachment_filename')->nullable();
            
            // Additional info
            $table->text('notes')->nullable();
            $table->uuid('created_by');
            $table->uuid('updated_by')->nullable();
            
            $table->timestamps();
            $table->softDeletes();
            
            // Foreign keys
            $table->foreign('payable_id')->references('payable_id')->on('payables')->onDelete('cascade');
            $table->foreign('company_id')->references('company_id')->on('companies')->onDelete('cascade');
            $table->foreign('created_by')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('updated_by')->references('id')->on('users')->onDelete('set null');
            
            // Indexes
            $table->index('payable_id');
            $table->index('company_id');
            $table->index('payment_date');
            $table->index(['payable_id', 'payment_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payable_payments');
    }
};
