<?php

namespace Tests\Unit\Models;

use App\Enums\IntegrationStatus;
use App\Enums\IntegrationType;
use App\Models\IntegrationAccount;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class IntegrationAccountTest extends TestCase
{
    use RefreshDatabase;

    public function test_integration_account_belongs_to_user()
    {
        // Create a user
        $user = User::factory()->create();

        // Create an integration account for the user
        $integrationAccount = IntegrationAccount::factory()->create([
            'user_id' => $user->id,
            'type' => IntegrationType::NOTION,
            'access_token' => 'test-token',
            'status' => IntegrationStatus::ACTIVE,
        ]);

        // Assert the relationship works correctly
        $this->assertInstanceOf(User::class, $integrationAccount->user);
        $this->assertEquals($user->id, $integrationAccount->user->id);
    }

    public function test_integration_account_casts_type_to_enum()
    {
        // Create an integration account
        $integrationAccount = IntegrationAccount::factory()->create([
            'type' => IntegrationType::NOTION,
        ]);

        // Assert the type is cast to an enum
        $this->assertInstanceOf(IntegrationType::class, $integrationAccount->type);
        $this->assertEquals(IntegrationType::NOTION, $integrationAccount->type);
    }

    public function test_integration_account_casts_status_to_enum()
    {
        // Create an integration account
        $integrationAccount = IntegrationAccount::factory()->create([
            'status' => IntegrationStatus::ACTIVE,
        ]);

        // Assert the status is cast to an enum
        $this->assertInstanceOf(IntegrationStatus::class, $integrationAccount->status);
        $this->assertEquals(IntegrationStatus::ACTIVE, $integrationAccount->status);
    }

    public function test_integration_account_casts_meta_to_array()
    {
        // Create an integration account with meta data
        $metaData = ['workspace_name' => 'Test Workspace', 'user_id' => '123456'];
        $integrationAccount = IntegrationAccount::factory()->create([
            'meta' => $metaData,
        ]);

        // Assert the meta is cast to an array
        $this->assertIsArray($integrationAccount->meta);
        $this->assertEquals($metaData, $integrationAccount->meta);
    }

    public function test_user_can_have_multiple_integration_accounts()
    {
        // Create a user
        $user = User::factory()->create();

        // Create multiple integration accounts for the user
        $notionAccount = IntegrationAccount::factory()->create([
            'user_id' => $user->id,
            'type' => IntegrationType::NOTION,
        ]);

        $gmailAccount = IntegrationAccount::factory()->create([
            'user_id' => $user->id,
            'type' => IntegrationType::GMAIL,
        ]);

        // Assert the user has multiple integration accounts
        $this->assertCount(2, $user->integrationAccounts);
        $this->assertTrue($user->integrationAccounts->contains($notionAccount));
        $this->assertTrue($user->integrationAccounts->contains($gmailAccount));
    }
}
