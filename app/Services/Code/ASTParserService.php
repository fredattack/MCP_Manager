<?php

namespace App\Services\Code;

use App\DataTransferObjects\CodeAnalysis\ASTResult;
use App\Services\Code\Parsers\ParserInterface;
use App\Services\Code\Parsers\PHPParser;
use InvalidArgumentException;

class ASTParserService
{
    /** @var array<int, ParserInterface> */
    private array $parsers;

    public function __construct(?array $parsers = null)
    {
        // Allow dependency injection, but provide default PHP parser
        $this->parsers = $parsers ?? [
            new PHPParser,
        ];
    }

    public function parseFile(string $path): ASTResult
    {
        if (! is_file($path)) {
            throw new InvalidArgumentException("File not found: {$path}");
        }

        $extension = strtolower(pathinfo($path, PATHINFO_EXTENSION));

        $parser = $this->findParserForExtension($extension);
        if ($parser === null) {
            throw new InvalidArgumentException("No parser available for extension: .{$extension}");
        }

        $content = file_get_contents($path);
        if ($content === false) {
            throw new \RuntimeException("Unable to read file: {$path}");
        }

        return $parser->parse($content);
    }

    private function findParserForExtension(string $extension): ?ParserInterface
    {
        foreach ($this->parsers as $parser) {
            if ($parser->supports($extension)) {
                return $parser;
            }
        }

        return null;
    }
}
