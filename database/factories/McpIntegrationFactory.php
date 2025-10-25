<?php

namespace Database\Factories;

use App\Models\McpServer;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\McpIntegration>
 */
class McpIntegrationFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'mcp_server_id' => McpServer::factory(),
            'service_name' => $this->faker->randomElement(['todoist', 'notion', 'jira', 'sentry', 'confluence']),
            'enabled' => $this->faker->boolean(80), // 80% chance of being enabled
            'status' => $this->faker->randomElement(['active', 'inactive', 'error', 'connecting']),
            'config' => [
                'configured_at' => $this->faker->dateTimeBetween('-1 year')->format('c'),
            ],
            'last_sync_at' => $this->faker->optional()->dateTimeBetween('-1 week'),
            'error_message' => null,
            'credentials_valid' => $this->faker->boolean(90), // 90% chance of valid credentials
        ];
    }

    /**
     * Indicate that the integration is active.
     */
    public function active(): static
    {
        return $this->state(fn (array $attributes) => [
            'enabled' => true,
            'status' => 'active',
            'credentials_valid' => true,
            'error_message' => null,
        ]);
    }

    /**
     * Indicate that the integration has an error.
     */
    public function withError(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'error',
            'credentials_valid' => false,
            'error_message' => 'Authentication failed: '.$this->faker->sentence(),
        ]);
    }

    /**
     * Indicate that the integration is for a specific service.
     */
    public function forService(string $service): static
    {
        return $this->state(fn (array $attributes) => [
            'service_name' => $service,
        ]);
    }
}
