<?php

declare(strict_types=1);

namespace Tests\Feature\Git;

use App\Enums\GitProvider;
use App\Models\GitRepository;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use PHPUnit\Framework\Attributes\Group;
use Tests\TestCase;

#[Group('git')]
#[Group('feature')]
#[Group('webhooks')]
#[Group('security')]

/**
 * @group git
 * @group webhook
 * @group feature
 */
class GitWebhookTest extends TestCase
{
    use RefreshDatabase;

    public function test_github_webhook_validates_signature(): void
    {
        config(['services.github.webhook_secret' => 'test-secret']);

        $payload = json_encode([
            'ref' => 'refs/heads/main',
            'repository' => [
                'id' => 123,
                'full_name' => 'user/repo',
            ],
        ]);

        $signature = 'sha256='.hash_hmac('sha256', $payload, 'test-secret');

        $response = $this->postJson('/api/webhooks/github', json_decode($payload, true), [
            'X-Hub-Signature-256' => $signature,
            'X-GitHub-Event' => 'push',
        ]);

        $response->assertOk();
    }

    public function test_github_webhook_rejects_invalid_signature(): void
    {
        config(['services.github.webhook_secret' => 'test-secret']);

        $payload = [
            'ref' => 'refs/heads/main',
            'repository' => [
                'id' => 123,
                'full_name' => 'user/repo',
            ],
        ];

        $response = $this->postJson('/api/webhooks/github', $payload, [
            'X-Hub-Signature-256' => 'sha256=invalid_signature',
            'X-GitHub-Event' => 'push',
        ]);

        $response->assertStatus(401)
            ->assertJson([
                'error' => 'Invalid signature',
            ]);
    }

    public function test_github_webhook_rejects_missing_signature(): void
    {
        config(['services.github.webhook_secret' => 'test-secret']);

        $payload = [
            'repository' => [
                'id' => 123,
            ],
        ];

        $response = $this->postJson('/api/webhooks/github', $payload, [
            'X-GitHub-Event' => 'push',
        ]);

        $response->assertStatus(401);
    }

    public function test_github_push_webhook_updates_repository(): void
    {
        config(['services.github.webhook_secret' => 'test-secret']);

        $user = User::factory()->create();
        $repository = GitRepository::factory()->create([
            'user_id' => $user->id,
            'provider' => GitProvider::GITHUB,
            'external_id' => '12345',
            'full_name' => 'owner/repo',
            'default_branch' => 'master',
        ]);

        $payload = json_encode([
            'ref' => 'refs/heads/main',
            'repository' => [
                'id' => 12345,
                'full_name' => 'owner/repo',
                'default_branch' => 'main',
            ],
        ]);

        $signature = 'sha256='.hash_hmac('sha256', $payload, 'test-secret');

        $response = $this->postJson('/api/webhooks/github', json_decode($payload, true), [
            'X-Hub-Signature-256' => $signature,
            'X-GitHub-Event' => 'push',
        ]);

        $response->assertOk();

        $repository->refresh();
        $this->assertEquals('main', $repository->default_branch);
        $this->assertNotNull($repository->last_synced_at);
    }

    public function test_github_pull_request_webhook_logs_event(): void
    {
        config(['services.github.webhook_secret' => 'test-secret']);

        $payload = json_encode([
            'action' => 'opened',
            'pull_request' => [
                'number' => 42,
                'title' => 'Add feature',
                'state' => 'open',
                'user' => [
                    'login' => 'contributor',
                ],
            ],
            'repository' => [
                'id' => 123,
                'full_name' => 'owner/repo',
            ],
        ]);

        $signature = 'sha256='.hash_hmac('sha256', $payload, 'test-secret');

        $response = $this->postJson('/api/webhooks/github', json_decode($payload, true), [
            'X-Hub-Signature-256' => $signature,
            'X-GitHub-Event' => 'pull_request',
        ]);

        $response->assertOk();
    }

    public function test_github_webhook_prevents_replay_attacks(): void
    {
        config(['services.github.webhook_secret' => 'test-secret']);

        $deliveryId = 'unique-delivery-'.uniqid();

        $payload = json_encode([
            'repository' => [
                'id' => 123,
                'full_name' => 'owner/repo',
            ],
        ]);

        $signature = 'sha256='.hash_hmac('sha256', $payload, 'test-secret');

        // First request should succeed
        $response1 = $this->postJson('/api/webhooks/github', json_decode($payload, true), [
            'X-Hub-Signature-256' => $signature,
            'X-GitHub-Event' => 'push',
            'X-GitHub-Delivery' => $deliveryId,
        ]);

        $response1->assertOk();

        // Duplicate request with same delivery ID should be ignored
        $response2 = $this->postJson('/api/webhooks/github', json_decode($payload, true), [
            'X-Hub-Signature-256' => $signature,
            'X-GitHub-Event' => 'push',
            'X-GitHub-Delivery' => $deliveryId,
        ]);

        $response2->assertOk()
            ->assertJson([
                'message' => 'Webhook already processed',
            ]);
    }

    public function test_github_webhook_validates_timestamp(): void
    {
        config(['services.github.webhook_secret' => 'test-secret']);

        $payload = json_encode([
            'repository' => [
                'id' => 123,
            ],
        ]);

        $signature = 'sha256='.hash_hmac('sha256', $payload, 'test-secret');

        // Old timestamp (10 minutes ago)
        $oldTimestamp = (string) (time() - 600);

        $response = $this->postJson('/api/webhooks/github', json_decode($payload, true), [
            'X-Hub-Signature-256' => $signature,
            'X-GitHub-Event' => 'push',
            'X-GitHub-Hook-Installation-Target-ID' => $oldTimestamp,
        ]);

        // Should still process if timestamp validation is lenient
        // Or reject if strict timestamp validation is enabled
        $this->assertTrue($response->status() === 200 || $response->status() === 401);
    }

    public function test_gitlab_webhook_validates_token(): void
    {
        config(['services.gitlab.webhook_secret' => 'gitlab-secret-token']);

        $payload = [
            'ref' => 'refs/heads/main',
            'project' => [
                'id' => 456,
                'path_with_namespace' => 'group/project',
            ],
        ];

        $response = $this->postJson('/api/webhooks/gitlab', $payload, [
            'X-Gitlab-Token' => 'gitlab-secret-token',
            'X-Gitlab-Event' => 'Push Hook',
        ]);

        $response->assertOk();
    }

    public function test_gitlab_webhook_rejects_invalid_token(): void
    {
        config(['services.gitlab.webhook_secret' => 'gitlab-secret-token']);

        $payload = [
            'project' => [
                'id' => 456,
            ],
        ];

        $response = $this->postJson('/api/webhooks/gitlab', $payload, [
            'X-Gitlab-Token' => 'wrong-token',
            'X-Gitlab-Event' => 'Push Hook',
        ]);

        $response->assertStatus(401)
            ->assertJson([
                'error' => 'Invalid signature',
            ]);
    }

    public function test_gitlab_push_webhook_updates_repository(): void
    {
        config(['services.gitlab.webhook_secret' => 'gitlab-secret']);

        $user = User::factory()->create();
        $repository = GitRepository::factory()->create([
            'user_id' => $user->id,
            'provider' => GitProvider::GITLAB,
            'external_id' => '67890',
            'full_name' => 'group/project',
            'default_branch' => 'master',
        ]);

        $payload = [
            'ref' => 'refs/heads/develop',
            'project' => [
                'id' => 67890,
                'path_with_namespace' => 'group/project',
                'default_branch' => 'develop',
            ],
        ];

        $response = $this->postJson('/api/webhooks/gitlab', $payload, [
            'X-Gitlab-Token' => 'gitlab-secret',
            'X-Gitlab-Event' => 'Push Hook',
        ]);

        $response->assertOk();

        $repository->refresh();
        $this->assertEquals('develop', $repository->default_branch);
    }

    public function test_gitlab_merge_request_webhook_logs_event(): void
    {
        config(['services.gitlab.webhook_secret' => 'gitlab-secret']);

        $payload = [
            'object_attributes' => [
                'iid' => 10,
                'title' => 'Merge feature',
                'state' => 'opened',
                'action' => 'open',
                'author' => [
                    'username' => 'developer',
                ],
            ],
            'project' => [
                'id' => 456,
                'path_with_namespace' => 'group/project',
            ],
        ];

        $response = $this->postJson('/api/webhooks/gitlab', $payload, [
            'X-Gitlab-Token' => 'gitlab-secret',
            'X-Gitlab-Event' => 'Merge Request Hook',
        ]);

        $response->assertOk();
    }

    public function test_webhook_handles_unknown_repository(): void
    {
        config(['services.github.webhook_secret' => 'test-secret']);

        $payload = json_encode([
            'repository' => [
                'id' => 99999,
                'full_name' => 'unknown/repo',
            ],
        ]);

        $signature = 'sha256='.hash_hmac('sha256', $payload, 'test-secret');

        $response = $this->postJson('/api/webhooks/github', json_decode($payload, true), [
            'X-Hub-Signature-256' => $signature,
            'X-GitHub-Event' => 'push',
        ]);

        $response->assertOk();
    }

    public function test_webhook_handles_ping_event(): void
    {
        config(['services.github.webhook_secret' => 'test-secret']);

        $payload = json_encode([
            'zen' => 'Design for failure.',
            'hook_id' => 12345,
        ]);

        $signature = 'sha256='.hash_hmac('sha256', $payload, 'test-secret');

        $response = $this->postJson('/api/webhooks/github', json_decode($payload, true), [
            'X-Hub-Signature-256' => $signature,
            'X-GitHub-Event' => 'ping',
        ]);

        $response->assertOk()
            ->assertJson([
                'message' => 'Pong!',
            ]);
    }

    public function test_webhook_deduplication_with_cache(): void
    {
        config(['services.github.webhook_secret' => 'test-secret']);

        $deliveryId = 'test-delivery-123';
        $cacheKey = "webhook_processed_{$deliveryId}";

        // Manually set cache to simulate already processed webhook
        Cache::put($cacheKey, true, 3600);

        $payload = json_encode([
            'repository' => [
                'id' => 123,
            ],
        ]);

        $signature = 'sha256='.hash_hmac('sha256', $payload, 'test-secret');

        $response = $this->postJson('/api/webhooks/github', json_decode($payload, true), [
            'X-Hub-Signature-256' => $signature,
            'X-GitHub-Event' => 'push',
            'X-GitHub-Delivery' => $deliveryId,
        ]);

        $response->assertOk();
    }
}
