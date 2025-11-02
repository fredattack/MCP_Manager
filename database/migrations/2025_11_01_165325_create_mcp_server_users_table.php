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
        Schema::create('mcp_server_users', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete()->unique();
            $table->uuid('mcp_user_uuid')->unique()->comment('UUID from MCP Server');
            $table->unsignedInteger('mcp_user_id')->comment('Numeric ID from MCP Server');
            $table->enum('sync_status', ['pending', 'synced', 'error', 'out_of_sync'])->default('pending');
            $table->timestamp('last_sync_at')->nullable();
            $table->text('sync_error')->nullable();
            $table->unsignedInteger('sync_attempts')->default(0);
            $table->timestamps();

            $table->index('sync_status');
            $table->index('last_sync_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mcp_server_users');
    }
};
