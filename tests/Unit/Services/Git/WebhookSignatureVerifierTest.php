<?php

declare(strict_types=1);

namespace Tests\Unit\Services\Git;

use App\Enums\GitProvider;
use App\Services\Git\WebhookSignatureVerifier;
use PHPUnit\Framework\Attributes\Group;
use Tests\TestCase;

#[Group('git')]
#[Group('unit')]
#[Group('services')]
#[Group('security')]

/**
 * @group git
 * @group webhook
 * @group unit
 */
class WebhookSignatureVerifierTest extends TestCase
{
    private WebhookSignatureVerifier $verifier;

    protected function setUp(): void
    {
        parent::setUp();
        $this->verifier = new WebhookSignatureVerifier;
    }

    public function test_verify_github_with_valid_signature(): void
    {
        config(['services.github.webhook_secret' => 'test-secret-key']);

        $payload = '{"test": "data"}';
        $expectedHash = hash_hmac('sha256', $payload, 'test-secret-key');
        $signature = 'sha256='.$expectedHash;

        $result = $this->verifier->verifyGitHub($payload, $signature);

        $this->assertTrue($result);
    }

    public function test_verify_github_with_invalid_signature(): void
    {
        config(['services.github.webhook_secret' => 'test-secret-key']);

        $payload = '{"test": "data"}';
        $signature = 'sha256=invalid_hash';

        $result = $this->verifier->verifyGitHub($payload, $signature);

        $this->assertFalse($result);
    }

    public function test_verify_github_without_sha256_prefix(): void
    {
        config(['services.github.webhook_secret' => 'test-secret-key']);

        $payload = '{"test": "data"}';
        $signature = 'just_a_hash_without_prefix';

        $result = $this->verifier->verifyGitHub($payload, $signature);

        $this->assertFalse($result);
    }

    public function test_verify_github_throws_exception_if_secret_not_configured(): void
    {
        config(['services.github.webhook_secret' => '']);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('GitHub webhook secret not configured');

        $this->verifier->verifyGitHub('payload', 'sha256=hash');
    }

    public function test_verify_github_uses_timing_safe_comparison(): void
    {
        config(['services.github.webhook_secret' => 'secret']);

        $payload = '{"test": "data"}';
        $correctHash = hash_hmac('sha256', $payload, 'secret');

        // Create a signature that differs by one character
        $almostCorrectHash = substr($correctHash, 0, -1).'X';

        $result = $this->verifier->verifyGitHub($payload, 'sha256='.$almostCorrectHash);

        $this->assertFalse($result);
    }

    public function test_verify_gitlab_with_valid_token(): void
    {
        config(['services.gitlab.webhook_secret' => 'gitlab-secret-token']);

        $result = $this->verifier->verifyGitLab('gitlab-secret-token');

        $this->assertTrue($result);
    }

    public function test_verify_gitlab_with_invalid_token(): void
    {
        config(['services.gitlab.webhook_secret' => 'gitlab-secret-token']);

        $result = $this->verifier->verifyGitLab('wrong-token');

        $this->assertFalse($result);
    }

    public function test_verify_gitlab_throws_exception_if_secret_not_configured(): void
    {
        config(['services.gitlab.webhook_secret' => '']);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('GitLab webhook secret not configured');

        $this->verifier->verifyGitLab('any-token');
    }

    public function test_verify_gitlab_uses_timing_safe_comparison(): void
    {
        config(['services.gitlab.webhook_secret' => 'secret123']);

        // Token differs by one character
        $result = $this->verifier->verifyGitLab('secret12X');

        $this->assertFalse($result);
    }

    public function test_verify_dispatches_to_github_verifier(): void
    {
        config(['services.github.webhook_secret' => 'github-secret']);

        $payload = '{"event": "push"}';
        $hash = hash_hmac('sha256', $payload, 'github-secret');
        $signature = 'sha256='.$hash;

        $result = $this->verifier->verify(
            GitProvider::GITHUB,
            $payload,
            $signature,
            null
        );

        $this->assertTrue($result);
    }

    public function test_verify_dispatches_to_gitlab_verifier(): void
    {
        config(['services.gitlab.webhook_secret' => 'gitlab-secret']);

        $result = $this->verifier->verify(
            GitProvider::GITLAB,
            '{"event": "push"}',
            null,
            'gitlab-secret'
        );

        $this->assertTrue($result);
    }

    public function test_verify_returns_false_if_github_signature_missing(): void
    {
        config(['services.github.webhook_secret' => 'secret']);

        $result = $this->verifier->verify(
            GitProvider::GITHUB,
            '{"test": "data"}',
            null,
            null
        );

        $this->assertFalse($result);
    }

    public function test_verify_returns_false_if_gitlab_token_missing(): void
    {
        config(['services.gitlab.webhook_secret' => 'secret']);

        $result = $this->verifier->verify(
            GitProvider::GITLAB,
            '{"test": "data"}',
            null,
            null
        );

        $this->assertFalse($result);
    }

    public function test_is_recent_timestamp_returns_true_for_current_time(): void
    {
        $timestamp = (string) time();

        $result = $this->verifier->isRecentTimestamp($timestamp);

        $this->assertTrue($result);
    }

    public function test_is_recent_timestamp_returns_true_for_time_within_5_minutes(): void
    {
        $timestamp = (string) (time() - 290); // 4 minutes 50 seconds ago

        $result = $this->verifier->isRecentTimestamp($timestamp);

        $this->assertTrue($result);
    }

    public function test_is_recent_timestamp_returns_false_for_old_timestamp(): void
    {
        $timestamp = (string) (time() - 400); // 6 minutes 40 seconds ago

        $result = $this->verifier->isRecentTimestamp($timestamp);

        $this->assertFalse($result);
    }

    public function test_is_recent_timestamp_returns_false_for_future_timestamp(): void
    {
        $timestamp = (string) (time() + 400); // 6 minutes 40 seconds in future

        $result = $this->verifier->isRecentTimestamp($timestamp);

        $this->assertFalse($result);
    }

    public function test_is_recent_timestamp_returns_true_if_timestamp_is_null(): void
    {
        $result = $this->verifier->isRecentTimestamp(null);

        $this->assertTrue($result);
    }

    public function test_is_recent_timestamp_handles_exactly_5_minutes(): void
    {
        $timestamp = (string) (time() - 300); // Exactly 5 minutes

        $result = $this->verifier->isRecentTimestamp($timestamp);

        $this->assertTrue($result);
    }

    public function test_verify_github_handles_empty_payload(): void
    {
        config(['services.github.webhook_secret' => 'secret']);

        $payload = '';
        $hash = hash_hmac('sha256', $payload, 'secret');
        $signature = 'sha256='.$hash;

        $result = $this->verifier->verifyGitHub($payload, $signature);

        $this->assertTrue($result);
    }

    public function test_verify_github_handles_complex_json_payload(): void
    {
        config(['services.github.webhook_secret' => 'secret']);

        $payload = json_encode([
            'ref' => 'refs/heads/main',
            'commits' => [
                ['id' => 'abc123', 'message' => 'Test commit'],
                ['id' => 'def456', 'message' => 'Another commit'],
            ],
            'repository' => [
                'id' => 12345,
                'name' => 'test-repo',
            ],
        ]);

        $hash = hash_hmac('sha256', $payload, 'secret');
        $signature = 'sha256='.$hash;

        $result = $this->verifier->verifyGitHub($payload, $signature);

        $this->assertTrue($result);
    }
}
