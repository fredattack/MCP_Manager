<?php

namespace App\Services\McpServer;

use App\Exceptions\CircuitBreakerOpenException;
use App\Exceptions\McpServerException;
use App\Exceptions\RateLimitException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class McpServerClient
{
    private string $baseUrl;

    private string $serviceAccountEmail;

    private string $serviceAccountPassword;

    private ?string $apiToken;

    private string $authMethod;

    private int $timeout;

    private ?string $serviceToken = null;

    public function __construct()
    {
        $this->baseUrl = config('mcp-server.base_url');
        $this->serviceAccountEmail = config('mcp-server.service_account.email');
        $this->serviceAccountPassword = config('mcp-server.service_account.password');
        $this->apiToken = config('mcp-server.service_account.api_token');
        $this->authMethod = config('mcp-server.service_account.auth_method', 'token');
        $this->timeout = config('mcp-server.timeout', 30);
    }

    public function get(string $endpoint, array $params = [], ?string $userToken = null): array
    {
        return $this->request('GET', $endpoint, ['query' => $params], $userToken);
    }

    public function post(string $endpoint, array $data = [], ?string $userToken = null): array
    {
        return $this->request('POST', $endpoint, $data, $userToken);
    }

    public function put(string $endpoint, array $data = [], ?string $userToken = null): array
    {
        return $this->request('PUT', $endpoint, $data, $userToken);
    }

    public function delete(string $endpoint, ?string $userToken = null): array
    {
        return $this->request('DELETE', $endpoint, [], $userToken);
    }

    private function request(string $method, string $endpoint, array $options = [], ?string $userToken = null): array
    {
        $this->checkCircuitBreaker();
        $this->checkRateLimit();

        $startTime = microtime(true);
        $token = $userToken ?? $this->getServiceToken();

        try {
            $response = Http::withToken($token)
                ->timeout($this->timeout)
                ->asJson()
                ->retry(3, 100, function ($exception) {
                    return ! in_array($exception->getCode(), [429, 401, 403]);
                })
                ->$method($this->baseUrl.$endpoint, $options);

            $duration = (int) ((microtime(true) - $startTime) * 1000);

            if ($response->status() === 429) {
                $retryAfter = (int) $response->header('Retry-After', 60);
                sleep($retryAfter);

                return $this->request($method, $endpoint, $options, $userToken);
            }

            if ($response->failed()) {
                $this->recordCircuitBreakerFailure();

                throw new McpServerException(
                    "MCP Server request failed: {$response->status()} - {$response->body()}",
                    $response->status()
                );
            }

            $this->resetCircuitBreaker();

            if (config('mcp-server.logging.log_requests', false)) {
                Log::channel('mcp')->debug('MCP Server request', [
                    'method' => $method,
                    'endpoint' => $endpoint,
                    'status' => $response->status(),
                    'duration_ms' => $duration,
                ]);
            }

            return $response->json() ?? [];

        } catch (McpServerException $e) {
            throw $e;
        } catch (\Exception $e) {
            $this->recordCircuitBreakerFailure();

            throw new McpServerException(
                "MCP Server connection failed: {$e->getMessage()}",
                0,
                $e
            );
        }
    }

    private function getServiceToken(): string
    {
        if ($this->serviceToken && $this->isServiceTokenValid()) {
            return $this->serviceToken;
        }

        $cachedToken = Cache::get('mcp_service_token');
        if ($cachedToken && $this->isServiceTokenValid()) {
            $this->serviceToken = $cachedToken;

            return $cachedToken;
        }

        $this->serviceToken = $this->authenticate();

        return $this->serviceToken;
    }

    private function authenticate(): string
    {
        Log::info('[MCP AUTH] Starting authentication', [
            'auth_method' => $this->authMethod,
            'api_token_set' => $this->apiToken !== null && $this->apiToken !== '',
            'email_set' => $this->serviceAccountEmail !== null && $this->serviceAccountEmail !== '',
        ]);

        try {
            if ($this->authMethod === 'token') {
                Log::info('[MCP AUTH] Using token authentication');

                return $this->authenticateWithToken();
            }

            Log::info('[MCP AUTH] Using password authentication');

            return $this->authenticateWithPassword();

        } catch (\Exception $e) {
            Log::error('[MCP AUTH] Authentication failed', [
                'method' => $this->authMethod,
                'error' => $e->getMessage(),
            ]);

            throw new McpServerException(
                'Service account authentication failed: '.$e->getMessage(),
                0,
                $e
            );
        }
    }

    private function authenticateWithToken(): string
    {
        if (! $this->apiToken) {
            throw new McpServerException('MCP_API_TOKEN not configured');
        }

        $response = Http::timeout($this->timeout)
            ->asJson()
            ->post($this->baseUrl.'/api/client-token', [
                'client_token' => $this->apiToken,
            ]);

        if ($response->failed()) {
            throw new McpServerException(
                'Token authentication failed: '.$response->body(),
                $response->status()
            );
        }

        $data = $response->json();
        $token = $data['access_token'];

        $ttl = config('mcp-server.tokens.cache_ttl', 1500);
        Cache::put('mcp_service_token', $token, now()->addSeconds($ttl));
        Cache::put('mcp_service_token_expires', now()->addSeconds($data['expires_in'] ?? 1800), now()->addSeconds($data['expires_in'] ?? 1800));

        Log::channel('mcp')->info('Service account authenticated successfully with token');

        return $token;
    }

    private function authenticateWithPassword(): string
    {
        $response = Http::timeout($this->timeout)
            ->asForm()
            ->post($this->baseUrl.'/token', [
                'username' => $this->serviceAccountEmail,
                'password' => $this->serviceAccountPassword,
            ]);

        if ($response->failed()) {
            throw new McpServerException(
                'Password authentication failed: '.$response->body(),
                $response->status()
            );
        }

        $data = $response->json();
        $token = $data['access_token'];

        $ttl = config('mcp-server.tokens.cache_ttl', 1500);
        Cache::put('mcp_service_token', $token, now()->addSeconds($ttl));
        Cache::put('mcp_service_token_expires', now()->addSeconds($data['expires_in'] ?? 1800), now()->addSeconds($data['expires_in'] ?? 1800));

        Log::channel('mcp')->info('Service account authenticated successfully with password');

        return $token;
    }

    private function isServiceTokenValid(): bool
    {
        $expiresAt = Cache::get('mcp_service_token_expires');

        if (! $expiresAt) {
            return false;
        }

        $threshold = config('mcp-server.tokens.refresh_threshold', 300);

        return $expiresAt->diffInSeconds(now()) > $threshold;
    }

    private function checkRateLimit(): void
    {
        if (! config('mcp-server.rate_limits.per_minute')) {
            return;
        }

        $key = 'mcp_rate_limit:'.request()->ip();
        $requests = Cache::get($key, 0);
        $limit = config('mcp-server.rate_limits.per_minute', 50);

        if ($requests >= $limit) {
            throw new RateLimitException('MCP Server rate limit approaching. Slow down requests.');
        }

        Cache::put($key, $requests + 1, now()->addMinute());

        $delay = config('mcp-server.rate_limits.request_delay_ms', 0);
        if ($delay > 0) {
            usleep($delay * 1000);
        }
    }

    private function checkCircuitBreaker(): void
    {
        if (! config('mcp-server.circuit_breaker.enabled', true)) {
            return;
        }

        $failures = Cache::get('mcp_circuit_breaker_failures', 0);
        $threshold = config('mcp-server.circuit_breaker.failure_threshold', 5);

        if ($failures >= $threshold) {
            $lastAttempt = Cache::get('mcp_circuit_breaker_last_attempt');
            $timeout = config('mcp-server.circuit_breaker.timeout', 300);

            if ($lastAttempt && $lastAttempt->diffInSeconds(now()) < $timeout) {
                throw new CircuitBreakerOpenException('MCP Server circuit breaker is open. Service temporarily unavailable.');
            }

            Cache::put('mcp_circuit_breaker_last_attempt', now(), now()->addMinutes(10));
        }
    }

    private function recordCircuitBreakerFailure(): void
    {
        if (! config('mcp-server.circuit_breaker.enabled', true)) {
            return;
        }

        $failures = Cache::get('mcp_circuit_breaker_failures', 0);
        Cache::put('mcp_circuit_breaker_failures', $failures + 1, now()->addMinutes(10));

        if ($failures + 1 >= config('mcp-server.circuit_breaker.failure_threshold', 5)) {
            Log::channel('mcp')->critical('MCP Server circuit breaker opened', [
                'failures' => $failures + 1,
            ]);
        }
    }

    private function resetCircuitBreaker(): void
    {
        Cache::put('mcp_circuit_breaker_failures', 0, now()->addMinutes(10));
    }

    public function isHealthy(): bool
    {
        try {
            $response = Http::timeout(5)->get($this->baseUrl.'/health');

            return $response->successful();
        } catch (\Exception $e) {
            return false;
        }
    }
}
