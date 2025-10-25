<?php

declare(strict_types=1);

namespace App\Services\Git;

use App\Enums\GitConnectionStatus;
use App\Enums\GitProvider;
use App\Models\GitConnection;
use App\Models\User;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class GitOAuthService
{
    /**
     * Generate OAuth authorization URL with PKCE.
     *
     * @return array{auth_url: string, state: string, code_verifier: string}
     */
    public function generateAuthUrl(GitProvider $provider, string $redirectUri): array
    {
        $state = Str::random(40);
        $codeVerifier = $this->generateCodeVerifier();
        $codeChallenge = $this->generateCodeChallenge($codeVerifier);

        $params = [
            'client_id' => config("services.{$provider->value}.client_id"),
            'redirect_uri' => $redirectUri,
            'scope' => implode(' ', $provider->getDefaultScopes()),
            'state' => $state,
            'response_type' => 'code',
        ];

        // Add PKCE parameters
        if ($provider === GitProvider::GITHUB) {
            $params['code_challenge'] = $codeChallenge;
            $params['code_challenge_method'] = 'S256';
        }

        $authUrl = $provider->getAuthUrl().'?'.http_build_query($params);

        return [
            'auth_url' => $authUrl,
            'state' => $state,
            'code_verifier' => $codeVerifier,
        ];
    }

    /**
     * Exchange authorization code for access token.
     *
     * @return array{access_token: string, refresh_token: ?string, expires_in: ?int, scope: string}
     */
    public function exchangeCode(
        GitProvider $provider,
        string $code,
        string $codeVerifier,
        string $redirectUri
    ): array {
        $params = [
            'client_id' => config("services.{$provider->value}.client_id"),
            'client_secret' => config("services.{$provider->value}.client_secret"),
            'code' => $code,
            'redirect_uri' => $redirectUri,
        ];

        if ($provider === GitProvider::GITHUB) {
            $params['code_verifier'] = $codeVerifier;
        } else {
            $params['grant_type'] = 'authorization_code';
        }

        try {
            $response = Http::asForm()
                ->acceptJson()
                ->timeout(30)
                ->post($provider->getTokenUrl(), $params);

            $response->throw();

            $data = $response->json();

            return [
                'access_token' => $data['access_token'],
                'refresh_token' => $data['refresh_token'] ?? null,
                'expires_in' => $data['expires_in'] ?? null,
                'scope' => $data['scope'] ?? implode(' ', $provider->getDefaultScopes()),
            ];
        } catch (RequestException $e) {
            Log::error('OAuth token exchange failed', [
                'provider' => $provider->value,
                'status' => $e->response->status(),
                'error' => $e->response->json(),
            ]);

            throw $e;
        }
    }

    /**
     * Refresh an expired access token.
     */
    public function refreshToken(GitConnection $connection): GitConnection
    {
        if ($connection->refresh_token_enc === null) {
            throw new \RuntimeException('No refresh token available');
        }

        try {
            $response = Http::asForm()
                ->acceptJson()
                ->timeout(30)
                ->post($connection->provider->getTokenUrl(), [
                    'client_id' => config("services.{$connection->provider->value}.client_id"),
                    'client_secret' => config("services.{$connection->provider->value}.client_secret"),
                    'grant_type' => 'refresh_token',
                    'refresh_token' => $connection->getRefreshToken(),
                ]);

            $response->throw();

            $data = $response->json();

            // Update connection with new tokens
            $connection->setAccessToken($data['access_token']);

            if (isset($data['refresh_token'])) {
                $connection->setRefreshToken($data['refresh_token']);
            }

            if (isset($data['expires_in'])) {
                $connection->expires_at = now()->addSeconds($data['expires_in']);
            }

            $connection->status = GitConnectionStatus::ACTIVE;
            $connection->save();

            Log::info('OAuth token refreshed successfully', [
                'provider' => $connection->provider->value,
                'user_id' => $connection->user_id,
            ]);

            return $connection;
        } catch (RequestException $e) {
            Log::error('OAuth token refresh failed', [
                'provider' => $connection->provider->value,
                'user_id' => $connection->user_id,
                'status' => $e->response->status(),
                'error' => $e->response->json(),
            ]);

            $connection->status = GitConnectionStatus::ERROR;
            $connection->save();

            throw $e;
        }
    }

    /**
     * Create or update git connection for user.
     *
     * @param  array{access_token: string, refresh_token: ?string, expires_in: ?int, scope: string}  $tokenData
     */
    public function createOrUpdateConnection(
        User $user,
        GitProvider $provider,
        array $tokenData,
        string $externalUserId
    ): GitConnection {
        $connection = GitConnection::where('user_id', $user->id)
            ->where('provider', $provider)
            ->where('external_user_id', $externalUserId)
            ->first();

        if ($connection === null) {
            $connection = new GitConnection;
            $connection->user_id = $user->id;
            $connection->provider = $provider;
            $connection->external_user_id = $externalUserId;
        }

        $connection->setAccessToken($tokenData['access_token']);
        $connection->setRefreshToken($tokenData['refresh_token']);
        $connection->scopes = explode(' ', $tokenData['scope']);
        $connection->status = GitConnectionStatus::ACTIVE;

        if ($tokenData['expires_in'] !== null) {
            $connection->expires_at = now()->addSeconds($tokenData['expires_in']);
        }

        $connection->save();

        Log::info('Git connection created/updated', [
            'provider' => $provider->value,
            'user_id' => $user->id,
            'external_user_id' => $externalUserId,
        ]);

        return $connection;
    }

    /**
     * Generate PKCE code verifier.
     */
    private function generateCodeVerifier(): string
    {
        return Str::random(128);
    }

    /**
     * Generate PKCE code challenge from verifier.
     */
    private function generateCodeChallenge(string $verifier): string
    {
        return rtrim(strtr(base64_encode(hash('sha256', $verifier, true)), '+/', '-_'), '=');
    }
}
