<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class CreateAdminUser extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:create-admin 
                            {--name= : Admin name}
                            {--email= : Admin email}
                            {--password= : Admin password (min 8 characters)}
                            {--no-interaction : Run without prompting for input}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create an admin user for the application';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('Creating Admin User for MCP Manager');
        $this->line('=====================================');

        // Get input data
        $name = $this->getNameInput();
        $email = $this->getEmailInput();
        $password = $this->getPasswordInput();

        // Validate input
        $validator = Validator::make([
            'name' => $name,
            'email' => $email,
            'password' => $password,
        ], [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:8|regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).+$/',
        ], [
            'password.regex' => 'Password must contain at least one uppercase letter, one lowercase letter, and one number.',
        ]);

        if ($validator->fails()) {
            $this->error('Validation failed:');
            foreach ($validator->errors()->all() as $error) {
                $this->error('  ✗ ' . $error);
            }
            return 1;
        }

        // Confirm before creating in production
        if (app()->environment('production')) {
            $this->warn('⚠️  You are about to create an admin user in PRODUCTION environment!');
            if (!$this->confirm('Are you sure you want to proceed?')) {
                $this->info('Operation cancelled.');
                return 0;
            }
        }

        try {
            // Create the admin user
            $user = User::create([
                'name' => $name,
                'email' => $email,
                'password' => Hash::make($password),
                'email_verified_at' => now(),
            ]);

            // Display success message
            $this->newLine();
            $this->info('✅ Admin user created successfully!');
            $this->newLine();
            
            // Display user details in a table
            $this->table(
                ['ID', 'Name', 'Email', 'Created At'],
                [[$user->id, $user->name, $user->email, $user->created_at->format('Y-m-d H:i:s')]]
            );

            // Security reminders
            $this->newLine();
            $this->line('📝 Security Reminders:');
            $this->line('  • Store the password securely');
            $this->line('  • Enable 2FA as soon as possible');
            $this->line('  • Regularly rotate passwords');
            
            if (app()->environment('local', 'development')) {
                $this->newLine();
                $this->warn('⚠️  Development environment detected - consider using stronger passwords in production');
            }

            // Log the creation
            \Log::info('Admin user created via artisan command', [
                'user_id' => $user->id,
                'email' => $user->email,
                'created_by' => 'artisan command',
                'environment' => app()->environment(),
            ]);

            return 0;

        } catch (\Exception $e) {
            $this->error('Failed to create admin user: ' . $e->getMessage());
            \Log::error('Failed to create admin user via artisan command', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return 1;
        }
    }

    /**
     * Get the name input.
     */
    private function getNameInput(): string
    {
        if ($this->option('no-interaction')) {
            return $this->option('name') ?? 'Admin User';
        }

        return $this->option('name') ?? $this->ask('What is the admin\'s name?', 'Admin User');
    }

    /**
     * Get the email input.
     */
    private function getEmailInput(): string
    {
        if ($this->option('no-interaction')) {
            if (!$this->option('email')) {
                $this->error('Email is required when using --no-interaction');
                exit(1);
            }
            return $this->option('email');
        }

        $email = $this->option('email');
        
        while (!$email) {
            $email = $this->ask('What is the admin\'s email address?');
            
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $this->error('Please provide a valid email address.');
                $email = null;
            }
        }

        return $email;
    }

    /**
     * Get the password input.
     */
    private function getPasswordInput(): string
    {
        if ($this->option('no-interaction')) {
            if (!$this->option('password')) {
                // Generate a random secure password if not provided
                $password = $this->generateSecurePassword();
                $this->info('Generated password: ' . $password);
                $this->warn('⚠️  Please save this password securely!');
                return $password;
            }
            return $this->option('password');
        }

        $password = $this->option('password');
        
        if (!$password) {
            $this->info('Password requirements:');
            $this->line('  • Minimum 8 characters');
            $this->line('  • At least one uppercase letter');
            $this->line('  • At least one lowercase letter');
            $this->line('  • At least one number');
            $this->newLine();
            
            while (!$password) {
                $password = $this->secret('Enter password');
                $passwordConfirm = $this->secret('Confirm password');
                
                if ($password !== $passwordConfirm) {
                    $this->error('Passwords do not match. Please try again.');
                    $password = null;
                    continue;
                }
                
                if (strlen($password) < 8) {
                    $this->error('Password must be at least 8 characters long.');
                    $password = null;
                    continue;
                }
                
                if (!preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).+$/', $password)) {
                    $this->error('Password must contain at least one uppercase letter, one lowercase letter, and one number.');
                    $password = null;
                }
            }
        }

        return $password;
    }

    /**
     * Generate a secure random password.
     */
    private function generateSecurePassword(): string
    {
        $length = 16;
        $uppercase = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $lowercase = 'abcdefghijklmnopqrstuvwxyz';
        $numbers = '0123456789';
        $symbols = '!@#$%^&*()_+-=[]{}|;:,.<>?';
        
        $password = '';
        $password .= $uppercase[random_int(0, strlen($uppercase) - 1)];
        $password .= $lowercase[random_int(0, strlen($lowercase) - 1)];
        $password .= $numbers[random_int(0, strlen($numbers) - 1)];
        $password .= $symbols[random_int(0, strlen($symbols) - 1)];
        
        $allChars = $uppercase . $lowercase . $numbers . $symbols;
        
        for ($i = 4; $i < $length; $i++) {
            $password .= $allChars[random_int(0, strlen($allChars) - 1)];
        }
        
        return str_shuffle($password);
    }
}