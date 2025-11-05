<?php

namespace Database\Seeders;

use App\Enums\Permission as PermissionEnum;
use App\Enums\Role as RoleEnum;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RoleAndPermissionSeeder extends Seeder
{
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        $this->command->info('Creating permissions...');
        $this->createPermissions();

        $this->command->info('Creating roles...');
        $this->createRoles();

        $this->command->info('Assigning permissions to roles...');
        $this->assignPermissions();

        $this->command->comment('Roles and permissions created successfully!');
    }

    protected function createPermissions(): void
    {
        foreach (PermissionEnum::cases() as $permission) {
            Permission::findOrCreate($permission->value, 'web');
            $this->command->line("  - {$permission->value}");
        }
    }

    protected function createRoles(): void
    {
        foreach (RoleEnum::cases() as $role) {
            Role::findOrCreate($role->value, 'web');
            $this->command->line("  - {$role->value}");
        }
    }

    protected function assignPermissions(): void
    {
        // God - Absolute Control (ALL permissions)
        $god = Role::findByName(RoleEnum::GOD->value);
        $allPermissions = Permission::all();
        $god->givePermissionTo($allPermissions);
        $this->command->line("  ✓ GOD permissions assigned ({$allPermissions->count()} permissions)");

        // Platform Admin - Full Access
        $platformAdmin = Role::findByName(RoleEnum::PLATFORM_ADMIN->value);
        $platformAdmin->givePermissionTo([
            PermissionEnum::PLATFORM_ORGANIZATIONS_MANAGE,
            PermissionEnum::PLATFORM_USERS_MANAGE,
            PermissionEnum::PLATFORM_AUDIT_MANAGE,
            PermissionEnum::PLATFORM_SYSTEM_MANAGE,
        ]);
        $this->command->line('  ✓ PLATFORM_ADMIN permissions assigned');

        // Platform Manager - Manage orgs and users
        $platformManager = Role::findByName(RoleEnum::PLATFORM_MANAGER->value);
        $platformManager->givePermissionTo([
            PermissionEnum::PLATFORM_ORGANIZATIONS_READ,
            PermissionEnum::PLATFORM_ORGANIZATIONS_CREATE,
            PermissionEnum::PLATFORM_ORGANIZATIONS_UPDATE,
            PermissionEnum::PLATFORM_USERS_READ,
            PermissionEnum::PLATFORM_USERS_CREATE,
            PermissionEnum::PLATFORM_USERS_UPDATE,
            PermissionEnum::PLATFORM_AUDIT_READ,
        ]);
        $this->command->line('  ✓ PLATFORM_MANAGER permissions assigned');

        // Platform Support - Read-only access
        $platformSupport = Role::findByName(RoleEnum::PLATFORM_SUPPORT->value);
        $platformSupport->givePermissionTo([
            PermissionEnum::PLATFORM_ORGANIZATIONS_READ,
            PermissionEnum::PLATFORM_USERS_READ,
            PermissionEnum::PLATFORM_AUDIT_READ,
            PermissionEnum::PLATFORM_SYSTEM_READ,
        ]);
        $this->command->line('  ✓ PLATFORM_SUPPORT permissions assigned');

        // Platform Developer - Technical access
        $platformDeveloper = Role::findByName(RoleEnum::PLATFORM_DEVELOPER->value);
        $platformDeveloper->givePermissionTo([
            PermissionEnum::PLATFORM_ORGANIZATIONS_READ,
            PermissionEnum::PLATFORM_USERS_READ,
            PermissionEnum::PLATFORM_AUDIT_MANAGE,
            PermissionEnum::PLATFORM_SYSTEM_MANAGE,
        ]);
        $this->command->line('  ✓ PLATFORM_DEVELOPER permissions assigned');

        // Organization Owner - Full org control
        $orgOwner = Role::findByName(RoleEnum::ORG_OWNER->value);
        $orgOwner->givePermissionTo([
            PermissionEnum::ORG_CREDENTIALS_MANAGE,
            PermissionEnum::ORG_MEMBERS_MANAGE,
            PermissionEnum::ORG_INTEGRATIONS_MANAGE,
            PermissionEnum::ORG_LEASES_MANAGE,
            PermissionEnum::ORG_SETTINGS_MANAGE,
        ]);
        $this->command->line('  ✓ ORG_OWNER permissions assigned');

        // Organization Admin - Manage resources
        $orgAdmin = Role::findByName(RoleEnum::ORG_ADMIN->value);
        $orgAdmin->givePermissionTo([
            PermissionEnum::ORG_CREDENTIALS_READ,
            PermissionEnum::ORG_CREDENTIALS_CREATE,
            PermissionEnum::ORG_CREDENTIALS_UPDATE,
            PermissionEnum::ORG_CREDENTIALS_SHARE,
            PermissionEnum::ORG_MEMBERS_READ,
            PermissionEnum::ORG_MEMBERS_INVITE,
            PermissionEnum::ORG_MEMBERS_UPDATE,
            PermissionEnum::ORG_INTEGRATIONS_MANAGE,
            PermissionEnum::ORG_LEASES_READ,
            PermissionEnum::ORG_LEASES_APPROVE,
            PermissionEnum::ORG_SETTINGS_READ,
        ]);
        $this->command->line('  ✓ ORG_ADMIN permissions assigned');

        // Organization Member - Standard access
        $orgMember = Role::findByName(RoleEnum::ORG_MEMBER->value);
        $orgMember->givePermissionTo([
            PermissionEnum::ORG_CREDENTIALS_READ,
            PermissionEnum::ORG_MEMBERS_READ,
            PermissionEnum::ORG_INTEGRATIONS_READ,
            PermissionEnum::ORG_LEASES_READ,
            PermissionEnum::ORG_LEASES_CREATE,
            PermissionEnum::ORG_SETTINGS_READ,
        ]);
        $this->command->line('  ✓ ORG_MEMBER permissions assigned');

        // Organization Guest - Read-only
        $orgGuest = Role::findByName(RoleEnum::ORG_GUEST->value);
        $orgGuest->givePermissionTo([
            PermissionEnum::ORG_CREDENTIALS_READ,
            PermissionEnum::ORG_MEMBERS_READ,
            PermissionEnum::ORG_SETTINGS_READ,
        ]);
        $this->command->line('  ✓ ORG_GUEST permissions assigned');
    }
}
