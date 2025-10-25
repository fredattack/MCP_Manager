<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\GitConnectionStatus;
use App\Enums\GitProvider;
use App\Models\GitConnection;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Crypt;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\GitConnection>
 */
class GitConnectionFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = GitConnection::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'provider' => $this->faker->randomElement(GitProvider::cases()),
            'external_user_id' => (string) $this->faker->randomNumber(8),
            'scopes' => ['repo', 'read:user'],
            'access_token_enc' => Crypt::encryptString('gho_'.$this->faker->sha256()),
            'refresh_token_enc' => Crypt::encryptString('ghr_'.$this->faker->sha256()),
            'expires_at' => now()->addHours(8),
            'status' => GitConnectionStatus::ACTIVE,
        ];
    }

    /**
     * Indicate that the connection is for GitHub.
     *
     * @return \Illuminate\Database\Eloquent\Factories\Factory<GitConnection>
     */
    public function github(): Factory
    {
        return $this->state(function (array $attributes) {
            return [
                'provider' => GitProvider::GITHUB,
                'scopes' => ['repo', 'read:user', 'workflow'],
            ];
        });
    }

    /**
     * Indicate that the connection is for GitLab.
     *
     * @return \Illuminate\Database\Eloquent\Factories\Factory<GitConnection>
     */
    public function gitlab(): Factory
    {
        return $this->state(function (array $attributes) {
            return [
                'provider' => GitProvider::GITLAB,
                'scopes' => ['api', 'read_repository', 'write_repository', 'read_user'],
            ];
        });
    }

    /**
     * Indicate that the connection is inactive.
     *
     * @return \Illuminate\Database\Eloquent\Factories\Factory<GitConnection>
     */
    public function inactive(): Factory
    {
        return $this->state(function (array $attributes) {
            return [
                'status' => GitConnectionStatus::INACTIVE,
            ];
        });
    }

    /**
     * Indicate that the connection has an expired token.
     *
     * @return \Illuminate\Database\Eloquent\Factories\Factory<GitConnection>
     */
    public function expired(): Factory
    {
        return $this->state(function (array $attributes) {
            return [
                'status' => GitConnectionStatus::EXPIRED,
                'expires_at' => now()->subHours(1),
            ];
        });
    }

    /**
     * Indicate that the connection has an error.
     *
     * @return \Illuminate\Database\Eloquent\Factories\Factory<GitConnection>
     */
    public function error(): Factory
    {
        return $this->state(function (array $attributes) {
            return [
                'status' => GitConnectionStatus::ERROR,
            ];
        });
    }
}
