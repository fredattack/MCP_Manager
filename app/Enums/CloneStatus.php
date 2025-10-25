<?php

declare(strict_types=1);

namespace App\Enums;

enum CloneStatus: string
{
    case PENDING = 'pending';
    case CLONING = 'cloning';
    case COMPLETED = 'completed';
    case FAILED = 'failed';

    /**
     * Check if the clone is in progress.
     */
    public function isInProgress(): bool
    {
        return in_array($this, [self::PENDING, self::CLONING], true);
    }

    /**
     * Check if the clone is finished (successfully or not).
     */
    public function isFinished(): bool
    {
        return in_array($this, [self::COMPLETED, self::FAILED], true);
    }

    /**
     * Check if the clone was successful.
     */
    public function isSuccessful(): bool
    {
        return $this === self::COMPLETED;
    }
}
