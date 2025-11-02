<?php

namespace Tests\Unit\Services\LLM\Prompts;

use App\DataTransferObjects\CodeAnalysis\ASTResult;
use App\Services\LLM\Prompts\AnalyzeCodePrompt;
use Tests\TestCase;

class AnalyzeCodePromptTest extends TestCase
{
    public function test_builds_context_correctly(): void
    {
        $prompt = new AnalyzeCodePrompt;

        $ast = new ASTResult(
            classes: [
                ['name' => 'UserController', 'methods_count' => 5],
            ],
            functions: [
                ['name' => 'helper_function'],
            ],
            dependencies: ['laravel/framework', 'guzzlehttp/guzzle'],
            namespaces: ['App\\Controllers', 'App\\Services'],
            metadata: ['lines' => 1500]
        );

        $repoData = [
            'name' => 'my-awesome-app',
            'language' => 'PHP',
            'file_count' => 50,
            'files' => ['app/Controller/UserController.php'],
        ];

        $context = $prompt->buildContext($ast, $repoData);

        $this->assertEquals('my-awesome-app', $context['repo_name']);
        $this->assertEquals('Laravel', $context['framework']);
        $this->assertEquals(1, $context['class_count']);
        $this->assertEquals(1, $context['function_count']);
    }

    public function test_renders_prompt_with_variables(): void
    {
        $prompt = new AnalyzeCodePrompt;

        $ast = new ASTResult(
            classes: [],
            functions: [],
            dependencies: ['laravel/framework'],
            namespaces: [],
            metadata: []
        );

        $context = $prompt->buildContext($ast, [
            'name' => 'test-app',
            'language' => 'PHP',
        ]);

        $rendered = $prompt->render($context);

        $this->assertArrayHasKey('system', $rendered);
        $this->assertArrayHasKey('user', $rendered);
        $this->assertIsString($rendered['system']);
        $this->assertIsString($rendered['user']);
        $this->assertStringContainsString('test-app', $rendered['user']);
        $this->assertStringNotContainsString('{{repo_name}}', $rendered['user']);
        $this->assertArrayHasKey('token_count', $rendered);
        $this->assertIsInt($rendered['token_count']);
    }
}
