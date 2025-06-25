<?php

namespace Tests\Feature;

use App\Enums\IntegrationStatus;
use App\Enums\IntegrationType;
use App\Models\IntegrationAccount;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class IntegrationsTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_list_their_integrations()
    {
        // Create a user with integrations
        $user = User::factory()->create();
        $notionIntegration = IntegrationAccount::factory()->notion()->create(['user_id' => $user->id]);
        $gmailIntegration = IntegrationAccount::factory()->gmail()->create(['user_id' => $user->id]);

        // Act as the user and make the request
        $response = $this->actingAs($user)->getJson('/api/integrations');

        // Assert the response is successful and contains the integrations
        $response->assertStatus(200)
            ->assertJsonCount(2)
            ->assertJsonFragment(['id' => $notionIntegration->id])
            ->assertJsonFragment(['id' => $gmailIntegration->id]);
    }

    public function test_user_can_create_an_integration()
    {
        // Create a user
        $user = User::factory()->create();

        // Prepare the data for the request
        $data = [
            'type' => IntegrationType::NOTION->value,
            'access_token' => 'test-token',
            'meta' => ['workspace_name' => 'Test Workspace'],
        ];

        // Act as the user and make the request
        $response = $this->actingAs($user)->postJson('/api/integrations', $data);

        // Assert the response is successful and the integration was created
        $response->assertStatus(201)
            ->assertJsonFragment([
                'type' => IntegrationType::NOTION->value,
                'status' => IntegrationStatus::ACTIVE->value,
            ]);

        // Assert the integration exists in the database
        $this->assertDatabaseHas('integration_accounts', [
            'user_id' => $user->id,
            'type' => IntegrationType::NOTION->value,
            'access_token' => 'test-token',
            'status' => IntegrationStatus::ACTIVE->value,
        ]);
    }

    public function test_user_cannot_create_duplicate_active_integration()
    {
        // Create a user with an active Notion integration
        $user = User::factory()->create();
        IntegrationAccount::factory()->notion()->create(['user_id' => $user->id]);

        // Prepare the data for the request
        $data = [
            'type' => IntegrationType::NOTION->value,
            'access_token' => 'another-token',
        ];

        // Act as the user and make the request
        $response = $this->actingAs($user)->postJson('/api/integrations', $data);

        // Assert the response indicates a validation error
        $response->assertStatus(422)
            ->assertJsonFragment(['message' => 'You already have an active integration of this type.']);
    }

    public function test_user_can_update_their_integration()
    {
        // Create a user with an integration
        $user = User::factory()->create();
        $integration = IntegrationAccount::factory()->notion()->create(['user_id' => $user->id]);

        // Prepare the data for the request
        $data = [
            'access_token' => 'updated-token',
            'meta' => ['workspace_name' => 'Updated Workspace'],
            'status' => IntegrationStatus::INACTIVE->value,
        ];

        // Act as the user and make the request
        $response = $this->actingAs($user)->putJson("/api/integrations/{$integration->id}", $data);

        // Assert the response is successful and the integration was updated
        $response->assertStatus(200)
            ->assertJsonFragment([
                'access_token' => 'updated-token',
                'status' => IntegrationStatus::INACTIVE->value,
            ]);

        // Assert the integration was updated in the database
        $this->assertDatabaseHas('integration_accounts', [
            'id' => $integration->id,
            'access_token' => 'updated-token',
            'status' => IntegrationStatus::INACTIVE->value,
        ]);
    }

    public function test_user_cannot_update_another_users_integration()
    {
        // Create two users
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();

        // Create an integration for user2
        $integration = IntegrationAccount::factory()->notion()->create(['user_id' => $user2->id]);

        // Prepare the data for the request
        $data = [
            'access_token' => 'hacked-token',
        ];

        // Act as user1 and try to update user2's integration
        $response = $this->actingAs($user1)->putJson("/api/integrations/{$integration->id}", $data);

        // Assert the response indicates unauthorized
        $response->assertStatus(403);

        // Assert the integration was not updated
        $this->assertDatabaseMissing('integration_accounts', [
            'id' => $integration->id,
            'access_token' => 'hacked-token',
        ]);
    }

    public function test_user_can_delete_their_integration()
    {
        // Create a user with an integration
        $user = User::factory()->create();
        $integration = IntegrationAccount::factory()->notion()->create(['user_id' => $user->id]);

        // Act as the user and make the request
        $response = $this->actingAs($user)->deleteJson("/api/integrations/{$integration->id}");

        // Assert the response is successful
        $response->assertStatus(200)
            ->assertJsonFragment(['message' => 'Integration deleted successfully']);

        // Assert the integration was deleted from the database
        $this->assertDatabaseMissing('integration_accounts', [
            'id' => $integration->id,
        ]);
    }

    public function test_user_cannot_delete_another_users_integration()
    {
        // Create two users
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();

        // Create an integration for user2
        $integration = IntegrationAccount::factory()->notion()->create(['user_id' => $user2->id]);

        // Act as user1 and try to delete user2's integration
        $response = $this->actingAs($user1)->deleteJson("/api/integrations/{$integration->id}");

        // Assert the response indicates unauthorized
        $response->assertStatus(403);

        // Assert the integration was not deleted
        $this->assertDatabaseHas('integration_accounts', [
            'id' => $integration->id,
        ]);
    }
}
