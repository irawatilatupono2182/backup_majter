<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * ✅ CRITICAL FIX #5: Add approval tracking fields
     */
    public function up(): void
    {
        // Add to invoices
        Schema::table('invoices', function (Blueprint $table) {
            $table->enum('approval_status', ['draft', 'pending', 'approved', 'rejected'])->default('draft')->after('status');
            $table->uuid('approved_by')->nullable()->after('approval_status');
            $table->timestamp('approved_at')->nullable()->after('approved_by');
            $table->text('approval_notes')->nullable()->after('approved_at');
            
            $table->foreign('approved_by')->references('id')->on('users')->onDelete('set null');
        });
        
        // Add to purchase_orders
        Schema::table('purchase_orders', function (Blueprint $table) {
            $table->enum('approval_status', ['draft', 'pending', 'approved', 'rejected'])->default('draft')->after('status');
            $table->uuid('approved_by')->nullable()->after('approval_status');
            $table->timestamp('approved_at')->nullable()->after('approved_by');
            $table->text('approval_notes')->nullable()->after('approved_at');
            
            $table->foreign('approved_by')->references('id')->on('users')->onDelete('set null');
        });
        
        // Add to delivery_notes
        Schema::table('delivery_notes', function (Blueprint $table) {
            $table->enum('approval_status', ['draft', 'pending', 'approved', 'rejected'])->default('draft')->after('status');
            $table->uuid('approved_by')->nullable()->after('approval_status');
            $table->timestamp('approved_at')->nullable()->after('approved_by');
            $table->text('approval_notes')->nullable()->after('approved_at');
            
            $table->foreign('approved_by')->references('id')->on('users')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->dropForeign(['approved_by']);
            $table->dropColumn(['approval_status', 'approved_by', 'approved_at', 'approval_notes']);
        });
        
        Schema::table('purchase_orders', function (Blueprint $table) {
            $table->dropForeign(['approved_by']);
            $table->dropColumn(['approval_status', 'approved_by', 'approved_at', 'approval_notes']);
        });
        
        Schema::table('delivery_notes', function (Blueprint $table) {
            $table->dropForeign(['approved_by']);
            $table->dropColumn(['approval_status', 'approved_by', 'approved_at', 'approval_notes']);
        });
    }
};
