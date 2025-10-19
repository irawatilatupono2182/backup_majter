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
        Schema::create('price_quotations', function (Blueprint $table) {
            $table->uuid('ph_id')->primary();
            $table->uuid('company_id');
            $table->uuid('supplier_id');
            $table->string('quotation_number', 50);
            $table->enum('type', ['PPN', 'Non-PPN'])->comment('PPN atau Non-PPN');
            $table->date('quotation_date');
            $table->date('valid_until')->nullable();
            $table->enum('status', ['Draft', 'Sent', 'Accepted', 'Rejected'])->default('Draft');
            $table->text('notes')->nullable();
            $table->uuid('created_by')->nullable();
            $table->timestamps();
            $table->softDeletes();
            
            $table->unique(['company_id', 'quotation_number'], 'uk_ph_number');
            $table->foreign('company_id')->references('company_id')->on('companies')->onDelete('cascade');
            $table->foreign('supplier_id')->references('supplier_id')->on('suppliers');
            $table->foreign('created_by')->references('id')->on('users');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('price_quotations');
    }
};