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
        Schema::create('customers', function (Blueprint $table) {
            $table->uuid('customer_id')->primary();
            $table->uuid('company_id');
            
            // [NO] → customer_code
            $table->string('customer_code', 50)->comment('Digunakan sebagai "NO" di laporan');
            
            // [NAMA]
            $table->string('name')->comment('Nama instansi customer');
            
            // [U.P.]
            $table->string('contact_person', 100)->nullable()->comment('Untuk Perhatian / PIC');
            
            // [ALAMAT 1 (SHIP TO)]
            $table->text('address_ship_to');
            
            // [ALAMAT 2 (BILL TO)]
            $table->text('address_bill_to')->nullable();
            
            // [NPWP]
            $table->string('npwp', 30)->nullable();
            
            // [JADWAL KONTRA BON]
            $table->string('billing_schedule', 100)->nullable()->comment('Contoh: "Setiap tgl 5", "Minggu ke-2"');
            
            // Tambahan penting
            $table->boolean('is_ppn')->default(false);
            $table->string('phone', 30)->nullable();
            $table->string('email', 100)->nullable();
            $table->boolean('is_active')->default(true);
            
            $table->timestamps();
            $table->softDeletes();
            
            $table->unique(['company_id', 'customer_code'], 'uk_customer_code');
            $table->foreign('company_id')->references('company_id')->on('companies')->onDelete('cascade');
            $table->index('company_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('customers');
    }
};