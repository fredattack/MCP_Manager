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
        Schema::create('git_clones', function (Blueprint $table) {
            $table->id();
            $table->foreignId('repository_id')->constrained('git_repositories')->onDelete('cascade');
            $table->string('ref'); // branch, tag, or commit SHA
            $table->string('storage_driver'); // s3, local
            $table->text('artifact_path');
            $table->bigInteger('size_bytes')->nullable();
            $table->integer('duration_ms')->nullable();
            $table->string('status')->default('pending'); // pending, cloning, completed, failed
            $table->text('error')->nullable();
            $table->timestamps();

            $table->index(['repository_id', 'status']);
            $table->index(['repository_id', 'ref']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('git_clones');
    }
};
