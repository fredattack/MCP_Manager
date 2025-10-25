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
        Schema::create('mcp_integrations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('mcp_server_id')->constrained()->onDelete('cascade');
            $table->string('service_name');
            $table->boolean('enabled')->default(false);
            $table->enum('status', ['active', 'inactive', 'error', 'connecting'])->default('inactive');
            $table->json('config')->nullable();
            $table->timestamp('last_sync_at')->nullable();
            $table->text('error_message')->nullable();
            $table->boolean('credentials_valid')->default(false);
            $table->timestamps();

            $table->index(['user_id', 'service_name']);
            $table->index('status');
            $table->index('enabled');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mcp_integrations');
    }
};
