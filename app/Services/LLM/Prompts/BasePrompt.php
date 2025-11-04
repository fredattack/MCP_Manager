<?php

namespace App\Services\LLM\Prompts;

abstract class BasePrompt
{
    abstract public function getSystemPrompt(): string;

    abstract public function getUserPrompt(array $context): string;

    abstract public function getVersion(): string;

    /**
     * Render the system/user prompts with metadata.
     *
     * @param  array<string, mixed>  $context
     * @return array{system:string,user:string,version:string,token_count:int}
     */
    public function render(array $context): array
    {
        $userTemplate = $this->getUserPrompt($context);
        $user = $this->replaceVariables($userTemplate, $context);

        return [
            'system' => $this->getSystemPrompt(),
            'user' => $user,
            'version' => $this->getVersion(),
            'token_count' => $this->estimateTokens($user),
        ];
    }

    /**
     * @param  array<string, mixed>  $context
     */
    protected function replaceVariables(string $template, array $context): string
    {
        foreach ($context as $key => $value) {
            $placeholder = '{{'.$key.'}}';
            $template = str_replace($placeholder, (string) $value, $template);
        }

        return $template;
    }

    protected function estimateTokens(string $content): int
    {
        // Rough estimation: 1 token ≈ 4 characters
        return (int) ceil(strlen($content) / 4);
    }

    protected function truncateIfNeeded(string $content, int $maxTokens = 4000): string
    {
        $estimatedTokens = $this->estimateTokens($content);

        if ($estimatedTokens <= $maxTokens) {
            return $content;
        }

        $maxChars = $maxTokens * 4;

        return substr($content, 0, $maxChars)."\n\n[... truncated ...]";
    }
}
