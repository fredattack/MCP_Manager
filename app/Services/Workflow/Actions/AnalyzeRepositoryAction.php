<?php

declare(strict_types=1);

namespace App\Services\Workflow\Actions;

use App\Models\GitClone;
use App\Models\WorkflowExecution;
use App\Services\Code\ASTParserService;
use App\Services\Git\GitCloneService;
use App\Services\LLM\LLMRouter;
use App\Services\LLM\Prompts\AnalyzeCodePrompt;
use App\Services\Workflow\WorkflowEngine;
use Illuminate\Support\Facades\Log;

class AnalyzeRepositoryAction extends BaseAction
{
    public function __construct(
        WorkflowEngine $engine,
        private readonly GitCloneService $cloneService,
        private readonly ASTParserService $astParser,
        private readonly LLMRouter $llmRouter,
        private readonly AnalyzeCodePrompt $prompt
    ) {
        parent::__construct($engine);
    }

    /**
     * @return array<string, mixed>
     */
    public function execute(WorkflowExecution $execution): array
    {
        $repository = $execution->repository;

        if (! $repository) {
            throw new \Exception('Repository not found for execution');
        }

        // Step 1: Clone Repository
        $cloneStep = $this->engine->createStep($execution, 'Clone Repository', 1);
        $this->engine->startStep($cloneStep);

        try {
            $clone = $this->cloneRepository($repository->id);
            $this->engine->completeStep($cloneStep, [
                'clone_id' => $clone->id,
                'artifact_path' => $clone->artifact_path,
            ]);
        } catch (\Exception $e) {
            $this->engine->failStep($cloneStep, $e->getMessage());
            throw $e;
        }

        // Step 2: Parse Code with AST
        $parseStep = $this->engine->createStep($execution, 'Parse Code Structure', 2);
        $this->engine->startStep($parseStep);

        try {
            $astAnalysis = $this->astParser->analyzeRepository($clone->getLocalPath());
            $this->engine->completeStep($parseStep, [
                'files_analyzed' => count($astAnalysis['files'] ?? []),
                'functions_found' => count($astAnalysis['functions'] ?? []),
                'classes_found' => count($astAnalysis['classes'] ?? []),
            ]);
        } catch (\Exception $e) {
            $this->engine->failStep($parseStep, $e->getMessage());
            throw $e;
        }

        // Step 3: Generate LLM Prompt
        $promptStep = $this->engine->createStep($execution, 'Generate Analysis Prompt', 3);
        $this->engine->startStep($promptStep);

        try {
            $promptMessages = $this->prompt->generate(
                $repository->name,
                $astAnalysis
            );
            $this->engine->completeStep($promptStep, [
                'prompt_tokens_estimate' => $this->estimateTokens($promptMessages),
            ]);
        } catch (\Exception $e) {
            $this->engine->failStep($promptStep, $e->getMessage());
            throw $e;
        }

        // Step 4: Call LLM for Analysis
        $llmStep = $this->engine->createStep($execution, 'AI Code Analysis', 4);
        $this->engine->startStep($llmStep);

        try {
            $llmResponse = $this->llmRouter->chat($promptMessages);
            $this->engine->completeStep($llmStep, [
                'provider' => $llmResponse['provider'],
                'model' => $llmResponse['model'],
                'tokens_used' => $llmResponse['usage']['total_tokens'],
                'duration' => $llmResponse['duration'],
            ]);
        } catch (\Exception $e) {
            $this->engine->failStep($llmStep, $e->getMessage());
            throw $e;
        }

        // Step 5: Parse and Store Results
        $resultsStep = $this->engine->createStep($execution, 'Process Results', 5);
        $this->engine->startStep($resultsStep);

        try {
            $analysis = $this->parseAnalysisResults($llmResponse['content']);
            $this->engine->completeStep($resultsStep, [
                'analysis_complete' => true,
            ]);
        } catch (\Exception $e) {
            $this->engine->failStep($resultsStep, $e->getMessage());
            throw $e;
        }

        Log::info('Repository analysis completed', [
            'execution_id' => $execution->id,
            'repository' => $repository->full_name,
        ]);

        return [
            'repository' => [
                'id' => $repository->id,
                'name' => $repository->full_name,
                'language' => $repository->language,
            ],
            'clone' => [
                'id' => $clone->id,
                'size_mb' => $clone->size_mb,
            ],
            'code_structure' => [
                'files' => count($astAnalysis['files'] ?? []),
                'functions' => count($astAnalysis['functions'] ?? []),
                'classes' => count($astAnalysis['classes'] ?? []),
            ],
            'llm_analysis' => $analysis,
            'metadata' => [
                'provider' => $llmResponse['provider'],
                'model' => $llmResponse['model'],
                'tokens_used' => $llmResponse['usage']['total_tokens'],
            ],
        ];
    }

    public function getName(): string
    {
        return 'Analyze Repository';
    }

    public function getDescription(): string
    {
        return 'Clone a repository, analyze its code structure, and generate insights using AI';
    }

    private function cloneRepository(int $repositoryId): GitClone
    {
        // This would need proper implementation with GitConnection
        // For now, simplified version
        $repository = \App\Models\GitRepository::findOrFail($repositoryId);
        $connection = $repository->connection;

        $clone = $this->cloneService->initializeClone($repository, 'main', 'local');

        return $this->cloneService->executeClone($clone, $connection);
    }

    /**
     * @param  array<int, array<string, string>>  $messages
     */
    private function estimateTokens(array $messages): int
    {
        $text = collect($messages)->pluck('content')->implode(' ');

        return (int) (strlen($text) / 4); // Rough estimate: 4 chars ≈ 1 token
    }

    /**
     * @return array<string, mixed>
     */
    private function parseAnalysisResults(string $content): array
    {
        // Try to extract JSON from the response
        if (preg_match('/\{[\s\S]*\}/', $content, $matches)) {
            $json = json_decode($matches[0], true);
            if ($json) {
                return $json;
            }
        }

        // Fallback: return raw content
        return [
            'raw_analysis' => $content,
            'summary' => substr($content, 0, 500),
        ];
    }
}
