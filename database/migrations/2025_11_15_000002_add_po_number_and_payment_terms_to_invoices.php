<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->string('po_number', 100)->nullable()->after('invoice_number');
            $table->integer('payment_terms')->default(30)->after('due_date')->comment('Payment terms in days');
        });
    }

    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->dropColumn(['po_number', 'payment_terms']);
        });
    }
};
