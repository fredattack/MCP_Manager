<?php

namespace Tests\Unit\Services;

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
        $notionService = new NotionService();
        $result = $notionService->fetchNotionPages();

        // Assert HTTP request was made correctly
        Http::assertSent(function (Request $request) {
            return $request->url() === 'http://mcp-test-server/notion/fetch?page_id=test-page-id' &&
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
        $notionService = new NotionService();
        $result = $notionService->fetchNotionPages('explicit-page-id');

        // Assert HTTP request was made correctly with the explicit page_id
        Http::assertSent(function (Request $request) {
            return $request->url() === 'http://mcp-test-server/notion/fetch?page_id=explicit-page-id' &&
                   $request->hasHeader('Authorization', 'Bearer test-token');
        });

        // Assert result matches mock data
        $this->assertEquals($mockResponseData, $result);
    }

    public function test_fetch_notion_pages_throws_exception_when_config_missing()
    {
        // Clear configuration
        Config::set('services.mcp.url', null);
        Config::set('services.mcp.token', null);

        // Create service
        $notionService = new NotionService();

        // Assert exception is thrown
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('MCP Server URL or API Token not configured');

        // Call method
        $notionService->fetchNotionPages();
    }

    public function test_fetch_notion_pages_throws_exception_when_request_fails()
    {
        // Mock HTTP client to return error
        Http::fake([
            '*' => Http::response('Server error', 500),
        ]);

        // Create service
        $notionService = new NotionService();

        // Assert exception is thrown
        $this->expectException(\Exception::class);
        $this->expectExceptionMessageMatches('/Failed to fetch Notion pages/');

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
        $notionService = new NotionService();
        $result = $notionService->fetchNotionPages();

        // Assert HTTP request was made correctly without page_id parameter
        Http::assertSent(function (Request $request) {
            return $request->url() === 'http://mcp-test-server/notion/fetch' &&
                   $request->hasHeader('Authorization', 'Bearer test-token');
        });

        // Assert result matches mock data
        $this->assertEquals($mockResponseData, $result);
    }
}
