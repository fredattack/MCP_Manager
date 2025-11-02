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
        Schema::create('mcp_access_tokens', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->text('access_token')->comment('Encrypted JWT access token');
            $table->text('refresh_token')->comment('Encrypted JWT refresh token');
            $table->string('token_type', 20)->default('bearer');
            $table->timestamp('expires_at');
            $table->string('scope')->default('read write');
            $table->timestamps();

            $table->index(['user_id', 'expires_at']);
            $table->index('expires_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mcp_access_tokens');
    }
};
