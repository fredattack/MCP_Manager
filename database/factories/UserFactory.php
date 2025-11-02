<?php

namespace Database\Factories;

use App\Enums\UserRole;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
 */
class UserFactory extends Factory
{
    /**
     * The current password being used by the factory.
     */
    protected static ?string $password;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password' => static::$password ??= Hash::make('password'),
            'remember_token' => Str::random(10),
            'role' => UserRole::USER->value,
            'permissions' => null,
            'is_active' => true,
            'is_locked' => false,
            'locked_at' => null,
            'locked_reason' => null,
            'last_login_at' => null,
            'last_login_ip' => null,
            'failed_login_attempts' => 0,
            'last_failed_login_at' => null,
            'notes' => null,
            'created_by' => null,
            'api_token' => hash('sha256', Str::random(60)),
        ];
    }

    /**
     * Indicate that the model's email address should be unverified.
     */
    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }

    public function admin(): static
    {
        return $this->state(fn (array $attributes) => [
            'role' => UserRole::ADMIN->value,
            'permissions' => null,
        ]);
    }

    public function manager(): static
    {
        return $this->state(fn (array $attributes) => [
            'role' => UserRole::MANAGER->value,
            'permissions' => null,
        ]);
    }

    public function readOnly(): static
    {
        return $this->state(fn (array $attributes) => [
            'role' => UserRole::READ_ONLY->value,
            'permissions' => null,
        ]);
    }

    public function locked(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_locked' => true,
            'locked_at' => now(),
            'locked_reason' => fake()->randomElement([
                'Security policy violation',
                'Too many failed login attempts',
                'Suspicious activity detected',
                'Manual lock by administrator',
            ]),
        ]);
    }

    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }

    /**
     * @param  array<int, string>  $permissions
     */
    public function withPermissions(array $permissions): static
    {
        return $this->state(fn (array $attributes) => [
            'permissions' => $permissions,
        ]);
    }

    public function withRecentLogin(): static
    {
        return $this->state(fn (array $attributes) => [
            'last_login_at' => now()->subMinutes(fake()->numberBetween(1, 60)),
            'last_login_ip' => fake()->ipv4(),
        ]);
    }

    public function withFailedLogins(): static
    {
        return $this->state(fn (array $attributes) => [
            'failed_login_attempts' => fake()->numberBetween(1, 4),
            'last_failed_login_at' => now()->subMinutes(fake()->numberBetween(1, 30)),
        ]);
    }
}
