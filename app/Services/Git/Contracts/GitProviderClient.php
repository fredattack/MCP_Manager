<?php

declare(strict_types=1);

namespace App\Services\Git\Contracts;

use App\DataTransferObjects\Git\PaginationData;
use App\DataTransferObjects\Git\RepositoryData;

interface GitProviderClient
{
    /**
     * List repositories for the authenticated user.
     *
     * @param  array<string, mixed>  $filters
     * @return array{items: array<int, RepositoryData>, pagination: PaginationData}
     */
    public function listRepositories(array $filters = [], ?PaginationData $pagination = null): array;

    /**
     * Get a single repository by owner and name.
     */
    public function getRepository(string $owner, string $repo): RepositoryData;

    /**
     * Get the authenticated user information.
     *
     * @return array<string, mixed>
     */
    public function getAuthenticatedUser(): array;

    /**
     * Check if the access token is valid.
     */
    public function validateToken(): bool;
}
