<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Only run if invoices table exists
        if (!Schema::hasTable('invoices')) {
            return;
        }
        
        // Update all capitalized status values to lowercase for consistency
        DB::table('invoices')
            ->where('status', 'Paid')
            ->update(['status' => 'paid']);

        DB::table('invoices')
            ->where('status', 'Partial')
            ->update(['status' => 'partial']);

        DB::table('invoices')
            ->where('status', 'Overdue')
            ->update(['status' => 'overdue']);

        DB::table('invoices')
            ->where('status', 'Unpaid')
            ->update(['status' => 'unpaid']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert back to capitalized status values if needed
        DB::table('invoices')
            ->where('status', 'paid')
            ->update(['status' => 'Paid']);

        DB::table('invoices')
            ->where('status', 'partial')
            ->update(['status' => 'Partial']);

        DB::table('invoices')
            ->where('status', 'overdue')
            ->update(['status' => 'Overdue']);

        DB::table('invoices')
            ->where('status', 'unpaid')
            ->update(['status' => 'Unpaid']);
    }
};
