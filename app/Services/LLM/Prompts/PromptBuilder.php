<?php

namespace App\Services\LLM\Prompts;

class PromptBuilder
{
    /**
     * Build the final prompts payload and ensure it fits under the token budget.
     *
     * @param  array<string,mixed>  $context
     * @return array{system:string,user:string,version:string,token_count:int}
     */
    public function build(BasePrompt $prompt, array $context, int $maxTokens = 4000): array
    {
        $rendered = $prompt->render($context);

        if (($rendered['token_count'] ?? 0) <= $maxTokens) {
            return $rendered;
        }

        $system = (string) ($rendered['system'] ?? '');
        $systemTokens = $this->estimateTokens($system);

        if ($systemTokens > $maxTokens) {
            $system = $this->truncateByTokens($system, $maxTokens);
            $systemTokens = $this->estimateTokens($system);
            $rendered['system'] = $system;
        }

        $remainingTokens = max(0, $maxTokens - $systemTokens);

        $user = (string) ($rendered['user'] ?? '');
        $user = $this->truncateByTokens($user, $remainingTokens);

        $userTokens = $this->estimateTokens($user);

        $rendered['user'] = $user;
        $rendered['token_count'] = $systemTokens + $userTokens;

        return $rendered;
    }

    private function estimateTokens(string $content): int
    {
        // Rough estimation: 1 token ≈ 4 characters
        return (int) ceil(strlen($content) / 4);
    }

    private function truncateByTokens(string $content, int $maxTokens): string
    {
        if ($maxTokens <= 0) {
            return '';
        }

        $estimatedTokens = $this->estimateTokens($content);

        if ($estimatedTokens <= $maxTokens) {
            return $content;
        }

        $suffix = "\n\n[... truncated ...]";
        $suffixChars = strlen($suffix);
        $maxChars = ($maxTokens * 4) - $suffixChars;
        if ($maxChars < 0) {
            $maxChars = 0;
        }

        return substr($content, 0, $maxChars).$suffix;
    }
}
