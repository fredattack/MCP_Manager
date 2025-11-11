<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\UserToken>
 */
class UserTokenFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'token_type' => 'mcp_server',
            'token' => Str::random(64),
            'name' => fake()->words(3, true).' Token',
            'scopes' => ['*'],
            'expires_at' => now()->addYear(),
            'last_used_at' => null,
            'usage_count' => 0,
            'max_usages' => null,
            'is_active' => true,
            'created_by_ip' => fake()->ipv4(),
            'notes' => null,
        ];
    }

    public function mcpServer(): static
    {
        return $this->state(fn (array $attributes) => [
            'token_type' => 'mcp_server',
            'name' => 'MCP Server Token',
            'scopes' => ['mcp:*', 'credentials:read', 'credentials:lease'],
            'expires_at' => now()->addYears(10),
            'notes' => 'Long-lived token for MCP Server authentication',
        ]);
    }

    public function personal(): static
    {
        return $this->state(fn (array $attributes) => [
            'token_type' => 'personal_access',
            'scopes' => ['read', 'write'],
            'expires_at' => now()->addMonths(6),
        ]);
    }

    public function limitedUsage(int $maxUsages = 100): static
    {
        return $this->state(fn (array $attributes) => [
            'max_usages' => $maxUsages,
        ]);
    }

    public function expired(): static
    {
        return $this->state(fn (array $attributes) => [
            'expires_at' => now()->subDays(1),
        ]);
    }

    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }

    public function used(int $count = 10): static
    {
        return $this->state(fn (array $attributes) => [
            'usage_count' => $count,
            'last_used_at' => now()->subMinutes(fake()->numberBetween(1, 60)),
        ]);
    }
}
