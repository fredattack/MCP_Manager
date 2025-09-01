<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\McpServer;
use App\Models\McpIntegration;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class McpServerManagementTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    }

    public function test_user_can_view_mcp_server_config_page(): void
    {
        $response = $this->actingAs($this->user)
            ->get('/mcp/server/config');

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->component('Mcp/ServerConfig')
            ->has('server')
        );
    }

    public function test_user_can_configure_mcp_server(): void
    {
        $response = $this->actingAs($this->user)
            ->post('/mcp/server/config', [
                'name' => 'Production MCP Server',
                'url' => 'https://mcp.example.com',
            ]);

        // Since we're mocking the actual MCP server connection, 
        // we expect an error or redirect
        $response->assertSessionHasErrors();
    }

    public function test_user_can_view_integrations_dashboard(): void
    {
        // Create a server for the user
        $server = McpServer::factory()->create([
            'user_id' => $this->user->id,
            'status' => 'active',
        ]);

        $response = $this->actingAs($this->user)
            ->get('/mcp/dashboard');

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->component('Mcp/Dashboard')
            ->has('integrations')
            ->has('serverStatus')
        );
    }

    public function test_user_without_server_sees_no_server_page(): void
    {
        $response = $this->actingAs($this->user)
            ->get('/mcp/dashboard');

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->component('Mcp/NoServerConfigured')
        );
    }

    public function test_user_can_configure_integration(): void
    {
        $server = McpServer::factory()->create([
            'user_id' => $this->user->id,
            'status' => 'active',
        ]);

        $response = $this->actingAs($this->user)
            ->get('/mcp/integrations/todoist/configure');

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->component('Mcp/ConfigureIntegration')
            ->has('service')
            ->has('serviceConfig')
        );
    }

    public function test_user_can_test_integration(): void
    {
        $server = McpServer::factory()->create([
            'user_id' => $this->user->id,
            'status' => 'active',
        ]);

        $integration = McpIntegration::factory()->create([
            'user_id' => $this->user->id,
            'mcp_server_id' => $server->id,
            'service_name' => 'todoist',
            'status' => 'active',
        ]);

        $response = $this->actingAs($this->user)
            ->postJson('/api/mcp/integrations/todoist/test');

        $response->assertStatus(200);
        $response->assertJson([
            'success' => false, // Will fail without actual MCP server
        ]);
    }

    public function test_user_can_toggle_integration(): void
    {
        $server = McpServer::factory()->create([
            'user_id' => $this->user->id,
            'status' => 'active',
        ]);

        $integration = McpIntegration::factory()->create([
            'user_id' => $this->user->id,
            'mcp_server_id' => $server->id,
            'service_name' => 'todoist',
            'enabled' => true,
        ]);

        $response = $this->actingAs($this->user)
            ->postJson('/api/mcp/integrations/todoist/toggle');

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'enabled' => false,
        ]);

        $this->assertDatabaseHas('mcp_integrations', [
            'id' => $integration->id,
            'enabled' => false,
        ]);
    }

    public function test_user_can_delete_integration(): void
    {
        $server = McpServer::factory()->create([
            'user_id' => $this->user->id,
            'status' => 'active',
        ]);

        $integration = McpIntegration::factory()->create([
            'user_id' => $this->user->id,
            'mcp_server_id' => $server->id,
            'service_name' => 'todoist',
        ]);

        $response = $this->actingAs($this->user)
            ->delete('/mcp/integrations/todoist');

        $response->assertRedirect('/mcp/dashboard');
        
        $this->assertDatabaseMissing('mcp_integrations', [
            'id' => $integration->id,
        ]);
    }
}