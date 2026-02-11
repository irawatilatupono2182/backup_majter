<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Add nota_menyusul_id to invoices table
        Schema::table('invoices', function (Blueprint $table) {
            if (!Schema::hasColumn('invoices', 'nota_menyusul_id')) {
                $table->uuid('nota_menyusul_id')->nullable()->after('sj_id');
                $table->foreign('nota_menyusul_id')->references('nm_id')->on('nota_menyusuls')->onDelete('set null');
            }
        });
    }

    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            if (Schema::hasColumn('invoices', 'nota_menyusul_id')) {
                $table->dropForeign(['nota_menyusul_id']);
                $table->dropColumn('nota_menyusul_id');
            }
        });
    }
};
