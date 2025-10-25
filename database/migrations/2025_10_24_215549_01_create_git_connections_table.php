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
        Schema::create('git_connections', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->string('provider'); // github, gitlab
            $table->string('external_user_id');
            $table->json('scopes');
            $table->text('access_token_enc'); // encrypted
            $table->text('refresh_token_enc')->nullable(); // encrypted
            $table->timestamp('expires_at')->nullable();
            $table->string('status')->default('active'); // active, inactive, error
            $table->timestamps();

            $table->index(['user_id', 'provider']);
            $table->unique(['user_id', 'provider', 'external_user_id'], 'git_conn_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('git_connections');
    }
};
