<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\McpServer>
 */
class McpServerFactory extends Factory
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
            'name' => $this->faker->company().' MCP Server',
            'url' => $this->faker->url(),
            'public_key' => '-----BEGIN PUBLIC KEY-----'."\n".$this->faker->sha256()."\n".'-----END PUBLIC KEY-----',
            'private_key' => '-----BEGIN PRIVATE KEY-----'."\n".$this->faker->sha256()."\n".'-----END PRIVATE KEY-----',
            'server_public_key' => '-----BEGIN PUBLIC KEY-----'."\n".$this->faker->sha256()."\n".'-----END PUBLIC KEY-----',
            'ssl_certificate' => null,
            'config' => [
                'version' => '1.0.0',
                'capabilities' => ['todoist', 'notion', 'jira'],
            ],
            'status' => $this->faker->randomElement(['active', 'inactive', 'error']),
            'session_token' => $this->faker->uuid(),
            'error_message' => null,
        ];
    }

    /**
     * Indicate that the server is active.
     */
    public function active(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'active',
            'error_message' => null,
        ]);
    }

    /**
     * Indicate that the server has an error.
     */
    public function withError(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'error',
            'error_message' => 'Connection failed: '.$this->faker->sentence(),
        ]);
    }
}
