<?php

namespace App\Services\Code\Parsers;

use App\DataTransferObjects\CodeAnalysis\ASTResult;
use PhpParser\Error;
use PhpParser\Node;
use PhpParser\Node\Name;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\Function_;
use PhpParser\Node\Stmt\Namespace_;
use PhpParser\Node\Stmt\Use_;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitorAbstract;
use PhpParser\ParserFactory;

class PHPParser implements ParserInterface
{
    private \PhpParser\Parser $parser;

    public function __construct()
    {
        $this->parser = (new ParserFactory)->createForNewestSupportedVersion();
    }

    public function parse(string $content): ASTResult
    {
        try {
            $stmts = $this->parser->parse($content ?? '');

            if ($stmts === null) {
                // Empty or comment-only content
                return new ASTResult(
                    classes: [],
                    functions: [],
                    dependencies: [],
                    namespaces: [],
                    metadata: [
                        'lines' => substr_count($content, "\n") + (strlen($content) > 0 ? 1 : 0),
                        'size_bytes' => strlen($content),
                    ]
                );
            }

            $traverser = new NodeTraverser;
            $collector = new class extends NodeVisitorAbstract
            {
                public array $classes = [];

                public array $functions = [];

                public array $dependencies = [];

                public array $namespaces = [];

                public function enterNode(Node $node): void
                {
                    if ($node instanceof Namespace_) {
                        $this->namespaces[] = $node->name instanceof Name ? $node->name->toString() : '';
                    }

                    if ($node instanceof Class_ && $node->name !== null) {
                        $methods = 0;
                        foreach ($node->stmts ?? [] as $stmt) {
                            if ($stmt instanceof \PhpParser\Node\Stmt\ClassMethod) {
                                $methods++;
                            }
                        }

                        $this->classes[] = [
                            'name' => (string) $node->name,
                            'methods_count' => $methods,
                        ];
                    }

                    if ($node instanceof Function_) {
                        $this->functions[] = [
                            'name' => (string) $node->name,
                        ];
                    }

                    if ($node instanceof Use_) {
                        foreach ($node->uses as $useUse) {
                            $this->dependencies[] = $useUse->name->toString();
                        }
                    }
                }
            };

            $traverser->addVisitor($collector);
            $traverser->traverse($stmts);

            // Deduplicate dependencies/namespaces
            $namespaces = array_values(array_unique(array_filter($collector->namespaces)));
            $dependencies = array_values(array_unique($collector->dependencies));

            return new ASTResult(
                classes: $collector->classes,
                functions: $collector->functions,
                dependencies: $dependencies,
                namespaces: $namespaces,
                metadata: [
                    'lines' => substr_count($content, "\n") + (strlen($content) > 0 ? 1 : 0),
                    'size_bytes' => strlen($content),
                ]
            );
        } catch (Error $error) {
            throw new \RuntimeException('Parse error: '.$error->getMessage());
        }
    }

    public function supports(string $extension): bool
    {
        return strtolower($extension) === 'php';
    }
}
