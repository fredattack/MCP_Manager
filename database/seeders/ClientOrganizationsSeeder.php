<?php

namespace Database\Seeders;

use App\Enums\OrganizationRole;
use App\Enums\OrganizationStatus;
use App\Enums\Role as RoleEnum;
use App\Models\Organization;
use App\Models\OrganizationMember;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class ClientOrganizationsSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('Creating client organizations and their members...');

        $clientOrgs = [
            [
                'name' => 'ACME Corporation',
                'slug' => 'acme-corp',
                'billing_email' => 'billing@acme-corp.com',
                'max_members' => 25,
                'users' => [
                    [
                        'name' => 'John Smith',
                        'email' => 'john.smith@acme-corp.com',
                        'role' => RoleEnum::ORG_OWNER,
                        'org_role' => OrganizationRole::Owner,
                    ],
                    [
                        'name' => 'Sarah Johnson',
                        'email' => 'sarah.johnson@acme-corp.com',
                        'role' => RoleEnum::ORG_ADMIN,
                        'org_role' => OrganizationRole::Admin,
                    ],
                    [
                        'name' => 'Mike Davis',
                        'email' => 'mike.davis@acme-corp.com',
                        'role' => RoleEnum::ORG_MEMBER,
                        'org_role' => OrganizationRole::Member,
                    ],
                ],
            ],
            [
                'name' => 'TechVentures Inc',
                'slug' => 'techventures',
                'billing_email' => 'billing@techventures.com',
                'max_members' => 15,
                'users' => [
                    [
                        'name' => 'Emma Wilson',
                        'email' => 'emma.wilson@techventures.com',
                        'role' => RoleEnum::ORG_OWNER,
                        'org_role' => OrganizationRole::Owner,
                    ],
                    [
                        'name' => 'David Brown',
                        'email' => 'david.brown@techventures.com',
                        'role' => RoleEnum::ORG_ADMIN,
                        'org_role' => OrganizationRole::Admin,
                    ],
                    [
                        'name' => 'Lisa Martinez',
                        'email' => 'lisa.martinez@techventures.com',
                        'role' => RoleEnum::ORG_MEMBER,
                        'org_role' => OrganizationRole::Member,
                    ],
                    [
                        'name' => 'Tom Anderson',
                        'email' => 'tom.anderson@techventures.com',
                        'role' => RoleEnum::ORG_GUEST,
                        'org_role' => OrganizationRole::Guest,
                    ],
                ],
            ],
            [
                'name' => 'Global Solutions Ltd',
                'slug' => 'global-solutions',
                'billing_email' => 'billing@global-solutions.com',
                'max_members' => 30,
                'users' => [
                    [
                        'name' => 'Robert Taylor',
                        'email' => 'robert.taylor@global-solutions.com',
                        'role' => RoleEnum::ORG_OWNER,
                        'org_role' => OrganizationRole::Owner,
                    ],
                    [
                        'name' => 'Jennifer Lee',
                        'email' => 'jennifer.lee@global-solutions.com',
                        'role' => RoleEnum::ORG_ADMIN,
                        'org_role' => OrganizationRole::Admin,
                    ],
                    [
                        'name' => 'Chris Garcia',
                        'email' => 'chris.garcia@global-solutions.com',
                        'role' => RoleEnum::ORG_MEMBER,
                        'org_role' => OrganizationRole::Member,
                    ],
                    [
                        'name' => 'Amy Rodriguez',
                        'email' => 'amy.rodriguez@global-solutions.com',
                        'role' => RoleEnum::ORG_MEMBER,
                        'org_role' => OrganizationRole::Member,
                    ],
                ],
            ],
            [
                'name' => 'Digital Dynamics',
                'slug' => 'digital-dynamics',
                'billing_email' => 'billing@digital-dynamics.com',
                'max_members' => 20,
                'users' => [
                    [
                        'name' => 'Patricia White',
                        'email' => 'patricia.white@digital-dynamics.com',
                        'role' => RoleEnum::ORG_OWNER,
                        'org_role' => OrganizationRole::Owner,
                    ],
                    [
                        'name' => 'James Miller',
                        'email' => 'james.miller@digital-dynamics.com',
                        'role' => RoleEnum::ORG_ADMIN,
                        'org_role' => OrganizationRole::Admin,
                    ],
                    [
                        'name' => 'Maria Lopez',
                        'email' => 'maria.lopez@digital-dynamics.com',
                        'role' => RoleEnum::ORG_MEMBER,
                        'org_role' => OrganizationRole::Member,
                    ],
                ],
            ],
        ];

        foreach ($clientOrgs as $orgData) {
            $this->command->newLine();
            $this->command->info("Creating organization: {$orgData['name']}");

            // Get the first user (owner) to use as organization owner_id
            $ownerData = $orgData['users'][0];
            $owner = User::updateOrCreate(
                ['email' => $ownerData['email']],
                [
                    'name' => $ownerData['name'],
                    'password' => Hash::make('password'), // Default password for all client users
                    'is_active' => true,
                    'email_verified_at' => now(),
                ]
            );
            $owner->syncRoles([$ownerData['role']->value]);

            // Create organization
            $org = Organization::updateOrCreate(
                ['slug' => $orgData['slug']],
                [
                    'name' => $orgData['name'],
                    'owner_id' => $owner->id,
                    'billing_email' => $orgData['billing_email'],
                    'status' => OrganizationStatus::Active,
                    'max_members' => $orgData['max_members'],
                    'settings' => [
                        'is_super_tenant' => false,
                        'features' => [
                            'credentials' => true,
                            'integrations' => true,
                            'leases' => true,
                            'audit_logs' => true,
                        ],
                    ],
                ]
            );

            $this->command->line("  ✓ Organization created: {$org->name}");

            // Add owner as member
            OrganizationMember::updateOrCreate(
                [
                    'organization_id' => $org->id,
                    'user_id' => $owner->id,
                ],
                [
                    'role' => $ownerData['org_role'],
                    'invited_by' => null,
                    'joined_at' => now(),
                ]
            );
            $this->command->line("    → {$owner->name} ({$ownerData['org_role']->value}, Owner)");

            // Add other users as members
            foreach (array_slice($orgData['users'], 1) as $userData) {
                $user = User::updateOrCreate(
                    ['email' => $userData['email']],
                    [
                        'name' => $userData['name'],
                        'password' => Hash::make('password'),
                        'is_active' => true,
                        'email_verified_at' => now(),
                    ]
                );
                $user->syncRoles([$userData['role']->value]);

                OrganizationMember::updateOrCreate(
                    [
                        'organization_id' => $org->id,
                        'user_id' => $user->id,
                    ],
                    [
                        'role' => $userData['org_role'],
                        'invited_by' => $owner->id,
                        'joined_at' => now(),
                    ]
                );
                $this->command->line("    → {$user->name} ({$userData['org_role']->value})");
            }
        }

        $this->command->newLine();
        $this->command->comment('Client organizations and members created successfully!');
        $this->command->info('Total: 4 client organizations with 14 users');
        $this->command->info('Default password for all client users: password');
    }
}
