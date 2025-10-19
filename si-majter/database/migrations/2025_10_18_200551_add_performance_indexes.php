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
        // Most indexes already exist from table creation - just add a few specific ones
        Schema::table('stocks', function (Blueprint $table) {
            $table->index(['expiry_date'], 'stocks_expiry_date_idx');
        });

        Schema::table('stock_movements', function (Blueprint $table) {
            $table->index(['movement_date'], 'stock_movements_date_idx');
            $table->index(['reference_type', 'reference_id'], 'stock_movements_reference_idx');
        });

        Schema::table('customers', function (Blueprint $table) {
            $table->index(['company_id', 'is_active']);
            $table->index(['company_id', 'name']);
            $table->index(['email']);
            $table->index(['tax_number']);
        });

        Schema::table('suppliers', function (Blueprint $table) {
            $table->index(['company_id', 'is_active']);
            $table->index(['company_id', 'name']);
            $table->index(['email']);
            $table->index(['tax_number']);
        });

        Schema::table('products', function (Blueprint $table) {
            $table->index(['company_id', 'is_active']);
            $table->index(['company_id', 'name']);
            $table->index(['company_id', 'code']);
            $table->index(['category']);
            $table->index(['product_type']);
        });

        Schema::table('price_quotations', function (Blueprint $table) {
            $table->index(['company_id', 'status']);
            $table->index(['company_id', 'customer_id']);
            $table->index(['quotation_date']);
            $table->index(['valid_until']);
            $table->index(['quotation_number']);
        });

        Schema::table('purchase_orders', function (Blueprint $table) {
            $table->index(['company_id', 'status']);
            $table->index(['company_id', 'supplier_id']);
            $table->index(['order_date']);
            $table->index(['expected_date']);
            $table->index(['po_number']);
        });

        Schema::table('delivery_notes', function (Blueprint $table) {
            $table->index(['company_id', 'status']);
            $table->index(['company_id', 'customer_id']);
            $table->index(['delivery_date']);
            $table->index(['sj_number']);
        });

        Schema::table('invoices', function (Blueprint $table) {
            $table->index(['company_id', 'status']);
            $table->index(['company_id', 'customer_id']);
            $table->index(['invoice_date']);
            $table->index(['due_date']);
            $table->index(['invoice_number']);
        });

        Schema::table('payments', function (Blueprint $table) {
            $table->index(['company_id', 'invoice_id']);
            $table->index(['payment_date']);
            $table->index(['payment_method']);
        });

        Schema::table('stocks', function (Blueprint $table) {
            $table->index(['company_id', 'product_id']);
            $table->index(['company_id', 'batch_number']);
            $table->index(['expiry_date']);
        });

        Schema::table('stock_movements', function (Blueprint $table) {
            $table->index(['company_id', 'product_id']);
            $table->index(['company_id', 'stock_id']);
            $table->index(['movement_date']);
            $table->index(['movement_type']);
            $table->index(['reference_type', 'reference_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Drop indexes in reverse order
        Schema::table('stock_movements', function (Blueprint $table) {
            $table->dropIndex(['company_id', 'product_id']);
            $table->dropIndex(['company_id', 'stock_id']);
            $table->dropIndex(['movement_date']);
            $table->dropIndex(['movement_type']);
            $table->dropIndex(['reference_type', 'reference_id']);
        });

        Schema::table('stocks', function (Blueprint $table) {
            $table->dropIndex(['company_id', 'product_id']);
            $table->dropIndex(['company_id', 'batch_number']);
            $table->dropIndex(['expiry_date']);
        });

        Schema::table('payments', function (Blueprint $table) {
            $table->dropIndex(['company_id', 'invoice_id']);
            $table->dropIndex(['payment_date']);
            $table->dropIndex(['payment_method']);
        });

        Schema::table('invoices', function (Blueprint $table) {
            $table->dropIndex(['company_id', 'status']);
            $table->dropIndex(['company_id', 'customer_id']);
            $table->dropIndex(['invoice_date']);
            $table->dropIndex(['due_date']);
            $table->dropIndex(['invoice_number']);
        });

        Schema::table('delivery_notes', function (Blueprint $table) {
            $table->dropIndex(['company_id', 'status']);
            $table->dropIndex(['company_id', 'customer_id']);
            $table->dropIndex(['delivery_date']);
            $table->dropIndex(['sj_number']);
        });

        Schema::table('purchase_orders', function (Blueprint $table) {
            $table->dropIndex(['company_id', 'status']);
            $table->dropIndex(['company_id', 'supplier_id']);
            $table->dropIndex(['order_date']);
            $table->dropIndex(['expected_date']);
            $table->dropIndex(['po_number']);
        });

        Schema::table('price_quotations', function (Blueprint $table) {
            $table->dropIndex(['company_id', 'status']);
            $table->dropIndex(['company_id', 'customer_id']);
            $table->dropIndex(['quotation_date']);
            $table->dropIndex(['valid_until']);
            $table->dropIndex(['quotation_number']);
        });

        Schema::table('products', function (Blueprint $table) {
            $table->dropIndex(['company_id', 'is_active']);
            $table->dropIndex(['company_id', 'name']);
            $table->dropIndex(['company_id', 'code']);
            $table->dropIndex(['category']);
            $table->dropIndex(['product_type']);
        });

        Schema::table('suppliers', function (Blueprint $table) {
            $table->dropIndex(['company_id', 'is_active']);
            $table->dropIndex(['company_id', 'name']);
            $table->dropIndex(['email']);
            $table->dropIndex(['tax_number']);
        });

        Schema::table('customers', function (Blueprint $table) {
            $table->dropIndex(['company_id', 'is_active']);
            $table->dropIndex(['company_id', 'name']);
            $table->dropIndex(['email']);
            $table->dropIndex(['tax_number']);
        });

        Schema::table('stock_movements', function (Blueprint $table) {
            $table->dropIndex('stock_movements_date_idx');
            $table->dropIndex('stock_movements_reference_idx');
        });

        Schema::table('stocks', function (Blueprint $table) {
            $table->dropIndex('stocks_expiry_date_idx');
        });
    }
};
