<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->uuid('payment_id')->primary();
            $table->uuid('company_id');
            $table->uuid('invoice_id');
            $table->uuid('customer_id');
            $table->date('payment_date');
            $table->decimal('amount', 18, 2);
            $table->string('payment_method', 50)->comment('Cash, Transfer, QRIS, dll');
            $table->string('reference_number', 100)->nullable();
            $table->text('notes')->nullable();
            $table->uuid('created_by')->nullable();
            $table->timestamps();
            $table->softDeletes();
            
            $table->foreign('company_id')->references('company_id')->on('companies')->onDelete('cascade');
            $table->foreign('invoice_id')->references('invoice_id')->on('invoices');
            $table->foreign('customer_id')->references('customer_id')->on('customers');
            $table->foreign('created_by')->references('id')->on('users');
            $table->index('invoice_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};