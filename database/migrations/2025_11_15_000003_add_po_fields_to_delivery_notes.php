<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('delivery_notes', function (Blueprint $table) {
            $table->string('po_number', 100)->nullable()->after('sj_number');
            $table->date('po_date')->nullable()->after('po_number');
            $table->integer('top')->default(14)->after('po_date')->comment('Terms of Payment in days');
        });
    }

    public function down(): void
    {
        Schema::table('delivery_notes', function (Blueprint $table) {
            $table->dropColumn(['po_number', 'po_date', 'top']);
        });
    }
};
