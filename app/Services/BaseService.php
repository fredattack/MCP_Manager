<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;

abstract class BaseService
{
    /**
     * @param array<string, mixed> $filters
     * @return LengthAwarePaginator<int, Model>
     */
    abstract public function list(array $filters = []): LengthAwarePaginator;

    abstract public function find(int|string $id): ?Model;

    /**
     * @param array<string, mixed> $data
     */
    abstract public function create(array $data): Model;

    /**
     * @param array<string, mixed> $data
     */
    abstract public function update(int|string $id, array $data): Model;

    abstract public function delete(int|string $id): bool;
}