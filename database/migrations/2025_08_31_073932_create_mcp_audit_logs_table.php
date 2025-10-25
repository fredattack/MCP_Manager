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
        Schema::create('mcp_audit_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('action', 100); // configuration_changed, authentication, connection, etc.
            $table->string('entity', 50); // mcp_server, integration, security, etc.
            $table->string('entity_id', 50)->nullable(); // ID of the affected entity
            $table->json('data')->nullable(); // Additional context data
            $table->enum('status', ['success', 'failed', 'warning'])->default('success');
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->timestamp('created_at')->useCurrent();

            // Indexes for performance
            $table->index('user_id');
            $table->index('action');
            $table->index('entity');
            $table->index('status');
            $table->index('created_at');
            $table->index(['user_id', 'action', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mcp_audit_logs');
    }
};
