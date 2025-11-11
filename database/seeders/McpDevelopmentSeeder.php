<?php

namespace Database\Seeders;

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
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class McpDevelopmentSeeder extends Seeder
{
    /**
     * Run the MCP development data seeder.
     *
     * This seeder creates:
     * - MCP Server user with long-lived token
     * - Test users with various integrations
     * - Organizations with shared credentials
     * - Active credential leases for testing
     */
    public function run(): void
    {
        $this->command->info('🚀 Seeding MCP Development Data...');

        // 1. Create MCP Server user with token
        $mcpServerToken = $this->createMcpServerUser();

        // 2. Create test users
        $testUser = $this->createTestUsers();

        // 3. Create organizations with members and credentials
        $orgs = $this->createOrganizations($testUser);

        // 4. Create personal credentials for test user
        $this->createPersonalCredentials($testUser);

        // 5. Create active leases
        $this->createActiveLeases($testUser, $orgs);

        // Display summary
        $this->displaySummary($mcpServerToken);
    }

    private function createMcpServerUser(): string
    {
        $this->command->info('📡 Creating MCP Server user...');

        $mcpUser = User::firstOrCreate(
            ['email' => 'mcp-server@system.local'],
            [
                'name' => 'MCP Server',
                'password' => Hash::make('mcp-server-'.now()->timestamp),
                'email_verified_at' => now(),
                'is_active' => true,
            ]
        );

        // Create long-lived token for MCP Server
        $token = UserToken::factory()
            ->mcpServer()
            ->create([
                'user_id' => $mcpUser->id,
                'token' => 'mcp_dev_'.bin2hex(random_bytes(32)),
                'name' => 'MCP Server Development Token',
            ]);

        $this->command->info("✅ MCP Server user created: {$mcpUser->email}");
        $this->command->warn("🔑 Token: {$token->token}");

        return $token->token;
    }

    private function createTestUsers(): User
    {
        $this->command->info('👥 Creating test users...');

        $testUser = User::firstOrCreate(
            ['email' => 'test@mcp-manager.local'],
            [
                'name' => 'Test User',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
                'is_active' => true,
            ]
        );

        $this->command->info("✅ Test user created: {$testUser->email} (password: password)");

        return $testUser;
    }

    private function createOrganizations(User $owner): array
    {
        $this->command->info('🏢 Creating organizations...');

        $orgs = [];

        // Organization 1: Development Team with shared integrations
        $devTeam = Organization::firstOrCreate(
            ['slug' => 'dev-team'],
            [
                'name' => 'Development Team',
                'owner_id' => $owner->id,
                'billing_email' => 'billing@dev-team.local',
                'max_members' => 20,
                'settings' => [
                    'allow_member_invites' => true,
                    'require_two_factor' => false,
                ],
            ]
        );

        // Add owner as member
        OrganizationMember::firstOrCreate(
            [
                'organization_id' => $devTeam->id,
                'user_id' => $owner->id,
            ],
            [
                'role' => OrganizationRole::Owner,
                'joined_at' => now(),
            ]
        );

        // Create shared JIRA integration for dev team
        IntegrationAccount::firstOrCreate(
            [
                'organization_id' => $devTeam->id,
                'type' => IntegrationType::JIRA,
            ],
            [
                'user_id' => $owner->id,
                'scope' => CredentialScope::Organization,
                'access_token' => 'jira_dev_team_'.bin2hex(random_bytes(16)),
                'meta' => [
                    'url' => 'https://dev-team.atlassian.net',
                    'email' => 'jira@dev-team.local',
                    'cloud' => true,
                ],
                'shared_with' => ['all_members'],
                'status' => IntegrationStatus::ACTIVE,
                'created_by' => $owner->id,
            ]
        );

        // Create shared Notion integration for dev team
        IntegrationAccount::firstOrCreate(
            [
                'organization_id' => $devTeam->id,
                'type' => IntegrationType::NOTION,
            ],
            [
                'user_id' => $owner->id,
                'scope' => CredentialScope::Organization,
                'access_token' => 'notion_dev_team_'.bin2hex(random_bytes(16)),
                'meta' => [
                    'workspace_name' => 'Dev Team Workspace',
                    'database_id' => 'notion-db-'.bin2hex(random_bytes(8)),
                ],
                'shared_with' => ['all_members'],
                'status' => IntegrationStatus::ACTIVE,
                'created_by' => $owner->id,
            ]
        );

        $orgs[] = $devTeam;
        $this->command->info("✅ Organization created: {$devTeam->name}");

        // Organization 2: Client Projects with admin-only access
        $clientOrg = Organization::firstOrCreate(
            ['slug' => 'client-projects'],
            [
                'name' => 'Client Projects',
                'owner_id' => $owner->id,
                'billing_email' => 'billing@client-projects.local',
                'max_members' => 10,
            ]
        );

        OrganizationMember::firstOrCreate(
            [
                'organization_id' => $clientOrg->id,
                'user_id' => $owner->id,
            ],
            [
                'role' => OrganizationRole::Owner,
                'joined_at' => now(),
            ]
        );

        // Sentry integration (admins only)
        IntegrationAccount::firstOrCreate(
            [
                'organization_id' => $clientOrg->id,
                'type' => IntegrationType::SENTRY,
            ],
            [
                'user_id' => $owner->id,
                'scope' => CredentialScope::Organization,
                'access_token' => 'sentry_client_'.bin2hex(random_bytes(16)),
                'meta' => [
                    'org_slug' => 'client-projects',
                    'base_url' => 'https://sentry.io/api/0',
                ],
                'shared_with' => ['admins_only'],
                'status' => IntegrationStatus::ACTIVE,
                'created_by' => $owner->id,
            ]
        );

        $orgs[] = $clientOrg;
        $this->command->info("✅ Organization created: {$clientOrg->name}");

        return $orgs;
    }

    private function createPersonalCredentials(User $user): void
    {
        $this->command->info('🔐 Creating personal credentials...');

        $personalCredentials = [
            [
                'type' => IntegrationType::TODOIST,
                'token' => 'todoist_personal_'.bin2hex(random_bytes(16)),
                'meta' => [],
            ],
            [
                'type' => IntegrationType::OPENAI,
                'token' => 'sk-openai_personal_'.bin2hex(random_bytes(24)),
                'meta' => [
                    'default_model' => 'gpt-4',
                    'max_tokens' => 4096,
                ],
            ],
            [
                'type' => IntegrationType::GMAIL,
                'token' => 'gmail_personal_'.bin2hex(random_bytes(16)),
                'meta' => [
                    'email' => $user->email,
                ],
            ],
        ];

        foreach ($personalCredentials as $cred) {
            IntegrationAccount::firstOrCreate(
                [
                    'user_id' => $user->id,
                    'type' => $cred['type'],
                    'scope' => CredentialScope::Personal,
                ],
                [
                    'access_token' => $cred['token'],
                    'meta' => $cred['meta'],
                    'status' => IntegrationStatus::ACTIVE,
                ]
            );
        }

        $this->command->info('✅ Personal credentials created');
    }

    private function createActiveLeases(User $user, array $orgs): void
    {
        $this->command->info('⏱️  Creating active credential leases...');

        // Lease 1: Personal credentials only
        $personalLease = CredentialLease::create([
            'user_id' => $user->id,
            'lease_id' => 'lse_personal_'.bin2hex(random_bytes(20)),
            'server_id' => 'mcp-server-dev-1',
            'services' => ['todoist', 'openai'],
            'credentials' => encrypt([
                'todoist' => ['access_token' => 'todoist_personal_xxx'],
                'openai' => ['access_token' => 'sk-openai_personal_xxx'],
            ]),
            'credential_scope' => 'personal',
            'expires_at' => now()->addHour(),
            'renewable' => true,
            'status' => LeaseStatus::Active,
            'client_info' => 'MCP Server v1.0 / Python 3.11',
            'client_ip' => '127.0.0.1',
        ]);

        // Lease 2: Organization credentials
        $orgLease = CredentialLease::create([
            'user_id' => $user->id,
            'organization_id' => $orgs[0]->id,
            'lease_id' => 'lse_org_'.bin2hex(random_bytes(20)),
            'server_id' => 'mcp-server-dev-1',
            'services' => ['jira', 'notion'],
            'credentials' => encrypt([
                'jira' => [
                    'url' => 'https://dev-team.atlassian.net',
                    'email' => 'jira@dev-team.local',
                    'api_token' => 'jira_dev_team_xxx',
                ],
                'notion' => ['access_token' => 'notion_dev_team_xxx'],
            ]),
            'credential_scope' => 'organization',
            'included_org_credentials' => [
                ['organization_id' => $orgs[0]->id, 'services' => ['jira', 'notion']],
            ],
            'expires_at' => now()->addHour(),
            'renewable' => true,
            'renewal_count' => 2,
            'status' => LeaseStatus::Active,
            'client_info' => 'MCP Server v1.0 / Python 3.11',
            'client_ip' => '127.0.0.1',
            'last_renewed_at' => now()->subMinutes(10),
        ]);

        // Lease 3: Expiring soon (for testing alerts)
        $expiringSoon = CredentialLease::create([
            'user_id' => $user->id,
            'lease_id' => 'lse_expiring_'.bin2hex(random_bytes(20)),
            'server_id' => 'mcp-server-dev-2',
            'services' => ['sentry'],
            'credentials' => encrypt([
                'sentry' => [
                    'auth_token' => 'sentry_xxx',
                    'org_slug' => 'client-projects',
                ],
            ]),
            'credential_scope' => 'organization',
            'included_org_credentials' => [
                ['organization_id' => $orgs[1]->id, 'services' => ['sentry']],
            ],
            'expires_at' => now()->addMinutes(5),
            'renewable' => true,
            'renewal_count' => 10,
            'status' => LeaseStatus::Active,
            'client_info' => 'MCP Server v1.0 / Python 3.11',
            'client_ip' => '127.0.0.1',
        ]);

        $this->command->info("✅ Created {$personalLease->lease_id}");
        $this->command->info("✅ Created {$orgLease->lease_id}");
        $this->command->info("✅ Created {$expiringSoon->lease_id} (expires in 5 min)");
    }

    private function displaySummary(string $mcpToken): void
    {
        $this->command->newLine();
        $this->command->info('═══════════════════════════════════════════════════════════');
        $this->command->info('🎉 MCP Development Data Seeded Successfully!');
        $this->command->info('═══════════════════════════════════════════════════════════');
        $this->command->newLine();

        $this->command->info('📊 Summary:');
        $this->command->line('  • Users: '.User::count());
        $this->command->line('  • Organizations: '.Organization::count());
        $this->command->line('  • Integration Accounts: '.IntegrationAccount::count());
        $this->command->line('  • User Tokens: '.UserToken::count());
        $this->command->line('  • Active Leases: '.CredentialLease::where('status', LeaseStatus::Active)->count());

        $this->command->newLine();
        $this->command->info('🔑 MCP Server Configuration:');
        $this->command->line('  Add this to your Python .env file:');
        $this->command->newLine();
        $this->command->warn('  MCP_API_URL=http://localhost:3978');
        $this->command->warn("  MCP_TOKEN={$mcpToken}");
        $this->command->newLine();

        $this->command->info('🧪 Test User:');
        $this->command->line('  Email: test@mcp-manager.local');
        $this->command->line('  Password: password');
        $this->command->newLine();

        $this->command->info('📡 Test API Endpoints:');
        $this->command->line('  GET  http://localhost:3978/api/mcp/me');
        $this->command->line('  POST http://localhost:3978/api/mcp/credentials/lease');
        $this->command->newLine();

        $this->command->info('💡 Quick Test:');
        $this->command->line('  curl -H "Authorization: Bearer '.$mcpToken.'" \\');
        $this->command->line('       http://localhost:3978/api/mcp/me');
        $this->command->newLine();

        $this->command->info('═══════════════════════════════════════════════════════════');
    }
}
