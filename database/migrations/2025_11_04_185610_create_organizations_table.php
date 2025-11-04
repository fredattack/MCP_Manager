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
        Schema::create('organizations', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug', 100)->unique();
            $table->foreignId('owner_id')->constrained('users')->onDelete('cascade');
            $table->string('billing_email')->nullable();
            $table->string('status', 20)->default('active');
            $table->integer('max_members')->default(5);
            $table->json('settings')->nullable();
            $table->timestamps();

            $table->index(['slug', 'status']);
            $table->index('owner_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('organizations');
    }
};
