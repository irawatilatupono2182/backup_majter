<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('purchase_orders', function (Blueprint $table) {
            $table->enum('payment_status', ['unpaid', 'partial', 'paid'])->default('unpaid')->after('status');
            $table->date('due_date')->nullable()->after('expected_delivery');
            $table->integer('payment_terms_days')->default(0)->after('due_date')->comment('Payment terms in days');
        });
    }

    public function down(): void
    {
        Schema::table('purchase_orders', function (Blueprint $table) {
            $table->dropColumn(['payment_status', 'due_date', 'payment_terms_days']);
        });
    }
};
