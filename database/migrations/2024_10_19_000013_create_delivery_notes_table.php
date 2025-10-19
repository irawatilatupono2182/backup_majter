<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('delivery_notes', function (Blueprint $table) {
            $table->uuid('sj_id')->primary();
            $table->uuid('company_id');
            $table->uuid('customer_id');
            $table->string('sj_number', 50);
            $table->enum('type', ['PPN', 'Non-PPN', 'Supplier']);
            $table->date('delivery_date');
            $table->enum('status', ['Draft', 'Sent', 'Completed'])->default('Draft');
            $table->text('notes')->nullable();
            $table->uuid('created_by')->nullable();
            $table->timestamps();
            $table->softDeletes();
            
            $table->unique(['company_id', 'sj_number'], 'uk_sj_number');
            $table->foreign('company_id')->references('company_id')->on('companies')->onDelete('cascade');
            $table->foreign('customer_id')->references('customer_id')->on('customers');
            $table->foreign('created_by')->references('id')->on('users');
            $table->index('company_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('delivery_notes');
    }
};