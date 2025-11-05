<?php

namespace Database\Factories;

use App\Enums\IntegrationType;
use App\Enums\LeaseStatus;
use App\Models\Organization;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Crypt;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\CredentialLease>
 */
class CredentialLeaseFactory extends Factory
{
    public function definition(): array
    {
        $services = fake()->randomElements(
            [
                IntegrationType::NOTION->value,
                IntegrationType::JIRA->value,
                IntegrationType::TODOIST->value,
                IntegrationType::SENTRY->value,
                IntegrationType::OPENAI->value,
            ],
            fake()->numberBetween(1, 3)
        );

        // Generate credentials for each service
        $credentials = [];
        foreach ($services as $service) {
            $credentials[$service] = [
                'access_token' => fake()->sha256(),
                'refresh_token' => fake()->sha256(),
            ];
        }

        $credentialScope = fake()->randomElement(['personal', 'organization', 'mixed']);

        return [
            'user_id' => User::factory(),
            'organization_id' => $credentialScope === 'personal' ? null : Organization::factory(),
            'server_id' => 'mcp-server-'.fake()->numberBetween(1, 5),
            'services' => $services,
            'credentials' => Crypt::encryptString(json_encode($credentials)),
            'credential_scope' => $credentialScope,
            'included_org_credentials' => $credentialScope === 'organization' ? $services : null,
            'expires_at' => now()->addHour(),
            'renewable' => true,
            'renewal_count' => fake()->numberBetween(0, 5),
            'max_renewals' => 24,
            'status' => LeaseStatus::Active,
            'client_info' => json_encode([
                'user_agent' => fake()->userAgent(),
                'platform' => fake()->randomElement(['macOS', 'Linux', 'Windows']),
            ]),
            'client_ip' => fake()->ipv4(),
            'last_renewed_at' => now()->subMinutes(fake()->numberBetween(5, 30)),
        ];
    }

    public function active(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => LeaseStatus::Active,
            'expires_at' => now()->addHour(),
            'revoked_at' => null,
            'revocation_reason' => null,
        ]);
    }

    public function expired(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => LeaseStatus::Expired,
            'expires_at' => now()->subHour(),
            'renewable' => false,
        ]);
    }

    public function revoked(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => LeaseStatus::Revoked,
            'revoked_at' => now()->subMinutes(fake()->numberBetween(5, 60)),
            'revocation_reason' => fake()->randomElement([
                'Manually revoked by user',
                'Security concern',
                'Token compromised',
                'Service no longer needed',
            ]),
            'renewable' => false,
        ]);
    }

    public function expiringSoon(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => LeaseStatus::Active,
            'expires_at' => now()->addMinutes(fake()->numberBetween(1, 9)),
        ]);
    }

    public function personal(): static
    {
        return $this->state(fn (array $attributes) => [
            'organization_id' => null,
            'credential_scope' => 'personal',
            'included_org_credentials' => null,
        ]);
    }

    public function organizational(): static
    {
        return $this->state(fn (array $attributes) => [
            'organization_id' => Organization::factory(),
            'credential_scope' => 'organization',
            'included_org_credentials' => $attributes['services'],
        ]);
    }

    public function forUser(User $user): static
    {
        return $this->state(fn (array $attributes) => [
            'user_id' => $user->id,
        ]);
    }

    public function forOrganization(Organization $organization): static
    {
        return $this->state(fn (array $attributes) => [
            'organization_id' => $organization->id,
            'credential_scope' => 'organization',
            'included_org_credentials' => $attributes['services'],
        ]);
    }
}
