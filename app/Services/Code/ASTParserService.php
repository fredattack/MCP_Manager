<?php

declare(strict_types=1);

namespace App\Services\Code;

use Illuminate\Support\Facades\File;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitor\NameResolver;
use PhpParser\ParserFactory;

class ASTParserService
{
    private \PhpParser\Parser $parser;

    public function __construct()
    {
        $this->parser = (new ParserFactory)->createForNewestSupportedVersion();
    }

    /**
     * Analyze a repository
     *
     * @return array<string, mixed>
     */
    public function analyzeRepository(string $path): array
    {
        $phpFiles = $this->findPhpFiles($path);

        $functions = [];
        $classes = [];
        $dependencies = [];
        $files = [];

        foreach ($phpFiles as $file) {
            try {
                $analysis = $this->analyzeFile($file);

                $files[] = [
                    'path' => str_replace($path.'/', '', $file),
                    'lines' => $analysis['lines'],
                ];

                $functions = array_merge($functions, $analysis['functions']);
                $classes = array_merge($classes, $analysis['classes']);
                $dependencies = array_merge($dependencies, $analysis['dependencies']);
            } catch (\Exception $e) {
                // Skip files that can't be parsed
                continue;
            }
        }

        return [
            'files' => $files,
            'functions' => $functions,
            'classes' => $classes,
            'dependencies' => array_unique($dependencies),
            'statistics' => [
                'total_files' => count($files),
                'total_functions' => count($functions),
                'total_classes' => count($classes),
                'total_lines' => array_sum(array_column($files, 'lines')),
            ],
        ];
    }

    /**
     * Analyze a single file
     *
     * @return array<string, mixed>
     */
    public function analyzeFile(string $filePath): array
    {
        $code = File::get($filePath);
        $lines = substr_count($code, "\n") + 1;

        try {
            $ast = $this->parser->parse($code);

            if (! $ast) {
                return [
                    'lines' => $lines,
                    'functions' => [],
                    'classes' => [],
                    'dependencies' => [],
                ];
            }

            $traverser = new NodeTraverser;
            $traverser->addVisitor(new NameResolver);

            $visitor = new CodeAnalysisVisitor;
            $traverser->addVisitor($visitor);
            $traverser->traverse($ast);

            return [
                'lines' => $lines,
                'functions' => $visitor->getFunctions(),
                'classes' => $visitor->getClasses(),
                'dependencies' => $visitor->getDependencies(),
            ];
        } catch (\Exception $e) {
            return [
                'lines' => $lines,
                'functions' => [],
                'classes' => [],
                'dependencies' => [],
            ];
        }
    }

    /**
     * @return array<int, string>
     */
    private function findPhpFiles(string $path): array
    {
        if (! File::isDirectory($path)) {
            return [];
        }

        $files = [];
        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($path, \RecursiveDirectoryIterator::SKIP_DOTS)
        );

        foreach ($iterator as $file) {
            if ($file->isFile() && $file->getExtension() === 'php') {
                // Skip vendor directory
                if (str_contains($file->getPathname(), '/vendor/')) {
                    continue;
                }

                $files[] = $file->getPathname();
            }
        }

        return $files;
    }
}
