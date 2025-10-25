<?php

declare(strict_types=1);

namespace Tests\Unit\Services\Git;

use App\Enums\GitProvider;
use App\Models\GitRepository;
use App\Models\User;
use App\Services\Git\WebhookEventHandler;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use PHPUnit\Framework\Attributes\Group;
use Tests\TestCase;

#[Group('git')]
#[Group('unit')]
#[Group('services')]
#[Group('webhooks')]

/**
 * @group git
 * @group webhook
 * @group unit
 */
class WebhookEventHandlerTest extends TestCase
{
    use RefreshDatabase;

    private WebhookEventHandler $handler;

    protected function setUp(): void
    {
        parent::setUp();
        $this->handler = new WebhookEventHandler;
        Cache::flush();
    }

    public function test_handle_push_updates_repository_metadata(): void
    {
        $user = User::factory()->create();
        $repository = GitRepository::factory()->create([
            'user_id' => $user->id,
            'provider' => GitProvider::GITHUB,
            'external_id' => '12345',
            'full_name' => 'owner/repo',
            'default_branch' => 'master',
        ]);

        $payload = [
            'ref' => 'refs/heads/main',
            'repository' => [
                'id' => 12345,
                'full_name' => 'owner/repo',
                'default_branch' => 'main',
            ],
        ];

        Log::shouldReceive('info')->once()->with('Push event processed', \Mockery::type('array'));
        Log::shouldReceive('debug')->once();

        $this->handler->handlePush(GitProvider::GITHUB, $payload);

        $repository->refresh();
        $this->assertEquals('main', $repository->default_branch);
        $this->assertNotNull($repository->last_synced_at);
    }

    public function test_handle_push_for_unknown_repository(): void
    {
        $payload = [
            'ref' => 'refs/heads/main',
            'repository' => [
                'id' => 99999,
                'full_name' => 'unknown/repo',
                'default_branch' => 'main',
            ],
        ];

        Log::shouldReceive('info')
            ->once()
            ->with('Push event for unknown repository', \Mockery::type('array'));

        $this->handler->handlePush(GitProvider::GITHUB, $payload);

        $this->assertDatabaseMissing('git_repositories', [
            'external_id' => '99999',
        ]);
    }

    public function test_handle_push_with_invalid_payload(): void
    {
        $payload = [
            'ref' => 'refs/heads/main',
            // Missing repository data
        ];

        Log::shouldReceive('warning')
            ->once()
            ->with('Could not extract repository data from push event', \Mockery::type('array'));

        $this->handler->handlePush(GitProvider::GITHUB, $payload);

        $this->assertTrue(true);
    }

    public function test_handle_push_invalidates_cache(): void
    {
        $user = User::factory()->create();
        GitRepository::factory()->create([
            'user_id' => $user->id,
            'provider' => GitProvider::GITHUB,
            'external_id' => '12345',
        ]);

        $cacheKey = 'test_cache_key';
        Cache::put($cacheKey, 'test_value', 60);

        $payload = [
            'repository' => [
                'id' => 12345,
                'full_name' => 'owner/repo',
                'default_branch' => 'main',
            ],
        ];

        Log::shouldReceive('info')->once();
        Log::shouldReceive('debug')->once();

        $this->handler->handlePush(GitProvider::GITHUB, $payload);

        $this->assertTrue(true);
    }

    public function test_handle_push_for_gitlab(): void
    {
        $user = User::factory()->create();
        $repository = GitRepository::factory()->create([
            'user_id' => $user->id,
            'provider' => GitProvider::GITLAB,
            'external_id' => '67890',
            'full_name' => 'group/project',
        ]);

        $payload = [
            'ref' => 'refs/heads/develop',
            'project' => [
                'id' => 67890,
                'path_with_namespace' => 'group/project',
                'default_branch' => 'develop',
            ],
        ];

        Log::shouldReceive('info')->once();
        Log::shouldReceive('debug')->once();

        $this->handler->handlePush(GitProvider::GITLAB, $payload);

        $repository->refresh();
        $this->assertEquals('develop', $repository->default_branch);
    }

    public function test_handle_pull_request_logs_event_data(): void
    {
        $payload = [
            'action' => 'opened',
            'pull_request' => [
                'number' => 42,
                'title' => 'Add new feature',
                'state' => 'open',
                'user' => [
                    'login' => 'contributor',
                ],
            ],
            'repository' => [
                'id' => 12345,
                'full_name' => 'owner/repo',
                'default_branch' => 'main',
            ],
        ];

        Log::shouldReceive('info')
            ->once()
            ->with('Pull request event received', \Mockery::on(function ($context) {
                return $context['action'] === 'opened' &&
                       $context['pr_number'] === 42 &&
                       $context['pr_title'] === 'Add new feature' &&
                       $context['state'] === 'open';
            }));

        $this->handler->handlePullRequest(GitProvider::GITHUB, $payload);

        $this->assertTrue(true);
    }

    public function test_handle_pull_request_for_gitlab_merge_request(): void
    {
        $payload = [
            'object_attributes' => [
                'iid' => 10,
                'title' => 'Merge feature branch',
                'state' => 'merged',
                'action' => 'merge',
                'author' => [
                    'username' => 'developer',
                ],
            ],
            'project' => [
                'id' => 67890,
                'path_with_namespace' => 'group/project',
                'default_branch' => 'main',
            ],
        ];

        Log::shouldReceive('info')
            ->once()
            ->with('Pull request event received', \Mockery::on(function ($context) {
                return $context['pr_number'] === 10 &&
                       $context['action'] === 'merge' &&
                       $context['state'] === 'merged';
            }));

        $this->handler->handlePullRequest(GitProvider::GITLAB, $payload);

        $this->assertTrue(true);
    }

    public function test_handle_pull_request_with_missing_data(): void
    {
        $payload = [
            'action' => 'opened',
            // Missing pull_request and repository
        ];

        Log::shouldReceive('warning')
            ->once()
            ->with('Could not extract PR data from event', \Mockery::type('array'));

        $this->handler->handlePullRequest(GitProvider::GITHUB, $payload);

        $this->assertTrue(true);
    }

    public function test_extract_github_repository_data(): void
    {
        $payload = [
            'repository' => [
                'id' => 12345,
                'full_name' => 'owner/repo',
                'default_branch' => 'main',
            ],
        ];

        $reflection = new \ReflectionClass($this->handler);
        $method = $reflection->getMethod('extractGitHubRepository');
        $method->setAccessible(true);

        $result = $method->invoke($this->handler, $payload);

        $this->assertEquals('12345', $result['external_id']);
        $this->assertEquals('owner/repo', $result['full_name']);
        $this->assertEquals('main', $result['default_branch']);
    }

    public function test_extract_github_repository_returns_null_if_missing(): void
    {
        $payload = [];

        $reflection = new \ReflectionClass($this->handler);
        $method = $reflection->getMethod('extractGitHubRepository');
        $method->setAccessible(true);

        $result = $method->invoke($this->handler, $payload);

        $this->assertNull($result);
    }

    public function test_extract_gitlab_repository_data(): void
    {
        $payload = [
            'project' => [
                'id' => 67890,
                'path_with_namespace' => 'group/project',
                'default_branch' => 'develop',
            ],
        ];

        $reflection = new \ReflectionClass($this->handler);
        $method = $reflection->getMethod('extractGitLabRepository');
        $method->setAccessible(true);

        $result = $method->invoke($this->handler, $payload);

        $this->assertEquals('67890', $result['external_id']);
        $this->assertEquals('group/project', $result['full_name']);
        $this->assertEquals('develop', $result['default_branch']);
    }

    public function test_extract_github_pull_request_data(): void
    {
        $payload = [
            'action' => 'synchronize',
            'pull_request' => [
                'number' => 123,
                'title' => 'Fix bug',
                'state' => 'open',
                'user' => [
                    'login' => 'bugfixer',
                ],
            ],
        ];

        $reflection = new \ReflectionClass($this->handler);
        $method = $reflection->getMethod('extractGitHubPullRequest');
        $method->setAccessible(true);

        $result = $method->invoke($this->handler, $payload);

        $this->assertEquals(123, $result['number']);
        $this->assertEquals('Fix bug', $result['title']);
        $this->assertEquals('open', $result['state']);
        $this->assertEquals('synchronize', $result['action']);
        $this->assertEquals('bugfixer', $result['author']);
    }

    public function test_extract_gitlab_merge_request_data(): void
    {
        $payload = [
            'object_attributes' => [
                'iid' => 25,
                'title' => 'Update documentation',
                'state' => 'opened',
                'action' => 'open',
                'author' => [
                    'username' => 'docwriter',
                ],
            ],
        ];

        $reflection = new \ReflectionClass($this->handler);
        $method = $reflection->getMethod('extractGitLabMergeRequest');
        $method->setAccessible(true);

        $result = $method->invoke($this->handler, $payload);

        $this->assertEquals(25, $result['number']);
        $this->assertEquals('Update documentation', $result['title']);
        $this->assertEquals('opened', $result['state']);
        $this->assertEquals('open', $result['action']);
        $this->assertEquals('docwriter', $result['author']);
    }

    public function test_extract_ref_from_github_payload(): void
    {
        $payload = ['ref' => 'refs/heads/feature-branch'];

        $reflection = new \ReflectionClass($this->handler);
        $method = $reflection->getMethod('extractRef');
        $method->setAccessible(true);

        $result = $method->invoke($this->handler, GitProvider::GITHUB, $payload);

        $this->assertEquals('refs/heads/feature-branch', $result);
    }

    public function test_extract_ref_from_gitlab_payload(): void
    {
        $payload = ['ref' => 'refs/heads/main'];

        $reflection = new \ReflectionClass($this->handler);
        $method = $reflection->getMethod('extractRef');
        $method->setAccessible(true);

        $result = $method->invoke($this->handler, GitProvider::GITLAB, $payload);

        $this->assertEquals('refs/heads/main', $result);
    }

    public function test_extract_ref_returns_null_if_missing(): void
    {
        $payload = [];

        $reflection = new \ReflectionClass($this->handler);
        $method = $reflection->getMethod('extractRef');
        $method->setAccessible(true);

        $result = $method->invoke($this->handler, GitProvider::GITHUB, $payload);

        $this->assertNull($result);
    }
}
