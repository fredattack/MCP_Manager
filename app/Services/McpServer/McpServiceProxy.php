<?php

namespace App\Services\McpServer;

use App\Models\User;
use App\Services\McpServer\Proxies\NotionProxy;

class McpServiceProxy
{
    public function __construct(
        private readonly McpServerClient $client,
        private readonly McpTokenManager $tokenManager
    ) {}

    public function notion(User $user): NotionProxy
    {
        return new NotionProxy($this->client, $this->tokenManager, $user);
    }

    public function getUserToken(User $user): string
    {
        return $this->tokenManager->getValidToken($user);
    }

    public function isUserAuthenticated(User $user): bool
    {
        return $this->tokenManager->isTokenValid($user);
    }

    public function authenticateUser(User $user, ?string $password = null): array
    {
        return $this->tokenManager->authenticate($user, $password);
    }

    public function revokeUserAccess(User $user): void
    {
        $this->tokenManager->revokeTokens($user);
    }
}
