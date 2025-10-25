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
        Schema::create('git_repositories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->string('provider'); // github, gitlab
            $table->string('external_id')->index();
            $table->string('full_name'); // owner/repo
            $table->string('default_branch')->default('main');
            $table->string('visibility'); // public, private, internal
            $table->boolean('archived')->default(false);
            $table->timestamp('last_synced_at')->nullable();
            $table->json('meta')->nullable(); // description, language, stars, etc.
            $table->timestamps();

            $table->unique(['user_id', 'provider', 'external_id'], 'git_repo_unique');
            $table->index(['user_id', 'provider', 'visibility']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('git_repositories');
    }
};
