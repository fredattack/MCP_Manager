<?php

declare(strict_types=1);

namespace App\Exceptions;

use Illuminate\Support\Facades\Log;

class IntegrationException extends \Exception
{
    public function __construct(
        private readonly string $service,
        string $message = '',
        int $code = 0,
        ?\Throwable $throwable = null
    ) {
        parent::__construct($message, $code, $throwable);
    }

    public function getService(): string
    {
        return $this->service;
    }

    public function report(): void
    {
        Log::error('Integration error', [
            'service' => $this->service,
            'message' => $this->getMessage(),
            'user_id' => auth()->id(),
        ]);
    }
}
