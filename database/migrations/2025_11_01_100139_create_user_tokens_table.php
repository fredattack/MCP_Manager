<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_tokens', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('token_type');
            $table->string('token', 128)->unique();
            $table->string('name')->nullable();
            $table->json('scopes')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->timestamp('last_used_at')->nullable();
            $table->integer('usage_count')->default(0);
            $table->integer('max_usages')->nullable();
            $table->boolean('is_active')->default(true);
            $table->string('created_by_ip')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index('user_id');
            $table->index('token_type');
            $table->index('token');
            $table->index('is_active');
            $table->index('expires_at');
        });
    }
};
