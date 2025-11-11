<?php

namespace Tests\Feature\Api\Mcp;

use App\Enums\CredentialScope;
use App\Enums\IntegrationStatus;
use App\Enums\IntegrationType;
use App\Enums\LeaseStatus;
use App\Enums\OrganizationRole;
use App\Models\CredentialLease;
use App\Models\IntegrationAccount;
use App\Models\Organization;
use App\Models\OrganizationMember;
use App\Models\User;
use App\Models\UserToken;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CredentialLeaseApiTest extends TestCase
{
    use RefreshDatabase;

    private function actingAsMcpServer(): User
    {
        $user = User::factory()->create();
        $token = UserToken::factory()->mcpServer()->create(['user_id' => $user->id]);

        $this->withHeaders(['Authorization' => 'Bearer '.$token->token]);

        return $user;
    }

    // CREATE LEASE TESTS

    public function test_can_create_credential_lease_with_personal_credentials(): void
    {
        $user = $this->actingAsMcpServer();

        // Create personal credentials
        IntegrationAccount::factory()->create([
            'user_id' => $user->id,
            'type' => IntegrationType::TODOIST,
            'scope' => CredentialScope::Personal,
            'status' => IntegrationStatus::ACTIVE,
        ]);

        IntegrationAccount::factory()->create([
            'user_id' => $user->id,
            'type' => IntegrationType::OPENAI,
            'scope' => CredentialScope::Personal,
            'status' => IntegrationStatus::ACTIVE,
        ]);

        $response = $this->postJson('/api/mcp/credentials/lease', [
            'services' => ['todoist', 'openai'],
            'ttl' => 3600,
            'server_id' => 'mcp-server-1',
        ]);

        $response->assertCreated();
        $response->assertJsonStructure([
            'lease_id',
            'credentials',
            'expires_at',
            'renewable',
            'credential_scope',
        ]);

        $this->assertEquals('personal', $response->json('credential_scope'));
        $this->assertDatabaseHas('credential_leases', [
            'user_id' => $user->id,
            'server_id' => 'mcp-server-1',
            'status' => LeaseStatus::Active,
        ]);
    }

    public function test_can_create_credential_lease_with_organization_credentials(): void
    {
        $user = $this->actingAsMcpServer();

        // Create organization
        $org = Organization::factory()->create(['owner_id' => $user->id]);
        OrganizationMember::factory()->create([
            'organization_id' => $org->id,
            'user_id' => $user->id,
            'role' => OrganizationRole::Owner,
        ]);

        // Create organization credentials
        IntegrationAccount::factory()->create([
            'organization_id' => $org->id,
            'user_id' => $user->id,
            'type' => IntegrationType::JIRA,
            'scope' => CredentialScope::Organization,
            'shared_with' => ['all_members'],
            'status' => IntegrationStatus::ACTIVE,
        ]);

        $response = $this->postJson('/api/mcp/credentials/lease', [
            'services' => ['jira'],
            'ttl' => 3600,
            'server_id' => 'mcp-server-1',
        ]);

        $response->assertCreated();
        $this->assertEquals('organization', $response->json('credential_scope'));
        $this->assertNotNull($response->json('included_org_credentials'));
    }

    public function test_can_create_lease_with_mixed_personal_and_organization_credentials(): void
    {
        $user = $this->actingAsMcpServer();

        // Personal credential
        IntegrationAccount::factory()->create([
            'user_id' => $user->id,
            'type' => IntegrationType::TODOIST,
            'scope' => CredentialScope::Personal,
        ]);

        // Organization credential
        $org = Organization::factory()->create(['owner_id' => $user->id]);
        OrganizationMember::factory()->create([
            'organization_id' => $org->id,
            'user_id' => $user->id,
            'role' => OrganizationRole::Owner,
        ]);

        IntegrationAccount::factory()->create([
            'organization_id' => $org->id,
            'user_id' => $user->id,
            'type' => IntegrationType::JIRA,
            'scope' => CredentialScope::Organization,
            'shared_with' => ['all_members'],
        ]);

        $response = $this->postJson('/api/mcp/credentials/lease', [
            'services' => ['todoist', 'jira'],
            'ttl' => 3600,
            'server_id' => 'mcp-server-1',
        ]);

        $response->assertCreated();
        $this->assertEquals('mixed', $response->json('credential_scope'));
    }

    public function test_cannot_create_lease_without_credentials(): void
    {
        $user = $this->actingAsMcpServer();

        $response = $this->postJson('/api/mcp/credentials/lease', [
            'services' => ['todoist'],
            'ttl' => 3600,
            'server_id' => 'mcp-server-1',
        ]);

        $response->assertStatus(422);
        $response->assertJsonFragment([
            'message' => 'No credentials available for requested services',
        ]);
    }

    public function test_create_lease_requires_valid_services_array(): void
    {
        $this->actingAsMcpServer();

        $response = $this->postJson('/api/mcp/credentials/lease', [
            'services' => 'invalid',
            'ttl' => 3600,
            'server_id' => 'mcp-server-1',
        ]);

        $response->assertStatus(422);
    }

    public function test_create_lease_requires_server_id(): void
    {
        $this->actingAsMcpServer();

        $response = $this->postJson('/api/mcp/credentials/lease', [
            'services' => ['todoist'],
            'ttl' => 3600,
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['server_id']);
    }

    public function test_lease_creation_logs_audit_trail(): void
    {
        $user = $this->actingAsMcpServer();

        IntegrationAccount::factory()->create([
            'user_id' => $user->id,
            'type' => IntegrationType::TODOIST,
            'scope' => CredentialScope::Personal,
        ]);

        $this->postJson('/api/mcp/credentials/lease', [
            'services' => ['todoist'],
            'ttl' => 3600,
            'server_id' => 'mcp-server-1',
        ]);

        $this->assertDatabaseHas('user_activity_logs', [
            'user_id' => $user->id,
            'action' => 'lease_created',
            'entity_type' => 'CredentialLease',
        ]);
    }

    // RENEW LEASE TESTS

    public function test_can_renew_active_lease(): void
    {
        $user = $this->actingAsMcpServer();

        $lease = CredentialLease::factory()->create([
            'user_id' => $user->id,
            'status' => LeaseStatus::Active,
            'renewable' => true,
            'expires_at' => now()->addMinutes(30),
            'renewal_count' => 0,
            'max_renewals' => 24,
        ]);

        $originalExpiry = $lease->expires_at;

        $response = $this->postJson("/api/mcp/credentials/lease/{$lease->lease_id}/renew");

        $response->assertOk();
        $lease->refresh();

        $this->assertTrue($lease->expires_at->gt($originalExpiry));
        $this->assertEquals(1, $lease->renewal_count);
        $this->assertNotNull($lease->last_renewed_at);
    }

    public function test_cannot_renew_expired_lease(): void
    {
        $user = $this->actingAsMcpServer();

        $lease = CredentialLease::factory()->create([
            'user_id' => $user->id,
            'status' => LeaseStatus::Expired,
            'expires_at' => now()->subHour(),
        ]);

        $response = $this->postJson("/api/mcp/credentials/lease/{$lease->lease_id}/renew");

        $response->assertStatus(403);
        $response->assertJsonFragment([
            'message' => 'Lease cannot be renewed',
        ]);
    }

    public function test_cannot_renew_revoked_lease(): void
    {
        $user = $this->actingAsMcpServer();

        $lease = CredentialLease::factory()->create([
            'user_id' => $user->id,
            'status' => LeaseStatus::Revoked,
        ]);

        $response = $this->postJson("/api/mcp/credentials/lease/{$lease->lease_id}/renew");

        $response->assertStatus(403);
    }

    public function test_cannot_renew_lease_exceeding_max_renewals(): void
    {
        $user = $this->actingAsMcpServer();

        $lease = CredentialLease::factory()->create([
            'user_id' => $user->id,
            'status' => LeaseStatus::Active,
            'renewable' => true,
            'renewal_count' => 24,
            'max_renewals' => 24,
        ]);

        $response = $this->postJson("/api/mcp/credentials/lease/{$lease->lease_id}/renew");

        $response->assertStatus(403);
        $response->assertJsonFragment([
            'message' => 'Maximum renewal count reached',
        ]);
    }

    public function test_lease_renewal_logs_audit_trail(): void
    {
        $user = $this->actingAsMcpServer();

        $lease = CredentialLease::factory()->create([
            'user_id' => $user->id,
            'status' => LeaseStatus::Active,
            'renewable' => true,
        ]);

        $this->postJson("/api/mcp/credentials/lease/{$lease->lease_id}/renew");

        $this->assertDatabaseHas('user_activity_logs', [
            'user_id' => $user->id,
            'action' => 'lease_renewed',
            'entity_type' => 'CredentialLease',
            'entity_id' => $lease->id,
        ]);
    }

    // REVOKE LEASE TESTS

    public function test_can_revoke_active_lease(): void
    {
        $user = $this->actingAsMcpServer();

        $lease = CredentialLease::factory()->create([
            'user_id' => $user->id,
            'status' => LeaseStatus::Active,
        ]);

        $response = $this->deleteJson("/api/mcp/credentials/lease/{$lease->lease_id}", [
            'reason' => 'User requested revocation',
        ]);

        $response->assertOk();
        $lease->refresh();

        $this->assertEquals(LeaseStatus::Revoked, $lease->status);
        $this->assertNotNull($lease->revoked_at);
        $this->assertEquals('User requested revocation', $lease->revocation_reason);
    }

    public function test_revoke_lease_logs_audit_trail(): void
    {
        $user = $this->actingAsMcpServer();

        $lease = CredentialLease::factory()->create([
            'user_id' => $user->id,
            'status' => LeaseStatus::Active,
        ]);

        $this->deleteJson("/api/mcp/credentials/lease/{$lease->lease_id}");

        $this->assertDatabaseHas('user_activity_logs', [
            'user_id' => $user->id,
            'action' => 'lease_revoked',
            'entity_type' => 'CredentialLease',
        ]);
    }

    // GET LEASE TESTS

    public function test_can_get_lease_details(): void
    {
        $user = $this->actingAsMcpServer();

        $lease = CredentialLease::factory()->create([
            'user_id' => $user->id,
            'status' => LeaseStatus::Active,
        ]);

        $response = $this->getJson("/api/mcp/credentials/lease/{$lease->lease_id}");

        $response->assertOk();
        $response->assertJson([
            'lease_id' => $lease->lease_id,
            'status' => 'active',
            'services' => $lease->services,
        ]);
    }

    public function test_cannot_get_another_users_lease(): void
    {
        $this->actingAsMcpServer();

        $otherUser = User::factory()->create();
        $lease = CredentialLease::factory()->create([
            'user_id' => $otherUser->id,
        ]);

        $response = $this->getJson("/api/mcp/credentials/lease/{$lease->lease_id}");

        $response->assertForbidden();
    }

    // CREDENTIAL RESOLUTION TESTS

    public function test_personal_credentials_have_priority_over_organization(): void
    {
        $user = $this->actingAsMcpServer();

        // Organization credential
        $org = Organization::factory()->create(['owner_id' => $user->id]);
        OrganizationMember::factory()->create([
            'organization_id' => $org->id,
            'user_id' => $user->id,
            'role' => OrganizationRole::Owner,
        ]);

        IntegrationAccount::factory()->create([
            'organization_id' => $org->id,
            'type' => IntegrationType::TODOIST,
            'scope' => CredentialScope::Organization,
            'access_token' => 'org_token',
            'shared_with' => ['all_members'],
        ]);

        // Personal credential (should override org)
        IntegrationAccount::factory()->create([
            'user_id' => $user->id,
            'type' => IntegrationType::TODOIST,
            'scope' => CredentialScope::Personal,
            'access_token' => 'personal_token',
        ]);

        $response = $this->postJson('/api/mcp/credentials/lease', [
            'services' => ['todoist'],
            'ttl' => 3600,
            'server_id' => 'mcp-server-1',
        ]);

        $response->assertCreated();

        // Decrypt to verify personal token was used
        $lease = CredentialLease::where('lease_id', $response->json('lease_id'))->first();
        $credentials = decrypt($lease->credentials);

        // Should contain personal token, not org token
        $this->assertStringContainsString('personal', json_encode($credentials));
    }

    public function test_user_must_have_access_to_organization_credentials(): void
    {
        $user = $this->actingAsMcpServer();

        // Create org where user is NOT a member
        $org = Organization::factory()->create();

        IntegrationAccount::factory()->create([
            'organization_id' => $org->id,
            'type' => IntegrationType::JIRA,
            'scope' => CredentialScope::Organization,
            'shared_with' => ['all_members'],
        ]);

        $response = $this->postJson('/api/mcp/credentials/lease', [
            'services' => ['jira'],
            'ttl' => 3600,
            'server_id' => 'mcp-server-1',
        ]);

        // Should fail because user is not member of org
        $response->assertStatus(422);
    }
}
