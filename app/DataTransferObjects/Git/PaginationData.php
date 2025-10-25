<?php

declare(strict_types=1);

namespace App\DataTransferObjects\Git;

readonly class PaginationData
{
    public function __construct(
        public int $page = 1,
        public int $perPage = 50,
        public ?string $nextCursor = null,
    ) {}

    /**
     * Get the next page number.
     */
    public function nextPage(): int
    {
        return $this->page + 1;
    }

    /**
     * Check if there is a next page.
     */
    public function hasNextPage(): bool
    {
        return $this->nextCursor !== null;
    }
}
