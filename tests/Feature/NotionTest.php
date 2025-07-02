<?php

namespace Tests\Feature;

use App\Services\NotionService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;
use Tests\TestCase;

class NotionTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_are_redirected_to_the_login_page()
    {
        $this->get('/notion')->assertRedirect('/login');
    }

    public function test_authenticated_users_can_visit_the_notion_page()
    {
        $this->actingAs($user = \App\Models\User::factory()->create());

        // Mock the Inertia response to avoid Vite compilation issues in tests
        $this->withoutExceptionHandling()
            ->withoutVite()
            ->get('/notion')
            ->assertInertia(fn ($assert) => $assert->component('notion'));
    }

    public function test_api_returns_notion_pages_with_default_page_id()
    {
        // Mock the NotionService
        $mockService = Mockery::mock(NotionService::class);
        $mockData = [
            ['id' => '1', 'title' => 'Test Page 1', 'url' => 'http://example.com/1'],
            ['id' => '2', 'title' => 'Test Page 2', 'url' => 'http://example.com/2'],
        ];

        // Expect fetchNotionPages to be called with null (using default page_id)
        $mockService->shouldReceive('fetchNotionPages')
            ->once()
            ->with(null)
            ->andReturn($mockData);

        $this->app->instance(NotionService::class, $mockService);

        // Make the API request without page_id parameter
        $response = $this->getJson('/api/notion/fetch');

        // Assert the response
        $response->assertStatus(200)
            ->assertJson($mockData);
    }

    public function test_api_returns_notion_pages_with_explicit_page_id()
    {
        // Mock the NotionService
        $mockService = Mockery::mock(NotionService::class);
        $mockData = [
            ['id' => '1', 'title' => 'Test Page 1', 'url' => 'http://example.com/1'],
            ['id' => '2', 'title' => 'Test Page 2', 'url' => 'http://example.com/2'],
        ];

        // Expect fetchNotionPages to be called with the specific page_id
        $mockService->shouldReceive('fetchNotionPages')
            ->once()
            ->with('test-page-id')
            ->andReturn($mockData);

        $this->app->instance(NotionService::class, $mockService);

        // Make the API request with page_id parameter
        $response = $this->getJson('/api/notion/fetch?page_id=test-page-id');

        // Assert the response
        $response->assertStatus(200)
            ->assertJson($mockData);
    }

    public function test_api_handles_errors_with_default_page_id()
    {
        // Mock the NotionService to throw an exception
        $mockService = Mockery::mock(NotionService::class);
        $mockService->shouldReceive('fetchNotionPages')
            ->once()
            ->with(null)
            ->andThrow(new \Exception('Test error'));

        $this->app->instance(NotionService::class, $mockService);

        // Make the API request without page_id parameter
        $response = $this->getJson('/api/notion/fetch');

        // Assert the response
        $response->assertStatus(500)
            ->assertJson(['error' => 'Test error']);
    }

    public function test_api_handles_errors_with_explicit_page_id()
    {
        // Mock the NotionService to throw an exception
        $mockService = Mockery::mock(NotionService::class);
        $mockService->shouldReceive('fetchNotionPages')
            ->once()
            ->with('test-page-id')
            ->andThrow(new \Exception('Test error with page_id'));

        $this->app->instance(NotionService::class, $mockService);

        // Make the API request with page_id parameter
        $response = $this->getJson('/api/notion/fetch?page_id=test-page-id');

        // Assert the response
        $response->assertStatus(500)
            ->assertJson(['error' => 'Test error with page_id']);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
}
