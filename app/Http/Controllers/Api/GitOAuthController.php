<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Enums\GitProvider;
use App\Http\Controllers\Controller;
use App\Services\Git\Clients\GitHubClient;
use App\Services\Git\Clients\GitLabClient;
use App\Services\Git\GitOAuthService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Enum;

class GitOAuthController extends Controller
{
    public function __construct(
        private readonly GitOAuthService $oauthService
    ) {}

    /**
     * Initiate OAuth flow - Generate authorization URL.
     *
     * POST /api/git/{provider}/oauth/start
     */
    public function start(Request $request, string $provider): JsonResponse
    {
        $startTime = microtime(true);

        $validator = Validator::make(['provider' => $provider], [
            'provider' => ['required', new Enum(GitProvider::class)],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'error' => 'Invalid provider',
                'message' => $validator->errors()->first(),
            ], 400);
        }

        $providerEnum = GitProvider::from($provider);
        $redirectUri = route('api.git.oauth.callback', ['provider' => $provider]);

        try {
            $authData = $this->oauthService->generateAuthUrl($providerEnum, $redirectUri);

            // Store state and code_verifier in cache for 10 minutes
            $cacheKey = "git_oauth_{$authData['state']}";
            Cache::put($cacheKey, [
                'provider' => $provider,
                'code_verifier' => $authData['code_verifier'],
                'user_id' => $request->user()?->id,
            ], 600);

            $duration = round((microtime(true) - $startTime) * 1000, 2);

            Log::info('OAuth flow started', [
                'provider' => $provider,
                'user_id' => $request->user()?->id,
                'duration_ms' => $duration,
            ]);

            return response()->json([
                'auth_url' => $authData['auth_url'],
                'state' => $authData['state'],
                'expires_in' => 600,
            ]);
        } catch (\Exception $e) {
            Log::error('OAuth start failed', [
                'provider' => $provider,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'error' => 'Failed to initiate OAuth flow',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Handle OAuth callback - Exchange code for tokens.
     *
     * GET /api/git/{provider}/oauth/callback
     */
    public function callback(Request $request, string $provider): RedirectResponse
    {
        $startTime = microtime(true);

        $validator = Validator::make([
            'code' => $request->input('code'),
            'state' => $request->input('state'),
            'provider' => $provider,
        ], [
            'code' => 'required|string',
            'state' => 'required|string',
            'provider' => ['required', new Enum(GitProvider::class)],
        ]);

        if ($validator->fails()) {
            return redirect()->to('/git/connections?error=invalid_request');
        }

        $code = $request->input('code');
        $state = $request->input('state');

        // Retrieve stored OAuth data
        $cacheKey = "git_oauth_{$state}";
        $oauthData = Cache::get($cacheKey);

        if ($oauthData === null) {
            return redirect()->to('/git/connections?error=expired_state');
        }

        if ($oauthData['provider'] !== $provider) {
            return redirect()->to('/git/connections?error=provider_mismatch');
        }

        $providerEnum = GitProvider::from($provider);
        $redirectUri = route('api.git.oauth.callback', ['provider' => $provider]);

        try {
            // Exchange authorization code for access token
            $tokenData = $this->oauthService->exchangeCode(
                $providerEnum,
                $code,
                $oauthData['code_verifier'],
                $redirectUri
            );

            // Get user information from provider
            $client = match ($providerEnum) {
                GitProvider::GITHUB => new GitHubClient($tokenData['access_token']),
                GitProvider::GITLAB => new GitLabClient($tokenData['access_token']),
            };

            $userData = $client->getAuthenticatedUser();
            $externalUserId = (string) $userData['id'];

            // Create or update connection
            $connection = $this->oauthService->createOrUpdateConnection(
                $request->user(),
                $providerEnum,
                $tokenData,
                $externalUserId
            );

            // Store user metadata
            $connection->meta = [
                'username' => $userData['login'] ?? $userData['username'] ?? null,
                'email' => $userData['email'] ?? null,
                'avatar_url' => $userData['avatar_url'] ?? null,
                'name' => $userData['name'] ?? null,
            ];
            $connection->save();

            // Clean up cache
            Cache::forget($cacheKey);

            $duration = round((microtime(true) - $startTime) * 1000, 2);

            Log::info('OAuth flow completed', [
                'provider' => $provider,
                'user_id' => $request->user()->id,
                'external_user_id' => $externalUserId,
                'duration_ms' => $duration,
            ]);

            // Redirect to connections page with success parameter
            return redirect()->to("/git/connections?{$provider}_connected=true");
        } catch (\Exception $e) {
            Log::error('OAuth callback failed', [
                'provider' => $provider,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            // Redirect to connections page with error parameter
            $errorType = 'unknown';

            if (str_contains($e->getMessage(), 'invalid_token') || str_contains($e->getMessage(), 'invalid token')) {
                $errorType = 'invalid_token';
            } elseif (str_contains($e->getMessage(), 'rate limit')) {
                $errorType = 'rate_limit';
            } elseif (str_contains($e->getMessage(), 'insufficient')) {
                $errorType = 'insufficient_scope';
            }

            return redirect()->to("/git/connections?error={$errorType}");
        }
    }

    /**
     * Disconnect Git provider.
     *
     * DELETE /api/git/{provider}/disconnect
     */
    public function disconnect(Request $request, string $provider): JsonResponse
    {
        $validator = Validator::make(['provider' => $provider], [
            'provider' => ['required', new Enum(GitProvider::class)],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'error' => 'Invalid provider',
                'message' => $validator->errors()->first(),
            ], 400);
        }

        $providerEnum = GitProvider::from($provider);

        try {
            $deleted = $this->oauthService->disconnect($request->user(), $providerEnum);

            if (! $deleted) {
                return response()->json([
                    'error' => 'Connection not found',
                    'message' => 'No active connection found for this provider',
                ], 404);
            }

            Log::info('Git connection disconnected', [
                'provider' => $provider,
                'user_id' => $request->user()->id,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Connection disconnected successfully',
            ]);
        } catch (\Exception $e) {
            Log::error('Disconnect failed', [
                'provider' => $provider,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'error' => 'Failed to disconnect',
                'message' => $e->getMessage(),
            ], 500);
        }
    }
}
