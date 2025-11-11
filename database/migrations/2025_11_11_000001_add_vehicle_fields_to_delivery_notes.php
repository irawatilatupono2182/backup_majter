<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('delivery_notes', function (Blueprint $table) {
            $table->string('vehicle_type', 100)->nullable()->after('notes'); // Jenis kendaraan
            $table->string('vehicle_number', 50)->nullable()->after('vehicle_type'); // No. Polisi
            $table->string('driver_name', 100)->nullable()->after('vehicle_number'); // Nama supir (optional)
        });
    }

    public function down(): void
    {
        Schema::table('delivery_notes', function (Blueprint $table) {
            $table->dropColumn(['vehicle_type', 'vehicle_number', 'driver_name']);
        });
    }
};
