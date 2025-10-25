<?php

declare(strict_types=1);

namespace App\Services\LLM\Prompts;

class AnalyzeCodePrompt extends BasePrompt
{
    /**
     * @param  string  $repositoryName
     * @param  array<string, mixed>  $astAnalysis
     * @return array<int, array<string, string>>
     */
    public function generate(...$args): array
    {
        [$repositoryName, $astAnalysis] = $args;

        $statistics = $astAnalysis['statistics'] ?? [];
        $files = array_slice($astAnalysis['files'] ?? [], 0, 20); // Limit to first 20 files
        $classes = array_slice($astAnalysis['classes'] ?? [], 0, 15); // Limit to first 15 classes
        $dependencies = array_slice($astAnalysis['dependencies'] ?? [], 0, 30); // Limit to first 30

        $systemPrompt = <<<'PROMPT'
You are a senior software architect and code quality expert. Your role is to analyze codebases and provide insightful, actionable feedback on architecture, design patterns, code quality, and potential improvements.

When analyzing code, focus on:
1. Overall architecture and design patterns
2. Code organization and structure
3. Potential issues, anti-patterns, or technical debt
4. Security concerns
5. Performance considerations
6. Best practices and recommendations

Provide your analysis in structured JSON format.
PROMPT;

        $userPrompt = <<<PROMPT
Analyze the following codebase for "{$repositoryName}".

## Repository Statistics:
- Total Files: {$statistics['total_files']}
- Total Classes: {$statistics['total_classes']}
- Total Functions/Methods: {$statistics['total_functions']}
- Total Lines of Code: {$statistics['total_lines']}

## File Structure (sample):
{$this->formatFileList($files)}

## Classes Found (sample):
{$this->formatClassList($classes)}

## Dependencies (sample):
{$this->formatDependencyList($dependencies)}

## Your Task:
Analyze this codebase and provide a comprehensive assessment. Return your analysis in the following JSON structure:

```json
{
  "architecture": {
    "patterns": ["List of identified design patterns"],
    "structure": "Description of overall code organization",
    "framework_used": "Detected framework or architecture style"
  },
  "quality_score": 7.5,
  "quality_assessment": {
    "strengths": ["List of strong points"],
    "weaknesses": ["List of areas needing improvement"]
  },
  "issues": [
    {
      "severity": "high|medium|low",
      "category": "security|performance|maintainability|etc",
      "description": "Description of the issue",
      "recommendation": "How to fix it"
    }
  ],
  "recommendations": [
    {
      "priority": "high|medium|low",
      "category": "Category of recommendation",
      "action": "Specific action to take",
      "benefit": "Expected benefit"
    }
  ],
  "summary": "Brief 2-3 sentence overall assessment"
}
```

Provide a thorough but concise analysis focusing on the most important insights.
PROMPT;

        return [
            ['role' => 'system', 'content' => $systemPrompt],
            ['role' => 'user', 'content' => $userPrompt],
        ];
    }

    /**
     * @param  array<int, array<string, mixed>>  $files
     */
    private function formatFileList(array $files): string
    {
        if (empty($files)) {
            return '(No files found)';
        }

        $lines = [];
        foreach ($files as $file) {
            $lines[] = "- {$file['path']} ({$file['lines']} lines)";
        }

        return implode("\n", $lines);
    }

    /**
     * @param  array<int, array<string, mixed>>  $classes
     */
    private function formatClassList(array $classes): string
    {
        if (empty($classes)) {
            return '(No classes found)';
        }

        $lines = [];
        foreach ($classes as $class) {
            $methods = $class['methods'] ?? 0;
            $properties = $class['properties'] ?? 0;
            $lines[] = "- {$class['name']} ({$methods} methods, {$properties} properties)";
        }

        return implode("\n", $lines);
    }

    /**
     * @param  array<int, string>  $dependencies
     */
    private function formatDependencyList(array $dependencies): string
    {
        if (empty($dependencies)) {
            return '(No dependencies found)';
        }

        return implode("\n", array_map(fn ($dep) => "- {$dep}", $dependencies));
    }
}
