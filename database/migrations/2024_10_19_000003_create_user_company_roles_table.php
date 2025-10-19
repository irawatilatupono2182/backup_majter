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
        Schema::create('user_company_roles', function (Blueprint $table) {
            $table->uuid('user_id');
            $table->uuid('company_id');
            $table->enum('role', ['admin', 'finance', 'warehouse', 'viewer'])
                  ->comment('admin, finance, warehouse, viewer');
            $table->boolean('is_default')->default(false);
            $table->timestamps();
            
            $table->primary(['user_id', 'company_id']);
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('company_id')->references('company_id')->on('companies')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_company_roles');
    }
};