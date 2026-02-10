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
        Schema::table('stocks', function (Blueprint $table) {
            // Add new fields for direct product information
            $table->string('product_code', 100)->after('company_id')->nullable();
            $table->string('product_name', 255)->after('product_code');
            $table->enum('product_type', ['Local', 'Import'])->after('product_name')->default('Local');
            $table->string('unit', 50)->after('product_type')->default('pcs');
            $table->string('category', 100)->after('unit')->nullable();
            $table->decimal('base_price', 15, 2)->after('category')->default(0);
            
            // Keep product_id for now but make it nullable (for backward compatibility)
            $table->uuid('product_id')->nullable()->change();
        });
        
        // Create index for better search performance
        Schema::table('stocks', function (Blueprint $table) {
            $table->index('product_code');
            $table->index('product_name');
            $table->index('product_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('stocks', function (Blueprint $table) {
            $table->dropIndex(['product_code']);
            $table->dropIndex(['product_name']);
            $table->dropIndex(['product_type']);
            
            $table->dropColumn([
                'product_code',
                'product_name',
                'product_type',
                'unit',
                'category',
                'base_price',
            ]);
            
            // Restore product_id to not nullable
            $table->uuid('product_id')->nullable(false)->change();
        });
    }
};
