<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * ✅ CRITICAL FIX #6: Audit Trail Table
     * Immutable audit log - CANNOT be deleted or modified
     */
    public function up(): void
    {
        Schema::create('audit_logs', function (Blueprint $table) {
            $table->id();
            $table->string('action', 20); // created, updated, deleted
            $table->string('model', 100); // Invoice, Payment, Stock, etc.
            $table->string('model_id', 36); // UUID of the record
            $table->uuid('user_id')->nullable();
            $table->string('user_name', 100)->nullable();
            $table->string('user_email', 100)->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->text('url')->nullable();
            $table->json('old_values')->nullable(); // Before changes
            $table->json('new_values')->nullable(); // After changes
            $table->timestamp('created_at')->useCurrent(); // When it happened
            
            // Indexes for fast search
            $table->index(['model', 'model_id']);
            $table->index('user_id');
            $table->index('created_at');
            $table->index('action');
            
            // Foreign key
            $table->foreign('user_id')->references('id')->on('users')->onDelete('set null');
        });
        
        // Note: Audit log immutability is enforced at application level
        // The model should not have update() or delete() methods enabled
    }

    public function down(): void
    {
        Schema::dropIfExists('audit_logs');
    }
};
