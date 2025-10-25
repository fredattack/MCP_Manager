<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\GitProvider;
use App\Models\GitRepository;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\GitRepository>
 */
class GitRepositoryFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = GitRepository::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $owner = $this->faker->userName();
        $name = $this->faker->slug(2);

        return [
            'user_id' => User::factory(),
            'provider' => $this->faker->randomElement(GitProvider::cases()),
            'external_id' => (string) $this->faker->randomNumber(8),
            'full_name' => "{$owner}/{$name}",
            'default_branch' => $this->faker->randomElement(['main', 'master', 'develop']),
            'visibility' => $this->faker->randomElement(['public', 'private', 'internal']),
            'archived' => false,
            'last_synced_at' => now(),
            'meta' => [
                'description' => $this->faker->sentence(),
                'language' => $this->faker->randomElement(['PHP', 'JavaScript', 'Python', 'Go', 'Rust']),
                'stars' => $this->faker->numberBetween(0, 1000),
                'forks' => $this->faker->numberBetween(0, 100),
                'open_issues' => $this->faker->numberBetween(0, 50),
                'size_kb' => $this->faker->numberBetween(100, 50000),
                'created_at' => $this->faker->dateTimeBetween('-2 years')->format('Y-m-d H:i:s'),
                'updated_at' => $this->faker->dateTimeBetween('-1 month')->format('Y-m-d H:i:s'),
            ],
        ];
    }

    /**
     * Indicate that the repository is for GitHub.
     *
     * @return \Illuminate\Database\Eloquent\Factories\Factory<GitRepository>
     */
    public function github(): Factory
    {
        return $this->state(function (array $attributes) {
            return [
                'provider' => GitProvider::GITHUB,
            ];
        });
    }

    /**
     * Indicate that the repository is for GitLab.
     *
     * @return \Illuminate\Database\Eloquent\Factories\Factory<GitRepository>
     */
    public function gitlab(): Factory
    {
        return $this->state(function (array $attributes) {
            return [
                'provider' => GitProvider::GITLAB,
            ];
        });
    }

    /**
     * Indicate that the repository is private.
     *
     * @return \Illuminate\Database\Eloquent\Factories\Factory<GitRepository>
     */
    public function private(): Factory
    {
        return $this->state(function (array $attributes) {
            return [
                'visibility' => 'private',
            ];
        });
    }

    /**
     * Indicate that the repository is public.
     *
     * @return \Illuminate\Database\Eloquent\Factories\Factory<GitRepository>
     */
    public function public(): Factory
    {
        return $this->state(function (array $attributes) {
            return [
                'visibility' => 'public',
            ];
        });
    }

    /**
     * Indicate that the repository is archived.
     *
     * @return \Illuminate\Database\Eloquent\Factories\Factory<GitRepository>
     */
    public function archived(): Factory
    {
        return $this->state(function (array $attributes) {
            return [
                'archived' => true,
            ];
        });
    }
}
