<?php

namespace Database\Seeders;

use App\Enums\Role as RoleEnum;
use App\Events\UserCreatedInManager;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AgentOpsUsersSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('Creating AgentOps platform users...');

        $shouldSync = config('mcp-server.sync.enabled', true);

        // 1. Platform Admin (will be owner of AgentOps organization)
        $admin = User::updateOrCreate(
            ['email' => 'admin@agentops.be'],
            [
                'name' => 'Platform Administrator',
                'password' => Hash::make('oaHtB!wDa.YxPV3Tn!V3'),
                'is_active' => true,
                'email_verified_at' => now(),
            ]
        );

        $admin->syncRoles([RoleEnum::PLATFORM_ADMIN->value]);

        if ($admin->wasRecentlyCreated && $shouldSync) {
            event(new UserCreatedInManager($admin));
        }

        $this->command->line("  ✓ {$admin->email} - PLATFORM_ADMIN");

        // 2. Platform Manager
        $manager = User::updateOrCreate(
            ['email' => 'manager@agentops.be'],
            [
                'name' => 'Platform Manager',
                'password' => Hash::make('oaHtB!wDa.YxPV3Tn!V3'),
                'is_active' => true,
                'email_verified_at' => now(),
            ]
        );

        $manager->syncRoles([RoleEnum::PLATFORM_MANAGER->value]);

        if ($manager->wasRecentlyCreated && $shouldSync) {
            event(new UserCreatedInManager($manager));
        }

        $this->command->line("  ✓ {$manager->email} - PLATFORM_MANAGER");

        // 3. Platform Support
        $support = User::updateOrCreate(
            ['email' => 'support@agentops.be'],
            [
                'name' => 'Platform Support',
                'password' => Hash::make('oaHtB!wDa.YxPV3Tn!V3'),
                'is_active' => true,
                'email_verified_at' => now(),
            ]
        );

        $support->syncRoles([RoleEnum::PLATFORM_SUPPORT->value]);

        if ($support->wasRecentlyCreated && $shouldSync) {
            event(new UserCreatedInManager($support));
        }

        $this->command->line("  ✓ {$support->email} - PLATFORM_SUPPORT");

        // 4. Platform Developer
        $developer = User::updateOrCreate(
            ['email' => 'dev@agentops.be'],
            [
                'name' => 'Platform Developer',
                'password' => Hash::make('oaHtB!wDa.YxPV3Tn!V3'),
                'is_active' => true,
                'email_verified_at' => now(),
            ]
        );

        $developer->syncRoles([RoleEnum::PLATFORM_DEVELOPER->value]);

        if ($developer->wasRecentlyCreated && $shouldSync) {
            event(new UserCreatedInManager($developer));
        }

        $this->command->line("  ✓ {$developer->email} - PLATFORM_DEVELOPER");

        $this->command->newLine();
        $this->command->comment('AgentOps platform users created successfully!');
        $this->command->newLine();

        $this->command->table(
            ['User', 'Email', 'Spatie Role', 'Password'],
            [
                ['Platform Admin', 'admin@agentops.be', 'PLATFORM_ADMIN', 'oaHtB!wDa.YxPV3Tn!V3'],
                ['Platform Manager', 'manager@agentops.be', 'PLATFORM_MANAGER', 'oaHtB!wDa.YxPV3Tn!V3'],
                ['Platform Support', 'support@agentops.be', 'PLATFORM_SUPPORT', 'oaHtB!wDa.YxPV3Tn!V3'],
                ['Platform Developer', 'dev@agentops.be', 'PLATFORM_DEVELOPER', 'oaHtB!wDa.YxPV3Tn!V3'],
            ]
        );

        if ($shouldSync) {
            $this->command->newLine();
            $this->command->info('📡 Sync jobs dispatched to queue.');
        }
    }
}
