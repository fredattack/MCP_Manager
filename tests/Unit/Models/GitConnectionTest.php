<?php

declare(strict_types=1);

namespace Tests\Unit\Models;

use App\Enums\GitConnectionStatus;
use App\Enums\GitProvider;
use App\Models\GitConnection;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Crypt;
use PHPUnit\Framework\Attributes\Group;
use Tests\TestCase;

#[Group('git')]
#[Group('unit')]
#[Group('models')]

/**
 * @group git
 * @group model
 * @group unit
 */
class GitConnectionTest extends TestCase
{
    use RefreshDatabase;

    public function test_connection_belongs_to_user(): void
    {
        $user = User::factory()->create();
        $connection = GitConnection::factory()->create([
            'user_id' => $user->id,
        ]);

        $this->assertInstanceOf(User::class, $connection->user);
        $this->assertEquals($user->id, $connection->user->id);
    }

    public function test_connection_casts_provider_to_enum(): void
    {
        $connection = GitConnection::factory()->create([
            'provider' => GitProvider::GITHUB,
        ]);

        $this->assertInstanceOf(GitProvider::class, $connection->provider);
        $this->assertEquals(GitProvider::GITHUB, $connection->provider);
    }

    public function test_connection_casts_status_to_enum(): void
    {
        $connection = GitConnection::factory()->create([
            'status' => GitConnectionStatus::ACTIVE,
        ]);

        $this->assertInstanceOf(GitConnectionStatus::class, $connection->status);
        $this->assertEquals(GitConnectionStatus::ACTIVE, $connection->status);
    }

    public function test_connection_casts_scopes_to_array(): void
    {
        $connection = GitConnection::factory()->create([
            'scopes' => ['repo', 'user', 'read:org'],
        ]);

        $this->assertIsArray($connection->scopes);
        $this->assertEquals(['repo', 'user', 'read:org'], $connection->scopes);
    }

    public function test_connection_casts_expires_at_to_datetime(): void
    {
        $connection = GitConnection::factory()->create([
            'expires_at' => now()->addHour(),
        ]);

        $this->assertInstanceOf(\Illuminate\Support\Carbon::class, $connection->expires_at);
    }

    public function test_set_access_token_encrypts_token(): void
    {
        $connection = GitConnection::factory()->create();

        $plainToken = 'my-secret-token';
        $connection->setAccessToken($plainToken);
        $connection->save();

        // Verify token is encrypted in database
        $this->assertNotEquals($plainToken, $connection->access_token_enc);

        // Verify we can decrypt it
        $this->assertEquals($plainToken, Crypt::decryptString($connection->access_token_enc));
    }

    public function test_get_access_token_decrypts_token(): void
    {
        $connection = GitConnection::factory()->create();

        $plainToken = 'test-access-token-123';
        $connection->setAccessToken($plainToken);
        $connection->save();

        $decryptedToken = $connection->getAccessToken();

        $this->assertEquals($plainToken, $decryptedToken);
    }

    public function test_set_refresh_token_encrypts_token(): void
    {
        $connection = GitConnection::factory()->create();

        $plainToken = 'my-refresh-token';
        $connection->setRefreshToken($plainToken);
        $connection->save();

        // Verify token is encrypted
        $this->assertNotEquals($plainToken, $connection->refresh_token_enc);
        $this->assertEquals($plainToken, Crypt::decryptString($connection->refresh_token_enc));
    }

    public function test_get_refresh_token_decrypts_token(): void
    {
        $connection = GitConnection::factory()->create();

        $plainToken = 'test-refresh-token-456';
        $connection->setRefreshToken($plainToken);
        $connection->save();

        $decryptedToken = $connection->getRefreshToken();

        $this->assertEquals($plainToken, $decryptedToken);
    }

    public function test_set_refresh_token_handles_null(): void
    {
        $connection = GitConnection::factory()->create();

        $connection->setRefreshToken(null);
        $connection->save();

        $this->assertNull($connection->refresh_token_enc);
        $this->assertNull($connection->getRefreshToken());
    }

    public function test_is_token_expired_returns_false_if_no_expiry(): void
    {
        $connection = GitConnection::factory()->create([
            'expires_at' => null,
        ]);

        $this->assertFalse($connection->isTokenExpired());
    }

    public function test_is_token_expired_returns_true_if_expired(): void
    {
        $connection = GitConnection::factory()->create([
            'expires_at' => now()->subHour(),
        ]);

        $this->assertTrue($connection->isTokenExpired());
    }

    public function test_is_token_expired_returns_true_if_expires_soon(): void
    {
        // Expires in 5 minutes (less than 10 minute buffer)
        $connection = GitConnection::factory()->create([
            'expires_at' => now()->addMinutes(5),
        ]);

        $this->assertTrue($connection->isTokenExpired());
    }

    public function test_is_token_expired_returns_false_if_expires_later(): void
    {
        // Expires in 30 minutes (more than 10 minute buffer)
        $connection = GitConnection::factory()->create([
            'expires_at' => now()->addMinutes(30),
        ]);

        $this->assertFalse($connection->isTokenExpired());
    }

    public function test_scope_active_filters_active_connections(): void
    {
        $user = User::factory()->create();

        GitConnection::factory()->create([
            'user_id' => $user->id,
            'status' => GitConnectionStatus::ACTIVE,
        ]);

        GitConnection::factory()->create([
            'user_id' => $user->id,
            'status' => GitConnectionStatus::ERROR,
        ]);

        $activeConnections = GitConnection::active()->get();

        $this->assertCount(1, $activeConnections);
        $this->assertEquals(GitConnectionStatus::ACTIVE, $activeConnections->first()->status);
    }

    public function test_scope_for_provider_filters_by_provider(): void
    {
        $user = User::factory()->create();

        GitConnection::factory()->create([
            'user_id' => $user->id,
            'provider' => GitProvider::GITHUB,
        ]);

        GitConnection::factory()->create([
            'user_id' => $user->id,
            'provider' => GitProvider::GITLAB,
        ]);

        $githubConnections = GitConnection::forProvider(GitProvider::GITHUB)->get();

        $this->assertCount(1, $githubConnections);
        $this->assertEquals(GitProvider::GITHUB, $githubConnections->first()->provider);
    }

    public function test_repositories_relationship(): void
    {
        $user = User::factory()->create();
        $connection = GitConnection::factory()->create([
            'user_id' => $user->id,
            'provider' => GitProvider::GITHUB,
        ]);

        // This test just verifies the relationship is defined
        $repositories = $connection->repositories;

        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Collection::class, $repositories);
    }

    public function test_connection_can_be_created_with_factory(): void
    {
        $connection = GitConnection::factory()->create();

        $this->assertDatabaseHas('git_connections', [
            'id' => $connection->id,
        ]);
    }
}
