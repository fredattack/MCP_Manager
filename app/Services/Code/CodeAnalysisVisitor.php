<?php

declare(strict_types=1);

namespace App\Services\Code;

use PhpParser\Node;
use PhpParser\NodeVisitorAbstract;

class CodeAnalysisVisitor extends NodeVisitorAbstract
{
    /** @var array<int, array<string, mixed>> */
    private array $functions = [];

    /** @var array<int, array<string, mixed>> */
    private array $classes = [];

    /** @var array<int, string> */
    private array $dependencies = [];

    public function enterNode(Node $node): void
    {
        // Extract functions
        if ($node instanceof Node\Stmt\Function_) {
            $this->functions[] = [
                'name' => $node->name->toString(),
                'line' => $node->getStartLine(),
                'params' => count($node->params),
            ];
        }

        // Extract class methods
        if ($node instanceof Node\Stmt\ClassMethod) {
            $this->functions[] = [
                'name' => $node->name->toString(),
                'line' => $node->getStartLine(),
                'params' => count($node->params),
                'visibility' => $this->getVisibility($node),
            ];
        }

        // Extract classes
        if ($node instanceof Node\Stmt\Class_) {
            $this->classes[] = [
                'name' => $node->name?->toString() ?? 'anonymous',
                'line' => $node->getStartLine(),
                'methods' => count($node->getMethods()),
                'properties' => count($node->getProperties()),
            ];
        }

        // Extract use statements (dependencies)
        if ($node instanceof Node\Stmt\Use_) {
            foreach ($node->uses as $use) {
                $this->dependencies[] = $use->name->toString();
            }
        }
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function getFunctions(): array
    {
        return $this->functions;
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function getClasses(): array
    {
        return $this->classes;
    }

    /**
     * @return array<int, string>
     */
    public function getDependencies(): array
    {
        return $this->dependencies;
    }

    private function getVisibility(Node\Stmt\ClassMethod $method): string
    {
        if ($method->isPublic()) {
            return 'public';
        }
        if ($method->isProtected()) {
            return 'protected';
        }
        if ($method->isPrivate()) {
            return 'private';
        }

        return 'public';
    }
}
