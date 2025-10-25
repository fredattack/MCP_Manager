<?php

declare(strict_types=1);

namespace Tests\Feature\Git;

use App\Enums\GitConnectionStatus;
use App\Enums\GitProvider;
use App\Models\GitConnection;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use PHPUnit\Framework\Attributes\Group;
use Tests\TestCase;

#[Group('git')]
#[Group('feature')]
#[Group('integration')]

/**
 * @group git
 * @group oauth
 * @group feature
 */
class GitOAuthFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_oauth_start_generates_auth_url_and_caches_state(): void
    {
        $user = User::factory()->create();

        config([
            'services.github.client_id' => 'test-client-id',
        ]);

        $response = $this->actingAs($user)
            ->getJson('/api/git/oauth/github/start');

        $response->assertOk()
            ->assertJsonStructure([
                'auth_url',
                'state',
            ]);

        $state = $response->json('state');

        // Verify state and verifier are cached
        $this->assertNotNull(Cache::get("git_oauth_state_{$state}"));
        $this->assertNotNull(Cache::get("git_oauth_verifier_{$state}"));

        // Verify URL contains required parameters
        $authUrl = $response->json('auth_url');
        $this->assertStringContainsString('github.com', $authUrl);
        $this->assertStringContainsString('client_id=test-client-id', $authUrl);
        $this->assertStringContainsString('state='.$state, $authUrl);
        $this->assertStringContainsString('code_challenge=', $authUrl);
    }

    public function test_oauth_callback_exchanges_code_and_creates_connection(): void
    {
        $user = User::factory()->create();

        config([
            'services.github.client_id' => 'test-client-id',
            'services.github.client_secret' => 'test-client-secret',
        ]);

        $state = 'test-state-'.uniqid();
        $verifier = 'test-verifier-'.uniqid();

        // Cache state and verifier
        Cache::put("git_oauth_state_{$state}", $user->id, 600);
        Cache::put("git_oauth_verifier_{$state}", $verifier, 600);

        // Mock token exchange
        Http::fake([
            'https://github.com/login/oauth/access_token' => Http::response([
                'access_token' => 'gho_test_token',
                'refresh_token' => 'refresh_token',
                'expires_in' => 3600,
                'scope' => 'repo user',
            ], 200),
            'https://api.github.com/user' => Http::response([
                'id' => 123456,
                'login' => 'testuser',
                'name' => 'Test User',
                'email' => 'test@example.com',
            ], 200),
        ]);

        $response = $this->actingAs($user)
            ->getJson('/api/git/oauth/github/callback?code=test-code&state='.$state);

        $response->assertOk()
            ->assertJson([
                'success' => true,
                'provider' => 'github',
            ]);

        // Verify connection was created
        $this->assertDatabaseHas('git_connections', [
            'user_id' => $user->id,
            'provider' => GitProvider::GITHUB->value,
            'external_user_id' => '123456',
            'status' => GitConnectionStatus::ACTIVE->value,
        ]);

        // Verify state and verifier were cleaned up
        $this->assertNull(Cache::get("git_oauth_state_{$state}"));
        $this->assertNull(Cache::get("git_oauth_verifier_{$state}"));
    }

    public function test_oauth_callback_updates_existing_connection(): void
    {
        $user = User::factory()->create();

        // Create existing connection
        $existingConnection = GitConnection::factory()->create([
            'user_id' => $user->id,
            'provider' => GitProvider::GITHUB,
            'external_user_id' => '123456',
            'status' => GitConnectionStatus::ERROR,
        ]);

        config([
            'services.github.client_id' => 'test-client-id',
            'services.github.client_secret' => 'test-client-secret',
        ]);

        $state = 'test-state-'.uniqid();
        $verifier = 'test-verifier-'.uniqid();

        Cache::put("git_oauth_state_{$state}", $user->id, 600);
        Cache::put("git_oauth_verifier_{$state}", $verifier, 600);

        Http::fake([
            'https://github.com/login/oauth/access_token' => Http::response([
                'access_token' => 'new_access_token',
                'refresh_token' => 'new_refresh_token',
                'expires_in' => 3600,
                'scope' => 'repo user',
            ], 200),
            'https://api.github.com/user' => Http::response([
                'id' => 123456,
                'login' => 'testuser',
            ], 200),
        ]);

        $response = $this->actingAs($user)
            ->getJson('/api/git/oauth/github/callback?code=test-code&state='.$state);

        $response->assertOk();

        // Verify connection was updated, not duplicated
        $this->assertEquals(1, GitConnection::where('user_id', $user->id)->count());

        $existingConnection->refresh();
        $this->assertEquals(GitConnectionStatus::ACTIVE, $existingConnection->status);
        $this->assertEquals('new_access_token', $existingConnection->getAccessToken());
    }

    public function test_oauth_callback_fails_with_invalid_state(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->getJson('/api/git/oauth/github/callback?code=test-code&state=invalid-state');

        $response->assertStatus(400)
            ->assertJson([
                'error' => 'Invalid state parameter',
            ]);
    }

    public function test_oauth_callback_fails_without_code(): void
    {
        $user = User::factory()->create();

        $state = 'test-state-'.uniqid();
        Cache::put("git_oauth_state_{$state}", $user->id, 600);

        $response = $this->actingAs($user)
            ->getJson('/api/git/oauth/github/callback?state='.$state);

        $response->assertStatus(400)
            ->assertJson([
                'error' => 'Missing authorization code',
            ]);
    }

    public function test_oauth_callback_handles_token_exchange_failure(): void
    {
        $user = User::factory()->create();

        config([
            'services.github.client_id' => 'test-client-id',
            'services.github.client_secret' => 'test-client-secret',
        ]);

        $state = 'test-state-'.uniqid();
        $verifier = 'test-verifier-'.uniqid();

        Cache::put("git_oauth_state_{$state}", $user->id, 600);
        Cache::put("git_oauth_verifier_{$state}", $verifier, 600);

        Http::fake([
            'https://github.com/login/oauth/access_token' => Http::response([
                'error' => 'invalid_grant',
                'error_description' => 'The code is invalid',
            ], 400),
        ]);

        $response = $this->actingAs($user)
            ->getJson('/api/git/oauth/github/callback?code=invalid-code&state='.$state);

        $response->assertStatus(500);
    }

    public function test_oauth_callback_handles_user_fetch_failure(): void
    {
        $user = User::factory()->create();

        config([
            'services.github.client_id' => 'test-client-id',
            'services.github.client_secret' => 'test-client-secret',
        ]);

        $state = 'test-state-'.uniqid();
        $verifier = 'test-verifier-'.uniqid();

        Cache::put("git_oauth_state_{$state}", $user->id, 600);
        Cache::put("git_oauth_verifier_{$state}", $verifier, 600);

        Http::fake([
            'https://github.com/login/oauth/access_token' => Http::response([
                'access_token' => 'token',
            ], 200),
            'https://api.github.com/user' => Http::response([
                'message' => 'Bad credentials',
            ], 401),
        ]);

        $response = $this->actingAs($user)
            ->getJson('/api/git/oauth/github/callback?code=test-code&state='.$state);

        $response->assertStatus(500);
    }

    public function test_complete_oauth_flow_completes_within_time_limit(): void
    {
        $user = User::factory()->create();

        config([
            'services.github.client_id' => 'test-client-id',
            'services.github.client_secret' => 'test-client-secret',
        ]);

        $startTime = microtime(true);

        // Step 1: Start OAuth
        $startResponse = $this->actingAs($user)
            ->getJson('/api/git/oauth/github/start');

        $startResponse->assertOk();
        $state = $startResponse->json('state');

        // Mock API responses
        Http::fake([
            'https://github.com/login/oauth/access_token' => Http::response([
                'access_token' => 'token',
                'scope' => 'repo',
            ], 200),
            'https://api.github.com/user' => Http::response([
                'id' => 999,
                'login' => 'testuser',
            ], 200),
        ]);

        // Step 2: Complete OAuth callback
        $callbackResponse = $this->actingAs($user)
            ->getJson('/api/git/oauth/github/callback?code=test-code&state='.$state);

        $callbackResponse->assertOk();

        $endTime = microtime(true);
        $duration = ($endTime - $startTime) * 1000; // Convert to milliseconds

        // Verify entire flow completes in under 60 seconds (60000ms)
        $this->assertLessThan(60000, $duration);
    }

    public function test_oauth_requires_authentication(): void
    {
        $response = $this->getJson('/api/git/oauth/github/start');

        $response->assertUnauthorized();
    }

    public function test_oauth_start_for_gitlab(): void
    {
        $user = User::factory()->create();

        config([
            'services.gitlab.client_id' => 'gitlab-client-id',
        ]);

        $response = $this->actingAs($user)
            ->getJson('/api/git/oauth/gitlab/start');

        $response->assertOk();

        $authUrl = $response->json('auth_url');
        $this->assertStringContainsString('gitlab.com', $authUrl);
        $this->assertStringNotContainsString('code_challenge', $authUrl); // GitLab doesn't use PKCE in this implementation
    }
}
