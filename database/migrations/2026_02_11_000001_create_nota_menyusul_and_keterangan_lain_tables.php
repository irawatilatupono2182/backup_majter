<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Table for Nota Menyusul
        Schema::create('nota_menyusuls', function (Blueprint $table) {
            $table->uuid('nm_id')->primary();
            $table->uuid('company_id');
            $table->uuid('customer_id');
            $table->uuid('sj_id')->nullable();
            $table->uuid('converted_to_invoice_id')->nullable();
            $table->timestamp('converted_at')->nullable();
            $table->string('nota_number', 50)->unique();
            $table->string('po_number', 100)->nullable();
            $table->enum('type', ['PPN', 'Non-PPN'])->default('PPN');
            $table->date('nota_date');
            $table->date('estimated_payment_date')->nullable();
            $table->decimal('total_amount', 15, 2)->default(0);
            $table->decimal('ppn_amount', 15, 2)->default(0);
            $table->decimal('grand_total', 15, 2)->default(0);
            $table->enum('status', ['Draft', 'Approved', 'Converted'])->default('Draft');
            $table->text('notes')->nullable();
            $table->text('payment_notes')->nullable();
            $table->uuid('created_by')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('company_id')->references('company_id')->on('companies')->onDelete('cascade');
            $table->foreign('customer_id')->references('customer_id')->on('customers')->onDelete('cascade');
            $table->foreign('sj_id')->references('sj_id')->on('delivery_notes')->onDelete('set null');
            $table->foreign('converted_to_invoice_id')->references('invoice_id')->on('invoices')->onDelete('set null');
            $table->foreign('created_by')->references('id')->on('users')->onDelete('set null');
        });

        // Table for Nota Menyusul Items
        Schema::create('nota_menyusul_items', function (Blueprint $table) {
            $table->uuid('item_id')->primary();
            $table->uuid('nm_id');
            $table->uuid('product_id');
            $table->decimal('qty', 10, 2);
            $table->string('unit', 20);
            $table->decimal('unit_price', 15, 2);
            $table->decimal('discount_percent', 5, 2)->default(0);
            $table->decimal('subtotal', 15, 2);
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->foreign('nm_id')->references('nm_id')->on('nota_menyusuls')->onDelete('cascade');
            $table->foreign('product_id')->references('product_id')->on('products')->onDelete('cascade');
        });

        // Table for Keterangan Lain
        Schema::create('keterangan_lains', function (Blueprint $table) {
            $table->uuid('kl_id')->primary();
            $table->uuid('company_id');
            $table->uuid('customer_id');
            $table->string('document_number', 50)->unique();
            $table->enum('document_category', ['Surat Jalan Tambahan', 'Nota Pengganti', 'Dokumen Koreksi', 'Lainnya'])->default('Lainnya');
            $table->string('reference_document', 100)->nullable();
            $table->string('reference_type')->nullable();
            $table->uuid('reference_id')->nullable();
            $table->enum('type', ['PPN', 'Non-PPN'])->default('PPN');
            $table->date('document_date');
            $table->decimal('total_amount', 15, 2)->default(0);
            $table->decimal('ppn_amount', 15, 2)->default(0);
            $table->decimal('grand_total', 15, 2)->default(0);
            $table->enum('status', ['Draft', 'Approved', 'Closed'])->default('Draft');
            $table->text('description')->nullable();
            $table->text('notes')->nullable();
            $table->uuid('created_by')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('company_id')->references('company_id')->on('companies')->onDelete('cascade');
            $table->foreign('customer_id')->references('customer_id')->on('customers')->onDelete('cascade');
            $table->foreign('created_by')->references('id')->on('users')->onDelete('set null');
            
            $table->index(['reference_type', 'reference_id']);
        });

        // Table for Keterangan Lain Items
        Schema::create('keterangan_lain_items', function (Blueprint $table) {
            $table->uuid('item_id')->primary();
            $table->uuid('kl_id');
            $table->uuid('product_id');
            $table->decimal('qty', 10, 2);
            $table->string('unit', 20);
            $table->decimal('unit_price', 15, 2);
            $table->decimal('subtotal', 15, 2);
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->foreign('kl_id')->references('kl_id')->on('keterangan_lains')->onDelete('cascade');
            $table->foreign('product_id')->references('product_id')->on('products')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('keterangan_lain_items');
        Schema::dropIfExists('keterangan_lains');
        Schema::dropIfExists('nota_menyusul_items');
        Schema::dropIfExists('nota_menyusuls');
    }
};
