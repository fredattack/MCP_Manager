<?php

namespace Tests\Feature;

use App\Enums\IntegrationStatus;
use App\Enums\IntegrationType;
use App\Models\IntegrationAccount;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class NotionIntegrationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Configure the MCP server URL for tests
        config(['services.mcp.url' => 'http://mcp-test-server']);
    }

    public function test_user_can_fetch_notion_pages_tree()
    {
        // Create a user with an active Notion integration
        $user = User::factory()->create();
        $integration = IntegrationAccount::factory()->notion()->create([
            'user_id' => $user->id,
            'access_token' => 'notion-token',
        ]);

        // Mock the HTTP response
        Http::fake([
            '*' => Http::response([
                ['id' => 'page-1', 'title' => 'Page 1'],
                ['id' => 'page-2', 'title' => 'Page 2'],
            ], 200),
        ]);

        // Act as the user and make the request
        $response = $this->actingAs($user)->getJson('/api/notion/pages-tree');

        // Assert the response is successful and contains the pages
        $response->assertStatus(200)
            ->assertJsonCount(2)
            ->assertJsonFragment(['id' => 'page-1', 'title' => 'Page 1'])
            ->assertJsonFragment(['id' => 'page-2', 'title' => 'Page 2']);

        // Assert the HTTP request was made with the correct token
        Http::assertSent(function ($request) {
            return $request->hasHeader('Authorization', 'Bearer notion-token');
        });
    }

    public function test_user_can_fetch_notion_pages_tree_with_page_id()
    {
        // Create a user with an active Notion integration
        $user = User::factory()->create();
        $integration = IntegrationAccount::factory()->notion()->create([
            'user_id' => $user->id,
            'access_token' => 'notion-token',
        ]);

        // Mock the HTTP response
        Http::fake([
            'http://mcp-test-server/notion/pages-tree*' => Http::response([
                ['id' => 'subpage-1', 'title' => 'Subpage 1'],
                ['id' => 'subpage-2', 'title' => 'Subpage 2'],
            ], 200),
        ]);

        // Act as the user and make the request with a page_id
        $response = $this->actingAs($user)->getJson('/api/notion/pages-tree?page_id=parent-page-id');

        // Assert the response is successful and contains the subpages
        $response->assertStatus(200)
            ->assertJsonCount(2)
            ->assertJsonFragment(['id' => 'subpage-1', 'title' => 'Subpage 1'])
            ->assertJsonFragment(['id' => 'subpage-2', 'title' => 'Subpage 2']);

        // Assert the HTTP request was made with the correct token and page_id
        Http::assertSent(function ($request) {
            return $request->url() === 'http://mcp-test-server/notion/pages-tree?page_id=parent-page-id' &&
                   $request->hasHeader('Authorization', 'Bearer notion-token');
        });
    }

    public function test_user_can_fetch_notion_databases()
    {
        // Create a user with an active Notion integration
        $user = User::factory()->create();
        $integration = IntegrationAccount::factory()->notion()->create([
            'user_id' => $user->id,
            'access_token' => 'notion-token',
        ]);

        // Mock the HTTP response
        Http::fake([
            'http://mcp-test-server/notion/databases' => Http::response([
                ['id' => 'db-1', 'title' => 'Database 1'],
                ['id' => 'db-2', 'title' => 'Database 2'],
            ], 200),
        ]);

        // Act as the user and make the request
        $response = $this->actingAs($user)->getJson('/api/notion/databases');

        // Assert the response is successful and contains the databases
        $response->assertStatus(200)
            ->assertJsonCount(2)
            ->assertJsonFragment(['id' => 'db-1', 'title' => 'Database 1'])
            ->assertJsonFragment(['id' => 'db-2', 'title' => 'Database 2']);

        // Assert the HTTP request was made with the correct token
        Http::assertSent(function ($request) {
            return $request->url() === 'http://mcp-test-server/notion/databases' &&
                   $request->hasHeader('Authorization', 'Bearer notion-token');
        });
    }

    public function test_user_can_fetch_notion_page()
    {
        // Create a user with an active Notion integration
        $user = User::factory()->create();
        $integration = IntegrationAccount::factory()->notion()->create([
            'user_id' => $user->id,
            'access_token' => 'notion-token',
        ]);

        // Mock the HTTP response
        Http::fake([
            'http://mcp-test-server/notion/page/page-id' => Http::response([
                'id' => 'page-id',
                'title' => 'Page Title',
                'content' => 'Page content',
            ], 200),
        ]);

        // Act as the user and make the request
        $response = $this->actingAs($user)->getJson('/api/notion/page/page-id');

        // Assert the response is successful and contains the page
        $response->assertStatus(200)
            ->assertJsonFragment([
                'id' => 'page-id',
                'title' => 'Page Title',
                'content' => 'Page content',
            ]);

        // Assert the HTTP request was made with the correct token
        Http::assertSent(function ($request) {
            return $request->url() === 'http://mcp-test-server/notion/page/page-id' &&
                   $request->hasHeader('Authorization', 'Bearer notion-token');
        });
    }

    public function test_user_can_fetch_notion_blocks()
    {
        // Create a user with an active Notion integration
        $user = User::factory()->create();
        $integration = IntegrationAccount::factory()->notion()->create([
            'user_id' => $user->id,
            'access_token' => 'notion-token',
        ]);

        // Mock the HTTP response
        Http::fake([
            'http://mcp-test-server/notion/blocks/page-id' => Http::response([
                ['id' => 'block-1', 'type' => 'paragraph', 'content' => 'Block 1 content'],
                ['id' => 'block-2', 'type' => 'heading', 'content' => 'Block 2 content'],
            ], 200),
        ]);

        // Act as the user and make the request
        $response = $this->actingAs($user)->getJson('/api/notion/blocks/page-id');

        // Assert the response is successful and contains the blocks
        $response->assertStatus(200)
            ->assertJsonCount(2)
            ->assertJsonFragment(['id' => 'block-1', 'type' => 'paragraph', 'content' => 'Block 1 content'])
            ->assertJsonFragment(['id' => 'block-2', 'type' => 'heading', 'content' => 'Block 2 content']);

        // Assert the HTTP request was made with the correct token
        Http::assertSent(function ($request) {
            return $request->url() === 'http://mcp-test-server/notion/blocks/page-id' &&
                   $request->hasHeader('Authorization', 'Bearer notion-token');
        });
    }

    public function test_user_without_notion_integration_cannot_access_endpoints()
    {
        // Create a user without a Notion integration
        $user = User::factory()->create();

        // Act as the user and make requests to various endpoints
        $pagesResponse = $this->actingAs($user)->getJson('/api/notion/pages-tree');
        $databasesResponse = $this->actingAs($user)->getJson('/api/notion/databases');
        $pageResponse = $this->actingAs($user)->getJson('/api/notion/page/page-id');
        $blocksResponse = $this->actingAs($user)->getJson('/api/notion/blocks/page-id');

        // Assert all responses indicate that no active Notion integration was found
        $pagesResponse->assertStatus(403)
            ->assertJsonFragment(['message' => 'No active Notion integration found']);
        $databasesResponse->assertStatus(403)
            ->assertJsonFragment(['message' => 'No active Notion integration found']);
        $pageResponse->assertStatus(403)
            ->assertJsonFragment(['message' => 'No active Notion integration found']);
        $blocksResponse->assertStatus(403)
            ->assertJsonFragment(['message' => 'No active Notion integration found']);
    }

    public function test_user_with_inactive_notion_integration_cannot_access_endpoints()
    {
        // Create a user with an inactive Notion integration
        $user = User::factory()->create();
        $integration = IntegrationAccount::factory()->notion()->inactive()->create([
            'user_id' => $user->id,
        ]);

        // Act as the user and make a request
        $response = $this->actingAs($user)->getJson('/api/notion/pages-tree');

        // Assert the response indicates that no active Notion integration was found
        $response->assertStatus(403)
            ->assertJsonFragment(['message' => 'No active Notion integration found']);
    }

    public function test_unauthenticated_user_cannot_access_endpoints()
    {
        // Make requests to various endpoints without authentication
        $pagesResponse = $this->getJson('/api/notion/pages-tree');
        $databasesResponse = $this->getJson('/api/notion/databases');
        $pageResponse = $this->getJson('/api/notion/page/page-id');
        $blocksResponse = $this->getJson('/api/notion/blocks/page-id');

        // Assert all responses indicate unauthenticated
        $pagesResponse->assertStatus(401);
        $databasesResponse->assertStatus(401);
        $pageResponse->assertStatus(401);
        $blocksResponse->assertStatus(401);
    }
}
