<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * ✅ CRITICAL FIX #8: Backup logs table
     */
    public function up(): void
    {
        Schema::create('backup_logs', function (Blueprint $table) {
            $table->id();
            $table->enum('type', ['database', 'storage', 'full'])->default('database');
            $table->string('filename', 255);
            $table->bigInteger('file_size')->nullable()->comment('File size in bytes');
            $table->enum('status', ['success', 'failed', 'in_progress'])->default('in_progress');
            $table->text('error_message')->nullable();
            $table->timestamp('created_at')->useCurrent();
            
            $table->index('created_at');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('backup_logs');
    }
};
