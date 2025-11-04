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
        Schema::create('organization_members', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('role', 20);
            $table->json('permissions')->nullable();
            $table->foreignId('invited_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('joined_at');
            $table->timestamps();

            $table->unique(['organization_id', 'user_id']);
            $table->index(['organization_id', 'role']);
            $table->index('user_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('organization_members');
    }
};
