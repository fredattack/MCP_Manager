<?php

namespace Tests\Feature\Api\Mcp;

use App\Models\User;
use App\Models\UserToken;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class McpAuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_authenticate_with_valid_token(): void
    {
        $user = User::factory()->create();
        $token = UserToken::factory()->mcpServer()->create([
            'user_id' => $user->id,
        ]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer '.$token->token,
        ])->getJson('/api/mcp/me');

        $response->assertOk();
        $response->assertJson([
            'user_id' => $user->id,
            'email' => $user->email,
            'name' => $user->name,
        ]);
    }

    public function test_cannot_authenticate_without_token(): void
    {
        $response = $this->getJson('/api/mcp/me');

        $response->assertUnauthorized();
        $response->assertJson([
            'message' => 'Unauthenticated',
        ]);
    }

    public function test_cannot_authenticate_with_invalid_token(): void
    {
        $response = $this->withHeaders([
            'Authorization' => 'Bearer invalid_token_here',
        ])->getJson('/api/mcp/me');

        $response->assertUnauthorized();
    }

    public function test_cannot_authenticate_with_expired_token(): void
    {
        $user = User::factory()->create();
        $token = UserToken::factory()->mcpServer()->expired()->create([
            'user_id' => $user->id,
        ]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer '.$token->token,
        ])->getJson('/api/mcp/me');

        $response->assertUnauthorized();
        $response->assertJsonFragment([
            'error' => 'Token has expired',
        ]);
    }

    public function test_cannot_authenticate_with_inactive_token(): void
    {
        $user = User::factory()->create();
        $token = UserToken::factory()->mcpServer()->inactive()->create([
            'user_id' => $user->id,
        ]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer '.$token->token,
        ])->getJson('/api/mcp/me');

        $response->assertUnauthorized();
    }

    public function test_token_usage_is_tracked(): void
    {
        $user = User::factory()->create();
        $token = UserToken::factory()->mcpServer()->create([
            'user_id' => $user->id,
            'usage_count' => 5,
        ]);

        $this->withHeaders([
            'Authorization' => 'Bearer '.$token->token,
        ])->getJson('/api/mcp/me');

        $token->refresh();
        $this->assertEquals(6, $token->usage_count);
        $this->assertNotNull($token->last_used_at);
    }

    public function test_authentication_logs_access(): void
    {
        $user = User::factory()->create();
        $token = UserToken::factory()->mcpServer()->create([
            'user_id' => $user->id,
        ]);

        $this->withHeaders([
            'Authorization' => 'Bearer '.$token->token,
        ])->getJson('/api/mcp/me');

        $this->assertDatabaseHas('user_activity_logs', [
            'user_id' => $user->id,
            'action' => 'mcp_auth_success',
        ]);
    }

    public function test_failed_authentication_logs_access(): void
    {
        $response = $this->withHeaders([
            'Authorization' => 'Bearer invalid_token',
        ])->getJson('/api/mcp/me');

        $response->assertUnauthorized();

        $this->assertDatabaseHas('user_activity_logs', [
            'action' => 'mcp_auth_failed',
        ]);
    }

    public function test_returns_user_organizations_info(): void
    {
        $user = User::factory()->create();
        $token = UserToken::factory()->mcpServer()->create([
            'user_id' => $user->id,
        ]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer '.$token->token,
        ])->getJson('/api/mcp/me');

        $response->assertOk();
        $response->assertJsonStructure([
            'user_id',
            'email',
            'name',
            'organizations',
            'available_services',
        ]);
    }

    public function test_bearer_token_must_have_bearer_prefix(): void
    {
        $user = User::factory()->create();
        $token = UserToken::factory()->mcpServer()->create([
            'user_id' => $user->id,
        ]);

        // Without "Bearer " prefix
        $response = $this->withHeaders([
            'Authorization' => $token->token,
        ])->getJson('/api/mcp/me');

        $response->assertUnauthorized();
    }

    public function test_token_with_max_usages_is_rejected_when_exceeded(): void
    {
        $user = User::factory()->create();
        $token = UserToken::factory()->mcpServer()->limitedUsage(10)->create([
            'user_id' => $user->id,
            'usage_count' => 10,
        ]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer '.$token->token,
        ])->getJson('/api/mcp/me');

        $response->assertUnauthorized();
        $response->assertJsonFragment([
            'error' => 'Token usage limit exceeded',
        ]);
    }
}
