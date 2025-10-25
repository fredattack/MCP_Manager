<?php

declare(strict_types=1);

namespace App\Services\LLM\Prompts;

abstract class BasePrompt
{
    /**
     * Generate the prompt messages
     *
     * @param  mixed  ...$args
     * @return array<int, array<string, string>>
     */
    abstract public function generate(...$args): array;

    /**
     * Get the version of this prompt
     */
    public function getVersion(): string
    {
        return 'v1';
    }
}
