<?php

namespace App\Services\Code\Parsers;

use App\DataTransferObjects\CodeAnalysis\ASTResult;

interface ParserInterface
{
    public function parse(string $content): ASTResult;

    public function supports(string $extension): bool;
}
