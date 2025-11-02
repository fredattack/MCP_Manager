<?php

namespace Tests\Unit\Services\LLM\Prompts;

use App\DataTransferObjects\CodeAnalysis\ASTResult;
use App\Services\LLM\Prompts\AnalyzeCodePrompt;
use App\Services\LLM\Prompts\BasePrompt;
use App\Services\LLM\Prompts\PromptBuilder;
use Tests\TestCase;

class PromptBuilderTest extends TestCase
{
    public function test_build_returns_payload_under_token_budget(): void
    {
        $builder = new PromptBuilder;
        $prompt = new AnalyzeCodePrompt;

        $ast = new ASTResult(
            classes: [],
            functions: [],
            dependencies: ['laravel/framework'],
            namespaces: [],
            metadata: ['lines' => 10]
        );

        $context = $prompt->buildContext($ast, [
            'name' => 'tiny-app',
            'language' => 'PHP',
            'file_count' => 1,
            'files' => ['app/Console/Kernel.php'],
        ]);

        $rendered = $builder->build($prompt, $context, maxTokens: 4000);

        $this->assertArrayHasKey('system', $rendered);
        $this->assertArrayHasKey('user', $rendered);
        $this->assertArrayHasKey('version', $rendered);
        $this->assertArrayHasKey('token_count', $rendered);
        $this->assertLessThanOrEqual(4000, $rendered['token_count']);
    }

    public function test_build_truncates_when_exceeding_token_budget(): void
    {
        $builder = new PromptBuilder;
        $prompt = new AnalyzeCodePrompt;

        // Create a very large context to exceed token budget
        $largeList = [];
        for ($i = 0; $i < 1000; $i++) {
            $largeList[] = 'File'.$i.'.php';
        }

        $ast = new ASTResult(
            classes: array_map(fn ($i) => ['name' => 'Class'.$i, 'methods_count' => 1], range(1, 200)),
            functions: array_map(fn ($i) => ['name' => 'func'.$i], range(1, 200)),
            dependencies: [],
            namespaces: [],
            metadata: ['lines' => 100000]
        );

        $context = $prompt->buildContext($ast, [
            'name' => 'big-app',
            'language' => 'PHP',
            'file_count' => count($largeList),
            'files' => $largeList,
        ]);

        $rendered = $builder->build($prompt, $context, maxTokens: 500);

        $this->assertLessThanOrEqual(500, $rendered['token_count']);
        $this->assertStringContainsString('[... truncated ...]', $rendered['user']);
    }

    public function test_build_accounts_for_system_tokens_when_truncating_user_prompt(): void
    {
        $builder = new PromptBuilder;

        $prompt = new class extends BasePrompt
        {
            public function getSystemPrompt(): string
            {
                return str_repeat('S', 2000); // ≈ 500 tokens
            }

            public function getUserPrompt(array $context): string
            {
                return $context['content'];
            }

            public function getVersion(): string
            {
                return 'test-system-awareness';
            }
        };

        $context = [
            'content' => str_repeat('U', 8000), // ≈ 2000 tokens
        ];

        $result = $builder->build($prompt, $context, maxTokens: 700);

        $systemTokens = (int) ceil(strlen($result['system']) / 4);
        $userTokens = (int) ceil(strlen($result['user']) / 4);

        $this->assertLessThanOrEqual(700, $result['token_count']);
        $this->assertSame($systemTokens + $userTokens, $result['token_count']);
        $this->assertLessThanOrEqual(200, $userTokens);
    }
}
