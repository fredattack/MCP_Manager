<?php

namespace App\Services\LLM\Prompts;

use App\DataTransferObjects\CodeAnalysis\ASTResult;

class AnalyzeCodePrompt extends BasePrompt
{
    public function getSystemPrompt(): string
    {
        $path = storage_path('prompts/analyze_code_v1_system.txt');

        return is_file($path) ? (string) file_get_contents($path) : 'You are a senior software architect.';
    }

    public function getUserPrompt(array $context): string
    {
        $path = storage_path('prompts/analyze_code_v1.txt');

        return is_file($path) ? (string) file_get_contents($path) : 'Repository: {{repo_name}}';
    }

    public function getVersion(): string
    {
        return 'v1.0';
    }

    /**
     * @param  array{name:string,language?:string,file_count?:int,files?:array<int,string>}  $repoData
     * @return array<string,mixed>
     */
    public function buildContext(ASTResult $ast, array $repoData): array
    {
        return [
            'repo_name' => $repoData['name'],
            'language' => $repoData['language'] ?? 'PHP',
            'framework' => $this->detectFramework($ast, $repoData),
            'file_count' => $repoData['file_count'] ?? 0,
            'total_lines' => $ast->metadata['lines'] ?? 0,
            'file_tree' => $this->formatFileTree($repoData['files'] ?? []),
            'class_count' => count($ast->classes),
            'class_list' => $this->formatClasses($ast->classes),
            'function_count' => count($ast->functions),
            'function_list' => $this->formatFunctions($ast->functions),
            'dependencies' => $this->formatDependencies($ast->dependencies),
            'namespaces' => implode(', ', $ast->namespaces),
        ];
    }

    private function detectFramework(ASTResult $ast, array $repoData): string
    {
        $dependencies = $ast->dependencies;

        if (in_array('laravel/framework', $dependencies, true)) {
            return 'Laravel';
        }

        if (in_array('symfony/symfony', $dependencies, true)) {
            return 'Symfony';
        }

        return 'Unknown';
    }

    /**
     * @param  array<int,string>  $files
     */
    private function formatFileTree(array $files): string
    {
        $tree = [];

        foreach (array_slice($files, 0, 50) as $file) {
            $tree[] = '- '.$file;
        }

        if (count($files) > 50) {
            $tree[] = '... and '.(count($files) - 50).' more files';
        }

        return implode("\n", $tree);
    }

    /**
     * @param  array<int,array{name:string,methods_count?:int}>  $classes
     */
    private function formatClasses(array $classes): string
    {
        $formatted = [];

        foreach (array_slice($classes, 0, 20) as $class) {
            $name = $class['name'] ?? '';
            $methods = $class['methods_count'] ?? 0;
            $formatted[] = "- {$name} ({$methods} methods)";
        }

        if (count($classes) > 20) {
            $formatted[] = '... and '.(count($classes) - 20).' more classes';
        }

        return implode("\n", $formatted);
    }

    /**
     * @param  array<int,array{name:string}>  $functions
     */
    private function formatFunctions(array $functions): string
    {
        $formatted = [];

        foreach (array_slice($functions, 0, 20) as $function) {
            $name = $function['name'] ?? '';
            $formatted[] = "- {$name}()";
        }

        if (count($functions) > 20) {
            $formatted[] = '... and '.(count($functions) - 20).' more functions';
        }

        return implode("\n", $formatted);
    }

    /**
     * @param  array<int,string>  $dependencies
     */
    private function formatDependencies(array $dependencies): string
    {
        return implode("\n", array_map(static fn ($dep) => '- '.$dep, $dependencies));
    }
}
