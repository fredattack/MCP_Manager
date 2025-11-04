<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('credential_leases', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('organization_id')->nullable()->constrained()->onDelete('cascade');
            $table->string('lease_id', 64)->unique();
            $table->string('server_id', 100)->nullable();
            $table->json('services');
            $table->text('credentials');
            $table->string('credential_scope', 20)->nullable();
            $table->json('included_org_credentials')->nullable();
            $table->timestamp('expires_at');
            $table->boolean('renewable')->default(true);
            $table->integer('renewal_count')->default(0);
            $table->integer('max_renewals')->default(24);
            $table->string('status', 20)->default('active');
            $table->text('client_info')->nullable();
            $table->ipAddress('client_ip')->nullable();
            $table->timestamp('last_renewed_at')->nullable();
            $table->timestamp('revoked_at')->nullable();
            $table->text('revocation_reason')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'status']);
            $table->index(['lease_id', 'status']);
            $table->index(['organization_id', 'status']);
            $table->index('expires_at');
            $table->index(['server_id', 'status']);
        });
    }
};
