<?php

namespace Tests\Unit\Services\Code;

use App\Services\Code\ASTParserService;
use Tests\TestCase;

class ASTParserServiceTest extends TestCase
{
    public function test_parse_php_file_success(): void
    {
        $service = new ASTParserService;
        $path = base_path('tests/Fixtures/code-samples/sample-class.php');

        $result = $service->parseFile($path);

        $this->assertNotEmpty($result->classes);
        $this->assertSame('SampleClass', $result->classes[0]['name']);
        $this->assertGreaterThan(0, $result->metadata['lines']);
    }

    public function test_parse_empty_file_returns_empty_ast(): void
    {
        $tmp = sys_get_temp_dir().'/empty-file-'.uniqid().'.php';
        file_put_contents($tmp, '');

        try {
            $service = new ASTParserService;
            $result = $service->parseFile($tmp);

            $this->assertSame(0, count($result->classes));
            $this->assertSame(0, count($result->functions));
        } finally {
            @unlink($tmp);
        }
    }

    public function test_throws_on_missing_file(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $service = new ASTParserService;
        $service->parseFile(base_path('tests/Fixtures/does-not-exist.php'));
    }

    public function test_throws_on_unsupported_extension(): void
    {
        $tmp = sys_get_temp_dir().'/unsupported-'.uniqid().'.js';
        file_put_contents($tmp, 'console.log("hi");');

        try {
            $this->expectException(\InvalidArgumentException::class);
            $service = new ASTParserService;
            $service->parseFile($tmp);
        } finally {
            @unlink($tmp);
        }
    }
}
