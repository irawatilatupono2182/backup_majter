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
        Schema::create('products', function (Blueprint $table) {
            $table->uuid('product_id')->primary();
            $table->uuid('company_id');
            $table->string('product_code', 50);
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('unit', 20)->comment('pcs, kg, liter, dll');
            $table->decimal('base_price', 18, 2)->default(0);
            $table->decimal('default_discount_percent', 5, 2)->default(0);
            $table->integer('min_stock_alert')->default(5);
            $table->string('category', 100)->nullable();
            $table->enum('product_type', ['STOCK', 'CATALOG'])->default('STOCK')->comment('STOCK: ada di gudang, CATALOG: hanya referensi');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();
            
            $table->unique(['company_id', 'product_code'], 'uk_product_code');
            $table->foreign('company_id')->references('company_id')->on('companies')->onDelete('cascade');
            $table->index('company_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};