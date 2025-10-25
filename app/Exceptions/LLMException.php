<?php

declare(strict_types=1);

namespace App\Exceptions;

use Exception;

class LLMException extends Exception
{
    public static function timeout(string $provider, float $seconds): self
    {
        return new self("LLM provider '{$provider}' timed out after {$seconds} seconds");
    }

    public static function rateLimitExceeded(string $provider): self
    {
        return new self("LLM provider '{$provider}' rate limit exceeded");
    }

    public static function invalidResponse(string $provider, string $reason): self
    {
        return new self("Invalid response from LLM provider '{$provider}': {$reason}");
    }

    public static function apiError(string $provider, string $message): self
    {
        return new self("API error from LLM provider '{$provider}': {$message}");
    }

    public static function allProvidersFailed(): self
    {
        return new self('All LLM providers failed to respond');
    }
}
