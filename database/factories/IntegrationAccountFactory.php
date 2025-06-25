<?php

namespace Database\Factories;

use App\Enums\IntegrationStatus;
use App\Enums\IntegrationType;
use App\Models\IntegrationAccount;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\IntegrationAccount>
 */
class IntegrationAccountFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = IntegrationAccount::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'type' => $this->faker->randomElement(IntegrationType::cases()),
            'access_token' => $this->faker->uuid(),
            'meta' => [
                'workspace_name' => $this->faker->company(),
                'user_id' => $this->faker->uuid(),
            ],
            'status' => IntegrationStatus::ACTIVE,
        ];
    }

    /**
     * Indicate that the integration account is for Notion.
     *
     * @return \Illuminate\Database\Eloquent\Factories\Factory
     */
    public function notion()
    {
        return $this->state(function (array $attributes) {
            return [
                'type' => IntegrationType::NOTION,
            ];
        });
    }

    /**
     * Indicate that the integration account is for Gmail.
     *
     * @return \Illuminate\Database\Eloquent\Factories\Factory
     */
    public function gmail()
    {
        return $this->state(function (array $attributes) {
            return [
                'type' => IntegrationType::GMAIL,
            ];
        });
    }

    /**
     * Indicate that the integration account is for OpenAI.
     *
     * @return \Illuminate\Database\Eloquent\Factories\Factory
     */
    public function openai()
    {
        return $this->state(function (array $attributes) {
            return [
                'type' => IntegrationType::OPENAI,
            ];
        });
    }

    /**
     * Indicate that the integration account is inactive.
     *
     * @return \Illuminate\Database\Eloquent\Factories\Factory
     */
    public function inactive()
    {
        return $this->state(function (array $attributes) {
            return [
                'status' => IntegrationStatus::INACTIVE,
            ];
        });
    }
}
