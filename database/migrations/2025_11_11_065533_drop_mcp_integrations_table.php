<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * The mcp_integrations table is deprecated in favor of the Credential Lease system.
     * MCP Manager should NOT connect directly to MCP Server.
     *
     * Communication is now handled via:
     * - integration_accounts (credential storage)
     * - credential_leases (temporary access via API)
     * - CredentialResolutionService (credential resolution)
     */
    public function up(): void
    {
        Schema::dropIfExists('mcp_integrations');
    }
};
