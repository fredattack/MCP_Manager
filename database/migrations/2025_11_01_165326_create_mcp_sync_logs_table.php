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
        Schema::create('mcp_sync_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->enum('sync_type', ['create', 'update', 'delete', 'token_refresh']);
            $table->enum('direction', ['laravel_to_mcp', 'mcp_to_laravel']);
            $table->enum('status', ['success', 'failed', 'partial']);
            $table->json('request_payload')->nullable();
            $table->json('response_payload')->nullable();
            $table->text('error_message')->nullable();
            $table->unsignedInteger('duration_ms')->nullable()->comment('Request duration in milliseconds');
            $table->timestamp('created_at');

            $table->index(['user_id', 'sync_type']);
            $table->index(['status', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mcp_sync_logs');
    }
};
