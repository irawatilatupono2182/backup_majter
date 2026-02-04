<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * ✅ CRITICAL FIX #10: Add credit limit fields
     */
    public function up(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->decimal('credit_limit', 18, 2)->default(0)->after('email');
            $table->decimal('used_credit', 18, 2)->default(0)->after('credit_limit');
            $table->decimal('available_credit', 18, 2)->default(0)->after('used_credit');
            $table->boolean('enforce_credit_limit')->default(true)->after('available_credit');
        });
    }

    public function down(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->dropColumn(['credit_limit', 'used_credit', 'available_credit', 'enforce_credit_limit']);
        });
    }
};
