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
        Schema::create('suppliers', function (Blueprint $table) {
            $table->uuid('supplier_id')->primary();
            $table->uuid('company_id');
            $table->string('supplier_code', 50);
            $table->string('name');
            $table->enum('type', ['Local', 'Import'])->comment('Local atau Import');
            $table->text('address');
            $table->string('phone', 30)->nullable();
            $table->string('email', 100)->nullable();
            $table->string('contact_person', 100)->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();
            
            $table->unique(['company_id', 'supplier_code'], 'uk_supplier_code');
            $table->foreign('company_id')->references('company_id')->on('companies')->onDelete('cascade');
            $table->index('company_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('suppliers');
    }
};