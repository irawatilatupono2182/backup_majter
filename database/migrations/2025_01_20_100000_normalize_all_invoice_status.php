<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Update all capitalized status values to lowercase for consistency
        DB::statement("UPDATE invoices SET status = LOWER(status) WHERE status IN ('Paid', 'Partial', 'Overdue', 'Unpaid')");
        
        // Also update any mixed case variations
        DB::statement("UPDATE invoices SET status = LOWER(status) WHERE status != LOWER(status)");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert back to capitalized status values if needed
        DB::statement("
            UPDATE invoices 
            SET status = CASE 
                WHEN LOWER(status) = 'paid' THEN 'Paid'
                WHEN LOWER(status) = 'partial' THEN 'Partial'
                WHEN LOWER(status) = 'overdue' THEN 'Overdue'
                WHEN LOWER(status) = 'unpaid' THEN 'Unpaid'
                ELSE status
            END
        ");
    }
};
