<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Mengubah price_quotations agar support 2 arah:
     * 1. PH untuk Customer (Sales - Penawaran dari kita ke customer)
     * 2. PH untuk Supplier (Purchasing - Minta penawaran dari supplier)
     */
    public function up(): void
    {
        Schema::table('price_quotations', function (Blueprint $table) {
            // Add polymorphic relation fields
            $table->string('entity_type', 50)->nullable()->after('company_id')
                ->comment('customer atau supplier');
            $table->uuid('entity_id')->nullable()->after('entity_type')
                ->comment('customer_id atau supplier_id');
            
            // Add customer_id explicitly for clarity
            $table->uuid('customer_id')->nullable()->after('entity_id');
            
            // Add foreign key for customer
            $table->foreign('customer_id')->references('customer_id')->on('customers');
            
            // Add index for polymorphic relation
            $table->index(['entity_type', 'entity_id'], 'idx_entity_polymorphic');
        });
        
        // Migrate existing data: all existing records assume supplier
        DB::statement("
            UPDATE price_quotations 
            SET entity_type = 'supplier',
                entity_id = supplier_id
            WHERE supplier_id IS NOT NULL
        ");
        
        // Now make supplier_id nullable (after data migration)
        // Using raw SQL to avoid issues with existing NULL values
        DB::statement('ALTER TABLE `price_quotations` MODIFY `supplier_id` char(36) NULL');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('price_quotations', function (Blueprint $table) {
            // Drop foreign keys first
            $table->dropForeign(['customer_id']);
            
            // Drop index
            $table->dropIndex('idx_entity_polymorphic');
            
            // Drop columns
            $table->dropColumn(['entity_type', 'entity_id', 'customer_id']);
        });
        
        // Restore supplier_id to NOT NULL if needed
        // Note: This might fail if there are NULL values
        // DB::statement('ALTER TABLE `price_quotations` MODIFY `supplier_id` char(36) NOT NULL');
    }
};
