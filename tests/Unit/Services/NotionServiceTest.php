<?php

namespace Tests\Unit\Services;

use App\Models\IntegrationAccount;
use App\Services\NotionService;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class NotionServiceTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // Set up configuration for tests
        Config::set('services.mcp.url', 'http://mcp-test-server');
        Config::set('services.mcp.token', 'test-token');
        Config::set('services.mcp.default_page_id', 'test-page-id');
    }

    public function test_fetch_notion_pages_successful_with_default_page_id()
    {
        // Mock response data
        $mockResponseData = [
            ['id' => '1', 'title' => 'Test Page 1', 'url' => 'http://example.com/1'],
            ['id' => '2', 'title' => 'Test Page 2', 'url' => 'http://example.com/2'],
        ];

        // Mock HTTP client
        Http::fake([
            '*' => Http::response($mockResponseData, 200),
        ]);

        // Create service and call method
        $notionService = new NotionService;
        $result = $notionService->fetchNotionPages();

        // Assert HTTP request was made correctly
        Http::assertSent(function (Request $request) {
            return $request->url() === 'http://mcp-test-server/notion/pages-tree?page_id=test-page-id' &&
                   $request->hasHeader('Authorization', 'Bearer test-token');
        });

        // Assert result matches mock data
        $this->assertEquals($mockResponseData, $result);
    }

    public function test_fetch_notion_pages_successful_with_explicit_page_id()
    {
        // Mock response data
        $mockResponseData = [
            ['id' => '1', 'title' => 'Test Page 1', 'url' => 'http://example.com/1'],
            ['id' => '2', 'title' => 'Test Page 2', 'url' => 'http://example.com/2'],
        ];

        // Mock HTTP client
        Http::fake([
            '*' => Http::response($mockResponseData, 200),
        ]);

        // Create service and call method with explicit page_id
        $notionService = new NotionService;
        $result = $notionService->fetchNotionPages('explicit-page-id');

        // Assert HTTP request was made correctly with the explicit page_id
        Http::assertSent(function (Request $request) {
            return $request->url() === 'http://mcp-test-server/notion/pages-tree?page_id=explicit-page-id' &&
                   $request->hasHeader('Authorization', 'Bearer test-token');
        });

        // Assert result matches mock data
        $this->assertEquals($mockResponseData, $result);
    }

    public function test_fetch_notion_pages_throws_exception_when_config_missing()
    {
        // Clear configuration
        Config::set('services.mcp.url', null);

        // Create service and expect exception
        try {
            $notionService = new NotionService;
            $this->fail('Expected exception was not thrown');
        } catch (\Exception $e) {
            $this->assertEquals('MCP Server URL not configured', $e->getMessage());
        }
    }

    public function test_fetch_notion_pages_throws_exception_when_token_missing()
    {
        // Set URL but clear token
        Config::set('services.mcp.url', 'http://mcp-test-server');
        Config::set('services.mcp.token', null);

        // Create service and expect exception
        try {
            $notionService = new NotionService;
            $this->fail('Expected exception was not thrown');
        } catch (\Exception $e) {
            $this->assertEquals('MCP API Token not configured', $e->getMessage());
        }
    }

    public function test_fetch_notion_pages_throws_exception_when_request_fails()
    {
        // Mock HTTP client to return error
        Http::fake([
            '*' => Http::response('Server error', 500),
        ]);

        // Create service
        $notionService = new NotionService;

        // Assert exception is thrown
        $this->expectException(\Exception::class);
        $this->expectExceptionMessageMatches('/Failed to fetch from \/notion\/pages-tree/');

        // Call method
        $notionService->fetchNotionPages();
    }

    public function test_fetch_notion_pages_successful_without_page_id()
    {
        // Clear the default page ID configuration
        Config::set('services.mcp.default_page_id', null);

        // Mock response data
        $mockResponseData = [
            ['id' => '1', 'title' => 'All Pages 1', 'url' => 'http://example.com/1'],
            ['id' => '2', 'title' => 'All Pages 2', 'url' => 'http://example.com/2'],
        ];

        // Mock HTTP client
        Http::fake([
            '*' => Http::response($mockResponseData, 200),
        ]);

        // Create service and call method
        $notionService = new NotionService;
        $result = $notionService->fetchNotionPages();

        // Assert HTTP request was made correctly without page_id parameter
        Http::assertSent(function (Request $request) {
            return $request->url() === 'http://mcp-test-server/notion/pages-tree' &&
                   $request->hasHeader('Authorization', 'Bearer test-token');
        });

        // Assert result matches mock data
        $this->assertEquals($mockResponseData, $result);
    }

    public function test_fetch_notion_pages_with_integration_account()
    {
        // Create a mock integration account
        $integrationAccount = new IntegrationAccount;
        $integrationAccount->access_token = 'integration-token';

        // Mock response data
        $mockResponseData = [
            ['id' => '1', 'title' => 'Integration Page 1', 'url' => 'http://example.com/1'],
            ['id' => '2', 'title' => 'Integration Page 2', 'url' => 'http://example.com/2'],
        ];

        // Mock HTTP client
        Http::fake([
            '*' => Http::response($mockResponseData, 200),
        ]);

        // Create service with integration account and call method
        $notionService = new NotionService($integrationAccount);
        $result = $notionService->fetchNotionPages();

        // Assert HTTP request was made with integration token
        Http::assertSent(function (Request $request) {
            return $request->url() === 'http://mcp-test-server/notion/pages-tree?page_id=test-page-id' &&
                   $request->hasHeader('Authorization', 'Bearer integration-token');
        });

        // Assert result matches mock data
        $this->assertEquals($mockResponseData, $result);
    }

    public function test_fetch_notion_databases()
    {
        // Mock response data
        $mockResponseData = [
            ['id' => '1', 'title' => 'Database 1'],
            ['id' => '2', 'title' => 'Database 2'],
        ];

        // Mock HTTP client
        Http::fake([
            '*' => Http::response($mockResponseData, 200),
        ]);

        // Create service and call method
        $notionService = new NotionService;
        $result = $notionService->fetchNotionDatabases();

        // Assert HTTP request was made correctly
        Http::assertSent(function (Request $request) {
            return $request->url() === 'http://mcp-test-server/notion/databases' &&
                   $request->hasHeader('Authorization', 'Bearer test-token');
        });

        // Assert result matches mock data
        $this->assertEquals($mockResponseData, $result);
    }

    public function test_fetch_notion_page()
    {
        // Mock response data
        $mockResponseData = [
            'id' => 'page-id',
            'title' => 'Page Title',
            'content' => 'Page content',
        ];

        // Mock HTTP client
        Http::fake([
            '*' => Http::response($mockResponseData, 200),
        ]);

        // Create service and call method
        $notionService = new NotionService;
        $result = $notionService->fetchNotionPage('page-id');

        // Assert HTTP request was made correctly
        Http::assertSent(function (Request $request) {
            return $request->url() === 'http://mcp-test-server/notion/page/page-id' &&
                   $request->hasHeader('Authorization', 'Bearer test-token');
        });

        // Assert result matches mock data
        $this->assertEquals($mockResponseData, $result);
    }

    public function test_fetch_notion_blocks()
    {
        // Mock response data
        $mockResponseData = [
            ['id' => 'block-1', 'type' => 'paragraph', 'content' => 'Block 1 content'],
            ['id' => 'block-2', 'type' => 'heading', 'content' => 'Block 2 content'],
        ];

        // Mock HTTP client
        Http::fake([
            '*' => Http::response($mockResponseData, 200),
        ]);

        // Create service and call method
        $notionService = new NotionService;
        $result = $notionService->fetchNotionBlocks('page-id');

        // Assert HTTP request was made correctly
        Http::assertSent(function (Request $request) {
            return $request->url() === 'http://mcp-test-server/notion/blocks/page-id' &&
                   $request->hasHeader('Authorization', 'Bearer test-token');
        });

        // Assert result matches mock data
        $this->assertEquals($mockResponseData, $result);
    }
}
