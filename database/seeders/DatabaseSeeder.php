<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // For production, create no default users
        if (app()->environment('production')) {
            $this->command->info('Production environment - no default users created.');
            $this->command->info('Please create your first user via registration or artisan command.');

            return;
        }

        // For development/staging, create test user
        if (app()->environment(['local', 'development', 'staging'])) {
            User::factory()->create([
                'name' => 'Fred Moras',
                'email' => 'info@hddev.be',
                'password' => Hash::make('password'),
            ]);

            $this->command->info('Test user created: info@hddev.be / password');
            $this->command->warn('WARNING: Change these credentials before deploying to production!');
        }
    }
}
