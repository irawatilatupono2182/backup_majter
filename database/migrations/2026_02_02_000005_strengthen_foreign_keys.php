<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * ✅ CRITICAL FIX #7: Strengthen Foreign Key Constraints
     * This migration adds MISSING foreign keys and ensures data integrity
     */
    public function up(): void
    {
        // Skip this migration for SQLite - it uses MySQL-specific syntax
        if (DB::connection()->getDriverName() === 'sqlite') {
            return;
        }
        
        // Disable foreign key checks temporarily (MySQL only)
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        
        // 1. Check and add missing foreign keys to stock_movements
        if (Schema::hasTable('stock_movements')) {
            Schema::table('stock_movements', function (Blueprint $table) {
                // Add foreign key if not exists
                $foreignKeys = DB::select("
                    SELECT CONSTRAINT_NAME 
                    FROM information_schema.TABLE_CONSTRAINTS 
                    WHERE TABLE_SCHEMA = DATABASE()
                    AND TABLE_NAME = 'stock_movements'
                    AND CONSTRAINT_TYPE = 'FOREIGN KEY'
                ");
                
                $hasCompanyFK = collect($foreignKeys)->contains(fn($fk) => 
                    str_contains($fk->CONSTRAINT_NAME, 'company')
                );
                
                if (!$hasCompanyFK) {
                    $table->foreign('company_id')
                        ->references('company_id')
                        ->on('companies')
                        ->onDelete('cascade')
                        ->onUpdate('cascade');
                }
            });
        }
        
        // 2. Add composite unique constraints to prevent duplicates
        if (Schema::hasTable('payments')) {
            Schema::table('payments', function (Blueprint $table) {
                // Prevent duplicate payment with same reference number for same invoice
                $indexExists = DB::select("
                    SELECT INDEX_NAME 
                    FROM information_schema.STATISTICS 
                    WHERE TABLE_SCHEMA = DATABASE()
                    AND TABLE_NAME = 'payments'
                    AND INDEX_NAME = 'uk_invoice_reference'
                ");
                
                if (empty($indexExists)) {
                    $table->unique(['invoice_id', 'reference_number'], 'uk_invoice_reference');
                }
            });
        }
        
        // 3. Add check constraint for stock quantity (MySQL 8.0.16+)
        if (Schema::hasTable('stocks')) {
            try {
                DB::statement('
                    ALTER TABLE stocks 
                    ADD CONSTRAINT chk_quantity_non_negative 
                    CHECK (quantity >= 0)
                ');
            } catch (\Exception $e) {
                // MySQL version might not support CHECK constraints
                // Will be handled by application layer
            }
            
            try {
                DB::statement('
                    ALTER TABLE stocks 
                    ADD CONSTRAINT chk_available_calculation 
                    CHECK (available_quantity = quantity - reserved_quantity)
                ');
            } catch (\Exception $e) {
                // Handled by application layer
            }
        }
        
        // 4. Ensure invoice items have valid references
        if (Schema::hasTable('invoice_items')) {
            Schema::table('invoice_items', function (Blueprint $table) {
                $foreignKeys = DB::select("
                    SELECT CONSTRAINT_NAME 
                    FROM information_schema.TABLE_CONSTRAINTS 
                    WHERE TABLE_SCHEMA = DATABASE()
                    AND TABLE_NAME = 'invoice_items'
                    AND CONSTRAINT_TYPE = 'FOREIGN KEY'
                ");
                
                $constraints = collect($foreignKeys)->pluck('CONSTRAINT_NAME')->toArray();
                
                // Add product FK if missing with RESTRICT to prevent orphan items
                if (!in_array('invoice_items_product_id_foreign', $constraints)) {
                    $table->foreign('product_id')
                        ->references('product_id')
                        ->on('products')
                        ->onDelete('restrict'); // Cannot delete product if used in invoice
                }
            });
        }
        
        // 5. Add ON DELETE RESTRICT to critical relationships
        // This prevents accidental deletion of important data
        $criticalTables = [
            'invoices' => ['customer_id', 'customers'],
            'purchase_orders' => ['supplier_id', 'suppliers'],
            'delivery_notes' => ['customer_id', 'customers'],
        ];
        
        foreach ($criticalTables as $table => [$column, $referencedTable]) {
            if (Schema::hasTable($table)) {
                try {
                    $fkName = "{$table}_{$column}_foreign";
                    
                    // Check if FK exists first
                    $fkExists = DB::select("
                        SELECT CONSTRAINT_NAME 
                        FROM information_schema.TABLE_CONSTRAINTS 
                        WHERE TABLE_SCHEMA = DATABASE()
                        AND TABLE_NAME = '{$table}'
                        AND CONSTRAINT_NAME = '{$fkName}'
                        AND CONSTRAINT_TYPE = 'FOREIGN KEY'
                    ");
                    
                    // Drop existing foreign key if exists
                    if (!empty($fkExists)) {
                        DB::statement("ALTER TABLE {$table} DROP FOREIGN KEY {$fkName}");
                    }
                    
                    // Add new foreign key with RESTRICT
                    Schema::table($table, function (Blueprint $tableSchema) use ($column, $referencedTable) {
                        $tableSchema->foreign($column)
                            ->references($column)
                            ->on($referencedTable)
                            ->onDelete('restrict')
                            ->onUpdate('cascade');
                    });
                } catch (\Exception $e) {
                    // Log but continue
                    \Log::warning("Could not add FK constraint to {$table}.{$column}: " . $e->getMessage());
                }
            }
        }
        
        // Re-enable foreign key checks
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
    }

    public function down(): void
    {
        // Skip this migration for SQLite - it uses MySQL-specific syntax
        if (DB::connection()->getDriverName() === 'sqlite') {
            return;
        }
        
        // This is a complex migration, rollback carefully  
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        
        // Remove constraints added
        if (Schema::hasTable('stocks')) {
            try {
                // Check and drop constraints if they exist
                $constraints = DB::select("
                    SELECT CONSTRAINT_NAME 
                    FROM information_schema.TABLE_CONSTRAINTS 
                    WHERE TABLE_SCHEMA = DATABASE()
                    AND TABLE_NAME = 'stocks'
                    AND CONSTRAINT_TYPE = 'CHECK'
                ");
                
                foreach ($constraints as $constraint) {
                    if (in_array($constraint->CONSTRAINT_NAME, ['chk_quantity_non_negative', 'chk_available_calculation'])) {
                        DB::statement("ALTER TABLE stocks DROP CHECK {$constraint->CONSTRAINT_NAME}");
                    }
                }
            } catch (\Exception $e) {
                // Ignore if not exists
            }
        }
        
        if (Schema::hasTable('payments')) {
            try {
                Schema::table('payments', function (Blueprint $table) {
                    $table->dropUnique('uk_invoice_reference');
                });
            } catch (\Exception $e) {
                // Ignore if not exists
            }
        }
        
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
    }
};
