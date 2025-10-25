<?php

declare(strict_types=1);

namespace Tests\Unit\Services\Git;

use App\Enums\GitConnectionStatus;
use App\Enums\GitProvider;
use App\Models\GitConnection;
use App\Models\User;
use App\Services\Git\GitOAuthService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Request;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use PHPUnit\Framework\Attributes\Group;
use Tests\TestCase;

#[Group('git')]
#[Group('unit')]
#[Group('services')]
class GitOAuthServiceTest extends TestCase
{
    use RefreshDatabase;

    private GitOAuthService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new GitOAuthService;
    }

    public function test_generate_auth_url_for_github_with_pkce(): void
    {
        config([
            'services.github.client_id' => 'test-client-id',
        ]);

        $result = $this->service->generateAuthUrl(
            GitProvider::GITHUB,
            'http://localhost/callback'
        );

        $this->assertArrayHasKey('auth_url', $result);
        $this->assertArrayHasKey('state', $result);
        $this->assertArrayHasKey('code_verifier', $result);

        // Verify state is 40 characters
        $this->assertEquals(40, strlen($result['state']));

        // Verify code_verifier is 128 characters
        $this->assertEquals(128, strlen($result['code_verifier']));

        // Verify URL contains required parameters
        $this->assertStringContainsString('client_id=test-client-id', $result['auth_url']);
        $this->assertStringContainsString('state='.$result['state'], $result['auth_url']);
        $this->assertStringContainsString('code_challenge=', $result['auth_url']);
        $this->assertStringContainsString('code_challenge_method=S256', $result['auth_url']);
        $this->assertStringContainsString('scope=', $result['auth_url']);
    }

    public function test_generate_auth_url_for_gitlab_without_pkce(): void
    {
        config([
            'services.gitlab.client_id' => 'test-gitlab-client-id',
        ]);

        $result = $this->service->generateAuthUrl(
            GitProvider::GITLAB,
            'http://localhost/callback'
        );

        $this->assertArrayHasKey('auth_url', $result);
        $this->assertArrayHasKey('state', $result);
        $this->assertArrayHasKey('code_verifier', $result);

        // GitLab URL should not contain PKCE parameters
        $this->assertStringNotContainsString('code_challenge=', $result['auth_url']);
        $this->assertStringNotContainsString('code_challenge_method=', $result['auth_url']);
    }

    public function test_exchange_code_successfully_for_github(): void
    {
        config([
            'services.github.client_id' => 'test-client-id',
            'services.github.client_secret' => 'test-client-secret',
        ]);

        Http::fake([
            'https://github.com/login/oauth/access_token' => Http::response([
                'access_token' => 'gho_test_token_123',
                'refresh_token' => 'refresh_token_123',
                'expires_in' => 3600,
                'scope' => 'repo user',
            ], 200),
        ]);

        $result = $this->service->exchangeCode(
            GitProvider::GITHUB,
            'test-code',
            'test-verifier',
            'http://localhost/callback'
        );

        $this->assertEquals('gho_test_token_123', $result['access_token']);
        $this->assertEquals('refresh_token_123', $result['refresh_token']);
        $this->assertEquals(3600, $result['expires_in']);
        $this->assertEquals('repo user', $result['scope']);

        // Verify request was made with correct parameters
        Http::assertSent(function (Request $request) {
            return $request->url() === 'https://github.com/login/oauth/access_token' &&
                   $request['code'] === 'test-code' &&
                   $request['code_verifier'] === 'test-verifier' &&
                   $request['client_id'] === 'test-client-id' &&
                   $request['client_secret'] === 'test-client-secret';
        });
    }

    public function test_exchange_code_successfully_for_gitlab(): void
    {
        config([
            'services.gitlab.client_id' => 'gitlab-client-id',
            'services.gitlab.client_secret' => 'gitlab-client-secret',
        ]);

        Http::fake([
            'https://gitlab.com/oauth/token' => Http::response([
                'access_token' => 'gitlab_token_123',
                'refresh_token' => 'gitlab_refresh_123',
                'expires_in' => 7200,
                'scope' => 'api read_user',
            ], 200),
        ]);

        $result = $this->service->exchangeCode(
            GitProvider::GITLAB,
            'gitlab-code',
            'verifier',
            'http://localhost/callback'
        );

        $this->assertEquals('gitlab_token_123', $result['access_token']);
        $this->assertEquals('gitlab_refresh_123', $result['refresh_token']);

        // Verify GitLab uses grant_type instead of code_verifier
        Http::assertSent(function (Request $request) {
            return $request['grant_type'] === 'authorization_code' &&
                   ! isset($request['code_verifier']);
        });
    }

    public function test_exchange_code_handles_missing_refresh_token(): void
    {
        config([
            'services.github.client_id' => 'test-client-id',
            'services.github.client_secret' => 'test-client-secret',
        ]);

        Http::fake([
            'https://github.com/login/oauth/access_token' => Http::response([
                'access_token' => 'token_without_refresh',
                'scope' => 'repo',
            ], 200),
        ]);

        $result = $this->service->exchangeCode(
            GitProvider::GITHUB,
            'test-code',
            'test-verifier',
            'http://localhost/callback'
        );

        $this->assertEquals('token_without_refresh', $result['access_token']);
        $this->assertNull($result['refresh_token']);
        $this->assertNull($result['expires_in']);
    }

    public function test_exchange_code_throws_exception_on_failure(): void
    {
        config([
            'services.github.client_id' => 'test-client-id',
            'services.github.client_secret' => 'test-client-secret',
        ]);

        Log::shouldReceive('error')->once();

        Http::fake([
            'https://github.com/login/oauth/access_token' => Http::response([
                'error' => 'invalid_grant',
                'error_description' => 'The code is invalid',
            ], 400),
        ]);

        $this->expectException(RequestException::class);

        $this->service->exchangeCode(
            GitProvider::GITHUB,
            'invalid-code',
            'test-verifier',
            'http://localhost/callback'
        );
    }

    public function test_refresh_token_successfully(): void
    {
        config([
            'services.github.client_id' => 'test-client-id',
            'services.github.client_secret' => 'test-client-secret',
        ]);

        $user = User::factory()->create();
        $connection = GitConnection::factory()->create([
            'user_id' => $user->id,
            'provider' => GitProvider::GITHUB,
            'status' => GitConnectionStatus::ACTIVE,
        ]);

        $oldRefreshToken = $connection->getRefreshToken();

        Http::fake([
            'https://github.com/login/oauth/access_token' => Http::response([
                'access_token' => 'new_access_token',
                'refresh_token' => 'new_refresh_token',
                'expires_in' => 3600,
            ], 200),
        ]);

        $refreshedConnection = $this->service->refreshToken($connection);

        $this->assertEquals('new_access_token', $refreshedConnection->getAccessToken());
        $this->assertEquals('new_refresh_token', $refreshedConnection->getRefreshToken());
        $this->assertEquals(GitConnectionStatus::ACTIVE, $refreshedConnection->status);
        $this->assertNotNull($refreshedConnection->expires_at);

        // Verify request used the old refresh token
        Http::assertSent(function (Request $request) use ($oldRefreshToken) {
            return $request['grant_type'] === 'refresh_token' &&
                   $request['refresh_token'] === $oldRefreshToken;
        });
    }

    public function test_refresh_token_updates_only_access_token_if_no_new_refresh_token(): void
    {
        config([
            'services.github.client_id' => 'test-client-id',
            'services.github.client_secret' => 'test-client-secret',
        ]);

        $user = User::factory()->create();
        $connection = GitConnection::factory()->create([
            'user_id' => $user->id,
            'provider' => GitProvider::GITHUB,
        ]);

        $oldRefreshToken = $connection->getRefreshToken();

        Http::fake([
            'https://github.com/login/oauth/access_token' => Http::response([
                'access_token' => 'new_access_token_only',
            ], 200),
        ]);

        $refreshedConnection = $this->service->refreshToken($connection);

        $this->assertEquals('new_access_token_only', $refreshedConnection->getAccessToken());
        $this->assertEquals($oldRefreshToken, $refreshedConnection->getRefreshToken());
    }

    public function test_refresh_token_throws_exception_if_no_refresh_token(): void
    {
        $user = User::factory()->create();
        $connection = GitConnection::factory()->create([
            'user_id' => $user->id,
            'provider' => GitProvider::GITHUB,
            'refresh_token_enc' => null,
        ]);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('No refresh token available');

        $this->service->refreshToken($connection);
    }

    public function test_refresh_token_marks_connection_as_error_on_failure(): void
    {
        config([
            'services.github.client_id' => 'test-client-id',
            'services.github.client_secret' => 'test-client-secret',
        ]);

        Log::shouldReceive('error')->once();

        $user = User::factory()->create();
        $connection = GitConnection::factory()->create([
            'user_id' => $user->id,
            'provider' => GitProvider::GITHUB,
            'status' => GitConnectionStatus::ACTIVE,
        ]);

        Http::fake([
            'https://github.com/login/oauth/access_token' => Http::response([
                'error' => 'invalid_grant',
            ], 400),
        ]);

        $this->expectException(RequestException::class);

        try {
            $this->service->refreshToken($connection);
        } catch (RequestException $e) {
            $connection->refresh();
            $this->assertEquals(GitConnectionStatus::ERROR, $connection->status);
            throw $e;
        }
    }

    public function test_create_or_update_connection_creates_new_connection(): void
    {
        $user = User::factory()->create();

        $tokenData = [
            'access_token' => 'test_access_token',
            'refresh_token' => 'test_refresh_token',
            'expires_in' => 3600,
            'scope' => 'repo user',
        ];

        Log::shouldReceive('info')->once();

        $connection = $this->service->createOrUpdateConnection(
            $user,
            GitProvider::GITHUB,
            $tokenData,
            'external_user_123'
        );

        $this->assertInstanceOf(GitConnection::class, $connection);
        $this->assertEquals($user->id, $connection->user_id);
        $this->assertEquals(GitProvider::GITHUB, $connection->provider);
        $this->assertEquals('external_user_123', $connection->external_user_id);
        $this->assertEquals('test_access_token', $connection->getAccessToken());
        $this->assertEquals('test_refresh_token', $connection->getRefreshToken());
        $this->assertEquals(['repo', 'user'], $connection->scopes);
        $this->assertEquals(GitConnectionStatus::ACTIVE, $connection->status);
        $this->assertNotNull($connection->expires_at);
    }

    public function test_create_or_update_connection_updates_existing_connection(): void
    {
        $user = User::factory()->create();

        $existingConnection = GitConnection::factory()->create([
            'user_id' => $user->id,
            'provider' => GitProvider::GITHUB,
            'external_user_id' => 'external_user_123',
            'status' => GitConnectionStatus::ERROR,
        ]);

        $tokenData = [
            'access_token' => 'updated_access_token',
            'refresh_token' => 'updated_refresh_token',
            'expires_in' => 7200,
            'scope' => 'repo user read:org',
        ];

        Log::shouldReceive('info')->once();

        $connection = $this->service->createOrUpdateConnection(
            $user,
            GitProvider::GITHUB,
            $tokenData,
            'external_user_123'
        );

        $this->assertEquals($existingConnection->id, $connection->id);
        $this->assertEquals('updated_access_token', $connection->getAccessToken());
        $this->assertEquals('updated_refresh_token', $connection->getRefreshToken());
        $this->assertEquals(['repo', 'user', 'read:org'], $connection->scopes);
        $this->assertEquals(GitConnectionStatus::ACTIVE, $connection->status);
    }

    public function test_create_or_update_connection_handles_null_refresh_token(): void
    {
        $user = User::factory()->create();

        $tokenData = [
            'access_token' => 'test_access_token',
            'refresh_token' => null,
            'expires_in' => null,
            'scope' => 'repo',
        ];

        Log::shouldReceive('info')->once();

        $connection = $this->service->createOrUpdateConnection(
            $user,
            GitProvider::GITHUB,
            $tokenData,
            'external_user_456'
        );

        $this->assertEquals('test_access_token', $connection->getAccessToken());
        $this->assertNull($connection->getRefreshToken());
        $this->assertNull($connection->expires_at);
    }
}
