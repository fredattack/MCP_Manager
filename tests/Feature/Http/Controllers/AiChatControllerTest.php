<?php

namespace Tests\Feature\Http\Controllers;

use Tests\TestCase;
use App\Models\User;
use App\Services\McpAuthService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Mockery;

class AiChatControllerTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected McpAuthService $mcpAuthService;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->user = User::factory()->create();
        $this->mcpAuthService = Mockery::mock(McpAuthService::class);
        $this->app->instance(McpAuthService::class, $this->mcpAuthService);
    }

    public function test_chat_requires_authentication()
    {
        $response = $this->postJson('/api/ai/chat', [
            'messages' => [['role' => 'user', 'content' => 'Hello']],
        ]);

        $response->assertRedirect('/login');
    }

    public function test_chat_validates_request_data()
    {
        $response = $this->actingAs($this->user)
            ->postJson('/api/ai/chat', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['messages']);
    }

    public function test_chat_validates_message_structure()
    {
        $response = $this->actingAs($this->user)
            ->postJson('/api/ai/chat', [
                'messages' => [
                    ['role' => 'invalid', 'content' => 'Hello'],
                ],
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['messages.0.role']);
    }

    public function test_chat_validates_model_parameter()
    {
        $response = $this->actingAs($this->user)
            ->postJson('/api/ai/chat', [
                'messages' => [['role' => 'user', 'content' => 'Hello']],
                'model' => 'invalid-model',
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['model']);
    }

    public function test_chat_successful_non_streaming_response()
    {
        $this->mcpAuthService->shouldReceive('getAccessToken')
            ->once()
            ->andReturn('test-token');

        Http::fake([
            '*/claude/chat' => Http::response([
                'content' => 'Hello! How can I help you?',
                'usage' => ['total_tokens' => 50],
                'id' => 'msg_123',
            ], 200),
        ]);

        $response = $this->actingAs($this->user)
            ->postJson('/api/ai/chat', [
                'messages' => [
                    ['role' => 'user', 'content' => 'Hello AI!'],
                ],
                'model' => 'gpt-4',
                'temperature' => 0.7,
                'max_tokens' => 1000,
                'stream' => false,
            ]);

        $response->assertOk()
            ->assertJson([
                'content' => 'Hello! How can I help you?',
                'model' => 'gpt-4',
                'usage' => ['total_tokens' => 50],
                'metadata' => [
                    'request_id' => 'msg_123',
                ],
            ]);
    }

    public function test_chat_handles_mcp_server_error()
    {
        $this->mcpAuthService->shouldReceive('getAccessToken')
            ->once()
            ->andReturn('test-token');

        Http::fake([
            '*/claude/chat' => Http::response([
                'error' => 'Internal server error',
            ], 500),
        ]);

        $response = $this->actingAs($this->user)
            ->postJson('/api/ai/chat', [
                'messages' => [
                    ['role' => 'user', 'content' => 'Hello AI!'],
                ],
            ]);

        $response->assertStatus(500)
            ->assertJson([
                'error' => 'Failed to communicate with AI service',
                'status' => 500,
            ]);
    }

    public function test_chat_handles_authentication_failure()
    {
        $this->mcpAuthService->shouldReceive('getAccessToken')
            ->once()
            ->andReturn('');

        $response = $this->actingAs($this->user)
            ->postJson('/api/ai/chat', [
                'messages' => [
                    ['role' => 'user', 'content' => 'Hello AI!'],
                ],
            ]);

        $response->assertStatus(500)
            ->assertJson([
                'error' => 'Failed to authenticate with AI service',
            ]);
    }

    public function test_chat_extracts_last_message_content()
    {
        $this->mcpAuthService->shouldReceive('getAccessToken')
            ->once()
            ->andReturn('test-token');

        Http::fake([
            '*/claude/chat' => Http::response([
                'content' => 'Response to last message',
            ], 200),
        ]);

        $response = $this->actingAs($this->user)
            ->postJson('/api/ai/chat', [
                'messages' => [
                    ['role' => 'user', 'content' => 'First message'],
                    ['role' => 'assistant', 'content' => 'First response'],
                    ['role' => 'user', 'content' => 'Last message'],
                ],
            ]);

        $response->assertOk();

        // Verify the last message was sent to MCP server
        Http::assertSent(function ($request) {
            $body = json_decode($request->body(), true);
            return $body['message'] === 'Last message';
        });
    }

    public function test_chat_returns_streamed_response_when_enabled()
    {
        $this->mcpAuthService->shouldReceive('getAccessToken')
            ->once()
            ->andReturn('test-token');

        $response = $this->actingAs($this->user)
            ->postJson('/api/ai/chat', [
                'messages' => [
                    ['role' => 'user', 'content' => 'Hello AI!'],
                ],
                'stream' => true,
            ]);

        $response->assertOk();
        $response->assertHeader('Content-Type', 'text/event-stream; charset=UTF-8');
    }

    public function test_chat_respects_temperature_parameter()
    {
        $this->mcpAuthService->shouldReceive('getAccessToken')
            ->once()
            ->andReturn('test-token');

        Http::fake([
            '*/claude/chat' => Http::response(['content' => 'Response'], 200),
        ]);

        $response = $this->actingAs($this->user)
            ->postJson('/api/ai/chat', [
                'messages' => [['role' => 'user', 'content' => 'Hello']],
                'temperature' => 1.5,
            ]);

        $response->assertOk();

        Http::assertSent(function ($request) {
            $body = json_decode($request->body(), true);
            return $body['temperature'] === 1.5;
        });
    }

    public function test_chat_respects_max_tokens_parameter()
    {
        $this->mcpAuthService->shouldReceive('getAccessToken')
            ->once()
            ->andReturn('test-token');

        Http::fake([
            '*/claude/chat' => Http::response(['content' => 'Response'], 200),
        ]);

        $response = $this->actingAs($this->user)
            ->postJson('/api/ai/chat', [
                'messages' => [['role' => 'user', 'content' => 'Hello']],
                'max_tokens' => 2000,
            ]);

        $response->assertOk();

        Http::assertSent(function ($request) {
            $body = json_decode($request->body(), true);
            return $body['max_tokens'] === 2000;
        });
    }

    public function test_chat_handles_network_exception()
    {
        $this->mcpAuthService->shouldReceive('getAccessToken')
            ->once()
            ->andReturn('test-token');

        Http::fake(function () {
            throw new \Exception('Network error');
        });

        $response = $this->actingAs($this->user)
            ->postJson('/api/ai/chat', [
                'messages' => [['role' => 'user', 'content' => 'Hello']],
            ]);

        $response->assertStatus(500)
            ->assertJson([
                'error' => 'Internal server error',
            ]);

        if (app()->environment('local')) {
            $response->assertJsonFragment([
                'message' => 'Network error',
            ]);
        }
    }

    public function test_chat_validates_empty_messages_array()
    {
        $this->mcpAuthService->shouldReceive('getAccessToken')
            ->once()
            ->andReturn('test-token');

        $response = $this->actingAs($this->user)
            ->postJson('/api/ai/chat', [
                'messages' => [],
            ]);

        $response->assertStatus(400)
            ->assertJson([
                'error' => 'Invalid messages format',
            ]);
    }

    public function test_chat_validates_message_content_exists()
    {
        $response = $this->actingAs($this->user)
            ->postJson('/api/ai/chat', [
                'messages' => [
                    ['role' => 'user'],
                ],
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['messages.0.content']);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
}