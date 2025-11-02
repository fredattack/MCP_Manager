<?php

namespace App\DataTransferObjects\CodeAnalysis;

class ASTResult
{
    public function __construct(
        public readonly array $classes = [],
        public readonly array $functions = [],
        public readonly array $dependencies = [],
        public readonly array $namespaces = [],
        public readonly array $metadata = [],
    ) {}

    public function toArray(): array
    {
        return [
            'classes' => $this->classes,
            'functions' => $this->functions,
            'dependencies' => $this->dependencies,
            'namespaces' => $this->namespaces,
            'metadata' => $this->metadata,
        ];
    }
}
