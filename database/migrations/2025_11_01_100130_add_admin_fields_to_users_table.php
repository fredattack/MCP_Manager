<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // role and permissions columns removed - using Spatie Permission package
            $table->boolean('is_active')->default(true)->after('email');
            $table->boolean('is_locked')->default(false)->after('is_active');
            $table->timestamp('locked_at')->nullable()->after('is_locked');
            $table->string('locked_reason')->nullable()->after('locked_at');
            $table->timestamp('last_login_at')->nullable()->after('locked_reason');
            $table->string('last_login_ip')->nullable()->after('last_login_at');
            $table->integer('failed_login_attempts')->default(0)->after('last_login_ip');
            $table->timestamp('last_failed_login_at')->nullable()->after('failed_login_attempts');
            $table->text('notes')->nullable()->after('last_failed_login_at');
            $table->foreignId('created_by')->nullable()->after('notes')->constrained('users')->nullOnDelete();

            $table->index('is_active');
            $table->index('last_login_at');
        });
    }
};
