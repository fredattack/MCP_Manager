<?php

namespace Tests\Feature\Http\Controllers;

use App\Enums\IntegrationStatus;
use App\Enums\IntegrationType;
use App\Models\IntegrationAccount;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * @group todoist
 * @group feature
 */
class TodoistIntegrationControllerTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $this->actingAs($this->user);
    }

    public function test_show_displays_setup_page(): void
    {
        $response = $this->get(route('integrations.todoist.setup'));

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page
            ->component('integrations/todoist-setup')
            ->has('integration', null)
        );
    }

    public function test_show_displays_existing_integration(): void
    {
        $integration = IntegrationAccount::factory()->create([
            'user_id' => $this->user->id,
            'type' => IntegrationType::TODOIST,
            'status' => IntegrationStatus::ACTIVE,
            'meta' => [
                'email' => 'test@example.com',
                'full_name' => 'Test User',
            ],
        ]);

        $response = $this->get(route('integrations.todoist.setup'));

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page
            ->component('integrations/todoist-setup')
            ->has('integration', fn ($page) => $page
                ->where('id', $integration->id)
                ->where('status', IntegrationStatus::ACTIVE->value)
                ->has('connected_at')
                ->has('meta')
                ->etc()
            )
        );
    }

    public function test_connect_requires_api_token(): void
    {
        $response = $this->post(route('integrations.todoist.connect'), []);

        $response->assertSessionHasErrors(['api_token']);
    }

    public function test_disconnect_removes_active_integration(): void
    {
        $integration = IntegrationAccount::factory()->create([
            'user_id' => $this->user->id,
            'type' => IntegrationType::TODOIST,
            'status' => IntegrationStatus::ACTIVE,
        ]);

        $response = $this->post(route('integrations.todoist.disconnect'));

        $response->assertRedirect(route('integrations.todoist.setup'));
        $response->assertSessionHas('success', 'Todoist account disconnected successfully.');

        $this->assertDatabaseHas('integration_accounts', [
            'id' => $integration->id,
            'status' => IntegrationStatus::INACTIVE,
            'access_token' => null,
        ]);
    }

    public function test_test_connection_requires_active_integration(): void
    {
        $response = $this->post(route('integrations.todoist.test'));

        $response->assertRedirect();
        $response->assertSessionHasErrors(['connection']);
    }
}
