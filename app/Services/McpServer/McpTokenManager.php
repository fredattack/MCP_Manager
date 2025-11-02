<?php

namespace App\Services\McpServer;

use App\Exceptions\McpAuthenticationException;
use App\Exceptions\McpServerException;
use App\Models\McpAccessToken;
use App\Models\McpSyncLog;
use App\Models\User;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class McpTokenManager
{
    public function __construct(
        private readonly McpServerClient $client
    ) {}

    public function getValidToken(User $user): string
    {
        $tokenRecord = McpAccessToken::where('user_id', $user->id)
            ->valid()
            ->first();

        if ($tokenRecord) {
            return $tokenRecord->access_token;
        }

        $tokenRecord = McpAccessToken::where('user_id', $user->id)->first();

        if ($tokenRecord && $tokenRecord->refresh_token) {
            try {
                return $this->refreshToken($user);
            } catch (McpServerException $e) {
                Log::channel('mcp')->warning('Token refresh failed, re-authenticating', [
                    'user_id' => $user->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        $tokens = $this->authenticate($user);

        return $tokens['access_token'];
    }

    public function authenticate(User $user, ?string $password = null): array
    {
        $startTime = microtime(true);

        try {
            $password = $password ?? $this->getUserMcpPassword($user);

            $response = Http::timeout(config('mcp-server.timeout', 30))
                ->asForm()
                ->post(config('mcp-server.base_url').'/token', [
                    'username' => $user->email,
                    'password' => $password,
                ]);

            if ($response->failed()) {
                throw new McpAuthenticationException(
                    'Authentication failed: '.$response->body(),
                    $response->status()
                );
            }

            $data = $response->json();

            if (isset($data['mfa_required']) && $data['mfa_required']) {
                $exception = new McpAuthenticationException('MFA required for this account');
                $exception->setMfaRequired(true);

                throw $exception;
            }

            $this->storeTokens($user, $data);

            $durationMs = (int) ((microtime(true) - $startTime) * 1000);

            $this->logTokenOperation($user, 'authenticate', 'success', null, $durationMs);

            Log::channel('mcp')->info('User authenticated successfully', [
                'user_id' => $user->id,
                'email' => $user->email,
            ]);

            return [
                'access_token' => $data['access_token'],
                'refresh_token' => $data['refresh_token'],
                'expires_in' => $data['expires_in'] ?? 1800,
            ];

        } catch (McpAuthenticationException $e) {
            $durationMs = (int) ((microtime(true) - $startTime) * 1000);
            $this->logTokenOperation($user, 'authenticate', 'failed', $e->getMessage(), $durationMs);

            throw $e;
        } catch (\Exception $e) {
            $durationMs = (int) ((microtime(true) - $startTime) * 1000);
            $this->logTokenOperation($user, 'authenticate', 'failed', $e->getMessage(), $durationMs);

            throw new McpAuthenticationException(
                'Authentication failed: '.$e->getMessage(),
                0,
                $e
            );
        }
    }

    public function refreshToken(User $user): string
    {
        $startTime = microtime(true);

        $tokenRecord = McpAccessToken::where('user_id', $user->id)->first();

        if (! $tokenRecord || ! $tokenRecord->refresh_token) {
            throw new McpServerException('No refresh token available');
        }

        try {
            $response = Http::timeout(config('mcp-server.timeout', 30))
                ->asForm()
                ->post(config('mcp-server.base_url').'/auth/refresh', [
                    'refresh_token' => $tokenRecord->refresh_token,
                ]);

            if ($response->failed()) {
                $tokenRecord->delete();

                throw new McpServerException(
                    'Token refresh failed: '.$response->body(),
                    $response->status()
                );
            }

            $data = $response->json();

            $tokenRecord->update([
                'access_token' => $data['access_token'],
                'expires_at' => now()->addSeconds($data['expires_in'] ?? 1800),
            ]);

            $durationMs = (int) ((microtime(true) - $startTime) * 1000);
            $this->logTokenOperation($user, 'refresh', 'success', null, $durationMs);

            Log::channel('mcp')->debug('Token refreshed successfully', [
                'user_id' => $user->id,
            ]);

            return $data['access_token'];

        } catch (\Exception $e) {
            $durationMs = (int) ((microtime(true) - $startTime) * 1000);
            $this->logTokenOperation($user, 'refresh', 'failed', $e->getMessage(), $durationMs);

            throw new McpServerException(
                'Token refresh failed: '.$e->getMessage(),
                0,
                $e
            );
        }
    }

    public function revokeTokens(User $user): void
    {
        McpAccessToken::where('user_id', $user->id)->delete();

        $this->logTokenOperation($user, 'revoke', 'success');

        Log::channel('mcp')->info('User tokens revoked', [
            'user_id' => $user->id,
        ]);
    }

    public function isTokenValid(User $user): bool
    {
        return McpAccessToken::where('user_id', $user->id)
            ->valid()
            ->exists();
    }

    public function getTokenExpiresAt(User $user): ?\Carbon\Carbon
    {
        $tokenRecord = McpAccessToken::where('user_id', $user->id)->first();

        return $tokenRecord?->expires_at;
    }

    private function storeTokens(User $user, array $data): void
    {
        McpAccessToken::updateOrCreate(
            ['user_id' => $user->id],
            [
                'access_token' => $data['access_token'],
                'refresh_token' => $data['refresh_token'] ?? null,
                'token_type' => $data['token_type'] ?? 'bearer',
                'expires_at' => now()->addSeconds($data['expires_in'] ?? 1800),
                'scope' => $data['scope'] ?? 'read write',
            ]
        );
    }

    private function getUserMcpPassword(User $user): string
    {
        $mcpServerUser = $user->mcpServerUser;

        if (! $mcpServerUser) {
            throw new McpServerException("User {$user->id} has not been synced to MCP Server");
        }

        return config('mcp-server.service_account.password');
    }

    private function logTokenOperation(
        User $user,
        string $operation,
        string $status,
        ?string $errorMessage = null,
        ?int $durationMs = null
    ): void {
        if (! config('mcp-server.logging.log_sync', true)) {
            return;
        }

        McpSyncLog::logSync(
            userId: $user->id,
            syncType: 'token_refresh',
            direction: 'laravel_to_mcp',
            status: $status,
            requestPayload: ['operation' => $operation],
            responsePayload: null,
            errorMessage: $errorMessage,
            durationMs: $durationMs
        );
    }

    public function getTokensExpiringSoon(?int $minutes = null): \Illuminate\Database\Eloquent\Collection
    {
        return McpAccessToken::expiringSoon($minutes)->get();
    }

    public function refreshExpiringSoonTokens(): array
    {
        $tokens = $this->getTokensExpiringSoon();

        $results = [
            'success' => 0,
            'failed' => 0,
            'errors' => [],
        ];

        foreach ($tokens as $tokenRecord) {
            try {
                $this->refreshToken($tokenRecord->user);
                $results['success']++;
            } catch (\Exception $e) {
                $results['failed']++;
                $results['errors'][] = [
                    'user_id' => $tokenRecord->user_id,
                    'email' => $tokenRecord->user->email,
                    'error' => $e->getMessage(),
                ];

                Log::channel('mcp')->error('Failed to refresh token', [
                    'user_id' => $tokenRecord->user_id,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        return $results;
    }
}
