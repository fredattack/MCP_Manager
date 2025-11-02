<?php

namespace Database\Seeders;

use App\Enums\UserRole;
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
        $this->command->info('Creating AgentOps users...');

        // Disable events temporarily to avoid duplicate syncs
        $shouldSync = config('mcp-server.sync.enabled', true);

        // 1. Admin User
        $admin = User::updateOrCreate(
            ['email' => 'admin@agentops.be'],
            [
                'name' => 'Admin AgentOps',
                'password' => Hash::make('oaHtB!wDa.YxPV3Tn!V3'),
                'role' => UserRole::ADMIN,
                'is_active' => true,
                'email_verified_at' => now(),
            ]
        );

        if ($admin->wasRecentlyCreated && $shouldSync) {
            event(new UserCreatedInManager($admin));
        }

        $this->command->info("✓ Admin created: admin@agentops.be");

        // 2. Manager User
        $manager = User::updateOrCreate(
            ['email' => 'manager@agentops.be'],
            [
                'name' => 'Manager AgentOps',
                'password' => Hash::make('WxswriLs74ZZUx6p8Pvg!'),
                'role' => UserRole::MANAGER,
                'is_active' => true,
                'email_verified_at' => now(),
            ]
        );

        if ($manager->wasRecentlyCreated && $shouldSync) {
            event(new UserCreatedInManager($manager));
        }

        $this->command->info("✓ Manager created: manager@agentops.be");

        // 3. Regular User
        $user = User::updateOrCreate(
            ['email' => 'user@agentops.be'],
            [
                'name' => 'User AgentOps',
                'password' => Hash::make('WxswriLs74ZZUx6p8Pvg!'),
                'role' => UserRole::USER,
                'is_active' => true,
                'email_verified_at' => now(),
            ]
        );

        if ($user->wasRecentlyCreated && $shouldSync) {
            event(new UserCreatedInManager($user));
        }

        $this->command->info("✓ User created: user@agentops.be");

        // 4. Read Only User
        $readonly = User::updateOrCreate(
            ['email' => 'readonly@agentops.be'],
            [
                'name' => 'ReadOnly AgentOps',
                'password' => Hash::make('WxswriLs74ZZUx6p8Pvg!'),
                'role' => UserRole::READ_ONLY,
                'is_active' => true,
                'email_verified_at' => now(),
            ]
        );

        if ($readonly->wasRecentlyCreated && $shouldSync) {
            event(new UserCreatedInManager($readonly));
        }

        $this->command->info("✓ ReadOnly created: readonly@agentops.be");

        // 5. Create 16 random users with random roles
        $this->command->info('Creating 16 random users...');

        $roles = [
            UserRole::ADMIN,
            UserRole::MANAGER,
            UserRole::USER,
            UserRole::READ_ONLY,
        ];

        $firstNames = [
            'Alice', 'Bob', 'Charlie', 'Diana', 'Edward', 'Fiona', 'George', 'Hannah',
            'Ian', 'Julia', 'Kevin', 'Laura', 'Michael', 'Nancy', 'Oliver', 'Patricia',
            'Quinn', 'Rachel', 'Steven', 'Tina',
        ];

        $lastNames = [
            'Smith', 'Johnson', 'Williams', 'Brown', 'Jones', 'Garcia', 'Miller', 'Davis',
            'Rodriguez', 'Martinez', 'Hernandez', 'Lopez', 'Gonzalez', 'Wilson', 'Anderson', 'Thomas',
        ];

        for ($i = 1; $i <= 16; $i++) {
            $firstName = $firstNames[array_rand($firstNames)];
            $lastName = $lastNames[array_rand($lastNames)];
            $email = strtolower("{$firstName}.{$lastName}.{$i}@agentops.be");
            $role = $roles[array_rand($roles)];

            $randomUser = User::updateOrCreate(
                ['email' => $email],
                [
                    'name' => "{$firstName} {$lastName}",
                    'password' => Hash::make('password'), // Default password for test users
                    'role' => $role,
                    'is_active' => true,
                    'email_verified_at' => now(),
                ]
            );

            if ($randomUser->wasRecentlyCreated && $shouldSync) {
                event(new UserCreatedInManager($randomUser));
            }

            $this->command->info("  ✓ User {$i}/16: {$email} (Role: {$role->value})");
        }

        $this->command->newLine();
        $this->command->info('═══════════════════════════════════════════════════');
        $this->command->info('AgentOps Users Seeding Complete!');
        $this->command->info('═══════════════════════════════════════════════════');
        $this->command->newLine();

        $this->command->table(
            ['Role', 'Email', 'Password'],
            [
                ['Admin', 'admin@agentops.be', 'oaHtB!wDa.YxPV3Tn!V3'],
                ['Manager', 'manager@agentops.be', 'WxswriLs74ZZUx6p8Pvg!'],
                ['User', 'user@agentops.be', 'WxswriLs74ZZUx6p8Pvg!'],
                ['ReadOnly', 'readonly@agentops.be', 'WxswriLs74ZZUx6p8Pvg!'],
                ['Various', 'Random users (16)', 'password'],
            ]
        );

        if ($shouldSync) {
            $this->command->newLine();
            $this->command->info('📡 Sync jobs dispatched to queue.');
            $this->command->info('Run: php artisan queue:work to process synchronization.');
        }
    }
}
